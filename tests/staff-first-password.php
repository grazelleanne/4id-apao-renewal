<?php
declare(strict_types=1);
session_start();
require dirname(__DIR__) . '/src/ActionOtp.php';
function brevo_send_transactional_email(string $email, string $name, string $subject, string $html): string {
    preg_match('/<strong>(\d{6})<\/strong>/', $html, $match);
    $GLOBALS['sentCode'] = $match[1];
    $GLOBALS['mailRecipient'] = $email;
    return 'test';
}
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
                    if (str_starts_with($this->sql, 'INSERT INTO users')) $GLOBALS['createdUser'] = $values;
                    if (str_starts_with($this->sql, 'UPDATE users SET password=')) {
                        $GLOBALS['record']['password'] = $values['password'];
                        $GLOBALS['record']['must_change_password'] = 0;
                        $GLOBALS['record']['session_version']++;
                    }
                }
                public function fetch(): array { return $GLOBALS['record']; }
                public function fetchColumn(): string { return $GLOBALS['record']['password']; }
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
        if (!empty($response->payload['otpRequired'])) {
            if (db()->inTransaction()) throw new RuntimeException('OTP email must not hold a database transaction.');
            $GLOBALS['input']['otp_code'] = $GLOBALS['sentCode'];
            return expect($action, $status);
        }
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
foreach (['admin', 'super_admin'] as $role) {
    $record['role'] = $role;
    $record['must_change_password'] = 1;
    $record['password'] = password_hash('Temporary1!', PASSWORD_DEFAULT);
    $_SESSION['user'] = $record;
    $blocked = expect(fn() => require_user([$role]), 403);
    if ($blocked->payload['redirect'] !== '/admin/first-password') throw new RuntimeException('Admin temporary password bypass allowed.');
    $user = require_user([$role], true);
    $input = ['password' => 'Personal2!', 'password_confirmation' => 'Personal2!'];
    $success = expect(fn() => staff_first_password($user), 200);
    if ($success->payload['redirect'] !== '/admin/dashboard' || $record['must_change_password']) throw new RuntimeException('Admin password replacement failed.');
    require_user([$role]);
}
function personnel_text(array $input, string $key, int $length): string { return trim((string) ($input[$key] ?? '')); }
$from = strpos($source, 'function users_store(');
$to = strpos($source, 'function users_data(', $from);
eval(substr($source, $from, $to - $from));
foreach (['admin', 'staff'] as $role) {
    $input = ['username' => 'new@example.com', 'fullName' => 'New Account', 'role' => $role,
        'status' => 'Active', 'password' => 'Temporary1!', 'adminPassword' => 'Personal2!'];
    expect(fn() => users_store($record), 201);
    if ($mailRecipient !== 'new@example.com') throw new RuntimeException('Creation OTP must go to the new Staff or Admin email.');
    if ($createdUser['firstPassword'] !== 1 || !password_verify('Temporary1!', $createdUser['password'])) throw new RuntimeException('New account did not receive a temporary hashed password.');
}
$input['adminPassword'] = 'wrong';
expect(fn() => users_store($record), 403);
echo "First-login password, account creation and access-control checks passed.\n";
