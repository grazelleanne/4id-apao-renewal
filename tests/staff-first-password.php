<?php
declare(strict_types=1);
session_start();
final class PasswordResponse extends RuntimeException {
    public function __construct(public array $payload, public int $status) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new PasswordResponse($payload, $status); }
function request_data(): array { return $GLOBALS['input']; }
function rate_limit(string $key, int $limit, int $window): bool { return true; }
function audit(array $user, string $action, string $subject): void {}
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function request_expects_json(): bool { return true; }
function env_value(string $key, string $default = ''): string { return $default; }
function csrf_token(): string { return 'test'; }
function redirect(string $path): never { throw new PasswordResponse(['redirect' => $path], 303); }
function db(): object {
    static $database;
    return $database ??= new class {
        private bool $transaction = false;
        public function beginTransaction(): void { $this->transaction = true; }
        public function commit(): void { $this->transaction = false; }
        public function rollBack(): void { $this->transaction = false; }
        public function inTransaction(): bool { return $this->transaction; }
        public function prepare(string $sql): object {
            return new class($sql) {
                public function __construct(private string $sql) {}
                public function execute(array $values): void {
                    if (str_starts_with($this->sql, 'UPDATE users SET password=')) {
                        $GLOBALS['record']['password'] = $values['password'];
                        $GLOBALS['record']['must_change_password'] = 0;
                        $GLOBALS['record']['session_version']++;
                    }
                }
                public function fetch(): array { return $GLOBALS['record']; }
            };
        }
    };
}
$bootstrap = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__) . '/src/bootstrap.php'));
preg_match('/function password_is_strong\(.*?\n}\n/s', $bootstrap, $match);
eval($match[0]);
$from = strpos($bootstrap, 'function require_user(');
$to = strpos($bootstrap, 'function redirect(', $from);
eval(substr($bootstrap, $from, $to - $from));
$source = str_replace("\r\n", "\n", file_get_contents(dirname(__DIR__) . '/public/index.php'));
$from = strpos($source, 'function staff_first_password(');
$to = strpos($source, 'function login_failed(', $from);
eval(substr($source, $from, $to - $from));
function expect(callable $action, int $status): PasswordResponse {
    try { $action(); } catch (PasswordResponse $response) {
        if ($response->status !== $status || db()->inTransaction()) throw new RuntimeException('Unexpected password response or unclosed transaction.');
        return $response;
    }
    throw new RuntimeException('Expected response missing.');
}
$record = ['id' => 3, 'name' => 'New Staff', 'email' => 'staff@example.com', 'role' => 'staff',
    'password' => password_hash('Temporary1!', PASSWORD_DEFAULT), 'is_active' => 1, 'session_version' => 1, 'must_change_password' => 1];
$_SESSION['user'] = $record;
$_SESSION['_last_activity'] = time();
$_SERVER['REQUEST_METHOD'] = 'POST';
$blocked = expect(fn() => require_user(['staff']), 403);
if ($blocked->payload['redirect'] !== '/staff/first-password') throw new RuntimeException('Temporary-password bypass allowed.');
$user = require_user(['staff'], true);
$input = ['password' => 'weak', 'password_confirmation' => 'weak'];
expect(fn() => staff_first_password($user), 422);
$input = ['password' => 'Temporary1!', 'password_confirmation' => 'Temporary1!'];
expect(fn() => staff_first_password($user), 422);
$input = ['password' => 'Personal2!', 'password_confirmation' => 'Mismatch2!'];
expect(fn() => staff_first_password($user), 422);
$input['password_confirmation'] = 'Personal2!';
$success = expect(fn() => staff_first_password($user), 200);
if ($success->payload['redirect'] !== '/staff/dashboard' || !password_verify('Personal2!', $record['password'])
    || $record['must_change_password'] || $_SESSION['user']['must_change_password'] || $_SESSION['user']['session_version'] !== 2) {
    throw new RuntimeException('Password replacement did not clear the gate or update the session.');
}
require_user(['staff']);
expect(fn() => staff_first_password($_SESSION['user']), 409);
echo "First-login password and access-control checks passed.\n";
