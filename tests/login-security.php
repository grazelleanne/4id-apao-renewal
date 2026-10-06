<?php
declare(strict_types=1);
session_start();
final class LoginResponse extends RuntimeException
{
    public function __construct(public array $payload, public int $status = 200) { parent::__construct(); }
}
$input = [];
$sentCode = null;
$attemptState = ['remaining' => 5, 'retryAfter' => 0];
$testUser = ['id' => 1, 'name' => 'Test Staff', 'email' => 'staff@example.com',
    'password' => password_hash('Example1!', PASSWORD_DEFAULT), 'role' => 'staff', 'is_active' => 1, 'session_version' => 1];
function require_post(): void {}
function request_data(): array { return $GLOBALS['input']; }
function rate_limit(string $key, int $limit, int $window): bool { return true; }
function login_attempts(string $key, string $action = 'check'): array {
    if ($action === 'reset') $GLOBALS['attemptState'] = ['remaining' => 5, 'retryAfter' => 0];
    if ($action === 'fail') {
        $GLOBALS['attemptState']['remaining']--;
        if ($GLOBALS['attemptState']['remaining'] <= 0) $GLOBALS['attemptState']['retryAfter'] = 180;
    }
    return $GLOBALS['attemptState'];
}
function json_response(array $payload, int $status = 200): never { throw new LoginResponse($payload, $status); }
function login_error(string $message, int $status): never { throw new LoginResponse(['success' => false, 'message' => $message], $status); }
function audit(array $user, string $action, string $subject): void {}
function brevo_send_transactional_email(string $email, string $name, string $subject, string $html): string {
    preg_match('/<strong>(\d{6})<\/strong>/', $html, $match);
    $GLOBALS['sentCode'] = $match[1];
    return 'test-message';
}
function db(): object {
    return new class {
        public function prepare(string $sql): object {
            $GLOBALS['lastSql'] = $sql;
            return new class {
                public function execute(array $values): void { $GLOBALS['lastValues'] = $values; }
                public function fetch(): array { return $GLOBALS['testUser']; }
                public function fetchAll(): array { return []; }
            };
        }
    };
}
$source = file_get_contents(dirname(__DIR__) . '/public/index.php');
$start = strpos($source, 'function login(): never');
$end = strpos($source, 'function login_error', $start);
eval(substr($source, $start, $end - $start));
function response(callable $call): LoginResponse {
    try { $call(); } catch (LoginResponse $result) { return $result; }
    throw new RuntimeException('Missing login response.');
}
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function credentials(string $password = 'Example1!'): void {
    $_SESSION = ['captcha' => 4];
    $GLOBALS['input'] = ['email' => 'staff@example.com', 'password' => $password, 'captcha' => '4'];
}
credentials();
$result = response('login');
check($result->status === 200 && isset($_SESSION['user']) && isset($result->payload['redirect']), 'Correct credentials must authenticate directly.');
check($sentCode === null && !isset($result->payload['otpRequired']), 'Login must not send or request email codes.');
$attemptState = ['remaining' => 5, 'retryAfter' => 0];
for ($attempt = 0; $attempt < 5; $attempt++) {
    credentials('wrong');
    $result = response('login');
}
check($result->status === 429 && !isset($_SESSION['user']), 'Five failed passwords must block login.');
credentials();
check(response('login')->status === 429 && !isset($_SESSION['user']), 'A correct password must not bypass an active lockout.');
$attemptState = ['remaining' => 5, 'retryAfter' => 0];
$testUser['is_active'] = 0;
credentials();
check(response('login')->status === 403 && !isset($_SESSION['user']), 'Inactive accounts must not authenticate.');
$start = strpos($source, 'function audit_data(): never');
$end = strpos($source, 'function inspection_data', $start);
eval(substr($source, $start, $end - $start));
$_GET = ['date_from' => '2026-10-01', 'date_to' => '2026-10-06'];
check(response('audit_data')->status === 200, 'Valid audit filters must load.');
check(str_contains($GLOBALS['lastSql'], 'created_at >= :date_from') && str_contains($GLOBALS['lastSql'], 'created_at < :date_to'), 'Audit filtering must happen in the database.');
check($GLOBALS['lastValues'] === ['date_from' => '2026-10-01', 'date_to' => '2026-10-07'], 'Audit end date must include the entire selected day.');
$_GET = ['date_from' => '2026-10-32'];
check(response('audit_data')->status === 422, 'Invalid audit date must be rejected.');
$_GET = ['date_from' => '2026-10-06', 'date_to' => '2026-10-01'];
check(response('audit_data')->status === 422, 'Reversed audit ranges must be rejected.');
echo "Login and audit security checks passed.\n";
