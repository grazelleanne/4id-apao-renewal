<?php
declare(strict_types=1);

function require_action_otp(array $user, string $action, array $input): void
{
    $code = $input['otp_code'] ?? '';
    unset($input['otp_code'], $input['_token'], $input['_csrf']);
    ksort($input);
    $binding = hash_hmac('sha256', json_encode([$user['id'], $user['session_version'], $action, $input], JSON_THROW_ON_ERROR), csrf_token());
    $challenge = $_SESSION['action_otp'] ?? null;
    if ($code !== '') {
        if (!$challenge || $challenge['expires'] <= time() || !hash_equals($challenge['binding'], $binding)) {
            unset($_SESSION['action_otp']);
            json_response(['success' => false, 'message' => 'Verification expired or details changed. Submit again for a new code.'], 422);
        }
        $_SESSION['action_otp']['attempts']++;
        if (!is_string($code) || !preg_match('/^\d{6}$/', $code) || !password_verify($code, $challenge['hash'])) {
            $locked = $_SESSION['action_otp']['attempts'] >= 5;
            if ($locked) unset($_SESSION['action_otp']);
            json_response(['success' => false, 'message' => $locked ? 'Too many incorrect codes. Try again later.' : 'Incorrect verification code. Submit again and enter the code.'], $locked ? 429 : 422);
        }
        unset($_SESSION['action_otp']);
        return;
    }
    if ($challenge && $challenge['expires'] > time() && hash_equals($challenge['binding'], $binding)) {
        json_response(['success' => true, 'otpRequired' => true, 'message' => 'Enter the code already sent to your account email.']);
    }
    if (!rate_limit('action-otp:' . $user['id'], 5, 900)) {
        json_response(['success' => false, 'message' => 'Too many verification emails. Try again later.'], 429);
    }
    unset($_SESSION['action_otp']);
    $code = (string) random_int(100000, 999999);
    try {
        brevo_send_transactional_email($user['email'], $user['name'], 'APAO account verification',
            '<p>Confirm your ' . ($action === 'create-user' ? 'user-account creation' : 'password change')
            . ' with this code:</p><p><strong>' . $code . '</strong></p><p>Expires in 5 minutes. Do not share this code.</p>');
    } catch (Throwable $error) {
        json_response(['success' => false, 'message' => 'Unable to send verification email. Try again later.'], 502);
    }
    $_SESSION['action_otp'] = ['binding' => $binding, 'hash' => password_hash($code, PASSWORD_DEFAULT), 'expires' => time() + 300, 'attempts' => 0];
    json_response(['success' => true, 'otpRequired' => true, 'message' => 'A verification code was sent to your account email.']);
}

function profile_change_password(array $user): never
{
    $input = request_data();
    if (!rate_limit('profile-password:' . $user['id'], 15, 300)) json_response(['success' => false, 'message' => 'Too many attempts. Try again later.'], 429);
    $password = $input['new_password'] ?? '';
    $current = $input['current_password'] ?? '';
    if (!is_string($password) || !is_string($current) || !password_is_strong($password)
        || $password !== ($input['new_password_confirmation'] ?? null)) {
        json_response(['success' => false, 'message' => 'Use a strong new password and matching confirmation.'], 422);
    }
    $query = db()->prepare('SELECT password FROM users WHERE id=:id');
    $query->execute(['id' => $user['id']]);
    $hash = $query->fetchColumn();
    if (!is_string($hash) || !password_verify($current, $hash)) json_response(['success' => false, 'message' => 'Your current password is incorrect.'], 403);
    if (password_verify($password, $hash)) json_response(['success' => false, 'message' => 'Choose a different new password.'], 422);
    require_action_otp($user, 'profile-password', $input);
    $query = db()->prepare('UPDATE users SET password=:password,session_version=session_version+1,updated_at=NOW() WHERE id=:id AND session_version=:version AND password=:old');
    $query->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id'], 'version' => $user['session_version'], 'old' => $hash]);
    if ($query->rowCount() !== 1) json_response(['success' => false, 'message' => 'Your account changed. Sign in again.'], 409);
    session_regenerate_id(true);
    $_SESSION['user']['session_version']++;
    audit($_SESSION['user'], 'password_changed', $user['email']);
    json_response(['success' => true, 'message' => 'Your password was changed successfully.']);
}
