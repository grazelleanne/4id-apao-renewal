<?php
declare(strict_types=1);
session_start();
require dirname(__DIR__) . '/src/Profile.php';
final class ProfileResponse extends RuntimeException {
    public function __construct(public array $payload, public int $status) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new ProfileResponse($payload, $status); }
function request_data(): array { return $GLOBALS['input']; }
function rate_limit(string $key, int $limit, int $window): bool { return true; }
function audit(array $user, string $action, string $subject): void {}
function db(): object {
    static $pdo;
    return $pdo ??= new class {
        private bool $transaction = false;
        public function beginTransaction(): void { $this->transaction = true; }
        public function commit(): void { $this->transaction = false; }
        public function rollBack(): void { $this->transaction = false; }
        public function inTransaction(): bool { return $this->transaction; }
        public function prepare(string $sql): object {
            return new class($sql) {
                private int $changed = 0;
                public function __construct(private string $sql) {}
                public function execute(array $values): void {
                    if (str_starts_with($this->sql, 'UPDATE users')) {
                        $GLOBALS['updatedValues'] = $values;
                        $this->changed = empty($GLOBALS['stale']) ? 1 : 0;
                    }
                }
                public function fetchColumn(): mixed {
                    return str_contains($this->sql, 'SELECT password') ? $GLOBALS['hash'] : ($GLOBALS['duplicate'] ?? false);
                }
                public function rowCount(): int { return $this->changed; }
            };
        }
    };
}
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function run(array $user): ProfileResponse {
    try { profile_update($user); } catch (ProfileResponse $response) {
        check(!db()->inTransaction(), 'Transaction must be closed.');
        return $response;
    }
    throw new RuntimeException('Missing response.');
}
$hash = password_hash('Current1!', PASSWORD_DEFAULT);
foreach (['staff','admin','super_admin'] as $role) {
    $user = ['id' => 1, 'role' => $role, 'name' => 'Old Name', 'email' => 'old@example.com', 'session_version' => 1];
    $_SESSION['user'] = $user;
    $input = ['name' => 'New Name', 'email' => 'new@example.com', 'current_password' => 'wrong'];
    check(run($user)->status === 403, 'Email changes require current password.');
    $input['current_password'] = 'Current1!';
    $duplicate = 9;
    check(run($user)->status === 409, 'Duplicate emails must be rejected.');
    $duplicate = false;
    $result = run($user);
    check($result->payload['success'] && $_SESSION['user']['email'] === 'new@example.com'
        && $_SESSION['user']['session_version'] === 2 && $updatedValues['old_password'] === $hash, 'Email update must save, protect against concurrent password changes, and refresh session.');
    $input = ['name' => 'Name Only', 'email' => 'old@example.com'];
    check(run($user)->payload['success'] && !isset($updatedValues['old_password']), 'Name-only updates do not require password.');
    $input['email'] = 'invalid';
    check(run($user)->status === 422, 'Invalid email must be rejected.');
    $input = ['name' => 'New Name', 'email' => 'new@example.com', 'current_password' => 'Current1!'];
    $stale = true;
    check(run($user)->status === 409, 'Stale account versions must not be overwritten.');
    $stale = false;
}
echo "Staff and Admin profile update checks passed.\n";
