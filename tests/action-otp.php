<?php
declare(strict_types=1);
session_start();
require dirname(__DIR__) . '/src/ActionOtp.php';
class OtpResponse extends RuntimeException {
    public function __construct(public array $payload, public int $status) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new OtpResponse($payload, $status); }
function csrf_token(): string { return 'session-secret'; }
function request_data(): array { return $GLOBALS['profileInput']; }
function password_is_strong(string $password): bool { return strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password); }
function audit(array $user, string $action, string $subject): void {}
function db(): object {
    return new class {
        public function prepare(string $sql): object {
            return new class($sql) {
                private int $changed = 0;
                public function __construct(private string $sql) {}
                public function execute(array $values): void {
                    if (str_starts_with($this->sql, 'UPDATE users')) {
                        $GLOBALS['profileHash'] = $values['password'];
                        $this->changed = 1;
                    }
                }
                public function fetchColumn(): string { return $GLOBALS['profileHash']; }
                public function rowCount(): int { return $this->changed; }
            };
        }
    };
}
function rate_limit(string $key, int $limit, int $window): bool { return $GLOBALS['allowMail'] ?? true; }
function brevo_send_transactional_email(string $email, string $name, string $subject, string $html): string {
    if (!empty($GLOBALS['mailFails'])) throw new RuntimeException('Delivery failed');
    preg_match('/<strong>(\d{6})<\/strong>/', $html, $matches);
    $GLOBALS['code'] = $matches[1];
    $GLOBALS['sent'] = ($GLOBALS['sent'] ?? 0) + 1;
    $GLOBALS['recipient'] = $email;
    return 'test';
}
function check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
function attempt(array $user, string $action, array $input): ?OtpResponse {
    try { require_action_otp($user, $action, $input); } catch (OtpResponse $response) { return $response; }
    return null;
}
$user = ['id' => 1, 'session_version' => 2, 'email' => 'admin@example.com', 'name' => 'Admin'];
$input = ['password' => 'Temporary1!', 'role' => 'admin'];
check(attempt($user, 'create-user', $input)->payload['otpRequired'], 'Creation must request verification first.');
check($recipient === $user['email'] && !str_contains(json_encode($_SESSION), 'Temporary1!'), 'Send to acting admin and never store the password.');
attempt($user, 'create-user', $input);
check($sent === 1, 'Repeated submission must reuse the pending challenge.');
check(attempt($user, 'profile-password', $input + ['otp_code' => $code])->status === 422, 'Codes must not authorize another operation.');
attempt($user, 'create-user', $input);
check(attempt($user, 'create-user', ['password' => 'Changed1!', 'role' => 'admin', 'otp_code' => $code])->status === 422, 'Codes must bind to submitted details.');
attempt($user, 'create-user', $input);
check(attempt($user, 'create-user', $input + ['otp_code' => $code]) === null && !isset($_SESSION['action_otp']), 'Correct code authorizes once.');
check(attempt($user, 'create-user', $input + ['otp_code' => $code])->status === 422, 'Consumed codes must not be replayed.');
attempt($user, 'create-user', $input);
$_SESSION['action_otp']['expires'] = time() - 1;
check(attempt($user, 'create-user', $input + ['otp_code' => $code])->status === 422, 'Expired codes must fail.');
attempt($user, 'create-user', $input);
for ($i = 0; $i < 5; $i++) $result = attempt($user, 'create-user', $input + ['otp_code' => 'wrong']);
check($result->status === 429 && !isset($_SESSION['action_otp']), 'Five incorrect codes must invalidate verification.');
$allowMail = false;
check(attempt($user, 'create-user', $input)->status === 429, 'Email requests must be rate limited.');
$allowMail = true; $mailFails = true;
check(attempt($user, 'create-user', $input)->status === 502 && !isset($_SESSION['action_otp']), 'Mail failure must not authorize an action.');
$mailFails = false;
$profileHash = password_hash('Current1!', PASSWORD_DEFAULT);
$_SESSION['user'] = $user;
$profileInput = ['current_password' => 'wrong', 'new_password' => 'Changed2!', 'new_password_confirmation' => 'Changed2!'];
try { profile_change_password($user); } catch (OtpResponse $response) { check($response->status === 403, 'Incorrect current password must be rejected.'); }
$profileInput['current_password'] = 'Current1!';
try { profile_change_password($user); } catch (OtpResponse $response) { check(!empty($response->payload['otpRequired']) && password_verify('Current1!', $profileHash), 'Password must remain unchanged before OTP.'); }
$profileInput['otp_code'] = $code;
try { profile_change_password($user); } catch (OtpResponse $response) { check($response->payload['success'] && password_verify('Changed2!', $profileHash) && $_SESSION['user']['session_version'] === 3, 'Verified change must replace the password and rotate session version.'); }
echo "Action OTP and profile-password security checks passed.\n";
