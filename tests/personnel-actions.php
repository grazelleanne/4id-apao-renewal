<?php
declare(strict_types=1);
final class ActionResponse extends RuntimeException {
    public function __construct(public array $payload, public int $status) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new ActionResponse($payload, $status); }
function request_data(): array { return $GLOBALS['input']; }
function personnel_text(array $input, string $key, int $maximum = 255): string { return is_string($input[$key] ?? null) ? trim($input[$key]) : ''; }
function audit(array $user, string $action, string $subject): void { $GLOBALS['audits'][] = $action; }
function renewal_status_for_personnel(array $row): string { return 'renewed'; }
final class ActionDatabase {
    public bool $transaction = false;
    public array $queries = [];
    public function beginTransaction(): void { $this->transaction = true; }
    public function commit(): void { $this->transaction = false; }
    public function rollBack(): void { $this->transaction = false; }
    public function inTransaction(): bool { return $this->transaction; }
    public function prepare(string $sql): object {
        return new class($this, $sql) {
            public function __construct(private ActionDatabase $db, private string $sql) {}
            public function execute(array $values = []): void { $this->db->queries[] = [$this->sql, $values]; }
            public function fetch(): array|false { return $GLOBALS['record']; }
            public function fetchAll(): array { return $GLOBALS['rows']; }
        };
    }
}
function db(): ActionDatabase { return $GLOBALS['database']; }
$source = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__) . '/public/index.php'));
foreach ([['personnel_rows', 'personnel_data'], ['personnel_restore', 'personnel_duplicate_errors'], ['inspection_find_personnel', 'inspection_detail']] as [$start, $end]) {
    $from = strpos($source, 'function ' . $start . '(');
    $to = strpos($source, 'function ' . $end . '(', $from);
    eval(substr($source, $from, $to - $from));
}
$database = new ActionDatabase();
$record = ['id' => 9, 'first_name' => 'Test', 'last_name' => 'Personnel'];
$rows = [];
$audits = [];
$user = ['id' => 1, 'role' => 'admin'];
$input = [];
function expect_response(callable $action, int $status): void {
    try { $action(); } catch (ActionResponse $response) {
        if ($response->status !== $status || db()->inTransaction()) throw new RuntimeException('Unexpected action response or open transaction.');
        return;
    }
    throw new RuntimeException('Missing JSON action response.');
}
expect_response(fn() => personnel_change(22, $user, true), 200);
$archiveSql = $database->queries[1][0];
if (!str_contains($archiveSql, 'archived_at=CURDATE()') || str_contains($archiveSql, 'DELETE')) throw new RuntimeException('Archive must retain related history.');
$input = ['id' => '22'];
expect_response(fn() => personnel_restore($user), 200);
if (!str_contains($database->queries[3][0], 'archived_at=NULL')) throw new RuntimeException('Restore failed.');
$input = ['firstName' => 'Test', 'lastName' => "O'Brien'; DROP TABLE personnel; --", 'qtyAmmo' => 10, 'dateOfBirth' => '1990-03-12'];
expect_response(fn() => personnel_change(22, $user, false), 200);
[$sql, $values] = end($database->queries);
if (str_contains($sql, 'DROP TABLE') || $values['lastName'] !== $input['lastName'] || $values['qtyAmmo'] !== 10) throw new RuntimeException('Update must bind inputs as values.');
$input = ['dateOfBirth' => '0000-00-00'];
expect_response(fn() => personnel_change(22, $user, false), 422);
$record = false;
expect_response(fn() => personnel_change(999, $user, true), 404);
inspection_find_personnel(22);
[$sql, $values] = end($database->queries);
if (!str_contains($sql, 'AND p.item_number=:item') || $values !== ['item' => 22] || str_contains($sql, 'SELECT p.*') || str_contains($sql, '""')) throw new RuntimeException('Inspection detail must load one lightweight record with SQL-mode-independent syntax.');
echo "Personnel archive, restore, update and lookup checks passed.\n";
