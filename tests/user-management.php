<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/UserManagement.php';
class ManagementResponse extends RuntimeException {
    public function __construct(public int $status) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new ManagementResponse($status); }
function request_data(): array { return $GLOBALS['input']; }
function rate_limit(string $key, int $limit, int $window): bool { return true; }
function audit(array $user, string $action, string $subject): void {}
function password_is_strong(string $password): bool { return strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password); }
function require_action_otp(array $user, string $action, array $input): void {
    if (db()->inTransaction()) throw new RuntimeException('OTP request must not hold account locks.');
    $GLOBALS['otpAction'] = $action;
}
function db(): object {
    static $pdo;
    return $pdo ??= new class {
        private bool $transaction = false;
        public function beginTransaction(): void { $this->transaction = true; }
        public function commit(): void { $this->transaction = false; }
        public function rollBack(): void { $this->transaction = false; }
        public function inTransaction(): bool { return $this->transaction; }
        public function query(string $sql): object { return new class { public function fetchAll(): array { return $GLOBALS['rows']; } }; }
        public function prepare(string $sql): object { return new class($sql) {
            public function __construct(private string $sql) {}
            public function execute(array $values): void { if (str_starts_with($this->sql, 'UPDATE users')) { $GLOBALS['saved'] = $values; $GLOBALS['updateSql'] = $this->sql; } }
            public function fetchColumn(): string { return $GLOBALS['admin']['password']; }
        }; }
    };
}
function expect(array $admin, int $status): void {
    unset($GLOBALS['saved']);
    try { users_update($admin); } catch (ManagementResponse $response) {
        if ($response->status !== $status || db()->inTransaction() || ($status !== 200 && isset($GLOBALS['saved']))) throw new RuntimeException('Unexpected account update result.');
        return;
    }
    throw new RuntimeException('Missing response.');
}
$admin = ['id' => 1, 'email' => 'admin@example.com', 'name' => 'Admin', 'role' => 'super_admin', 'is_active' => 1, 'session_version' => 1, 'password' => password_hash('Admin1!', PASSWORD_DEFAULT)];
$staff = array_replace($admin, ['id' => 2, 'email' => 'staff@example.com', 'role' => 'staff']);
$rows = [$admin, $staff];
$input = ['username' => $staff['email'], 'fullName' => 'Staff', 'role' => 'staff', 'status' => 'Inactive', 'adminPassword' => 'wrong'];
expect($admin, 403);
$input['adminPassword'] = 'Admin1!';
expect($admin, 200);
if ($saved['active'] !== 0 || $saved['increment'] !== 1) throw new RuntimeException('Deactivation must invalidate target sessions.');
$input['status'] = 'Active'; expect($admin, 200);
$input['newPassword'] = 'NewTemporary2!'; $input['newPasswordConfirmation'] = 'Mismatch'; expect($admin, 422);
$input['newPasswordConfirmation'] = 'NewTemporary2!'; expect($admin, 200);
if (!password_verify('NewTemporary2!', $saved['password']) || !str_contains($updateSql, 'must_change_password=1')
    || $saved['increment'] !== 1 || $otpAction !== 'reset-user-password') throw new RuntimeException('Password reset must hash, force replacement, invalidate sessions and require OTP.');
$input = ['username' => $admin['email'], 'fullName' => 'Admin', 'role' => 'admin', 'status' => 'Inactive', 'adminPassword' => 'Admin1!'];
expect($admin, 403);
$rows[1]['role'] = 'admin';
$input = ['username' => $staff['email'], 'fullName' => 'Admin', 'role' => 'staff', 'status' => 'Inactive', 'adminPassword' => 'Admin1!'];
expect($admin, 200);
$rows[0]['is_active'] = 0;
$rows[] = array_replace($staff, ['id' => 3, 'email' => 'actor@example.com', 'role' => 'admin']);
$actor = $rows[2];
$rows[1]['role'] = 'super_admin';
$input['username'] = $rows[1]['email']; expect($actor, 403);
$rows[1]['role'] = 'admin';
$input['username'] = $rows[0]['email']; $input['role'] = 'admin'; expect($actor, 403);
echo "User account status and authorization checks passed.\n";
