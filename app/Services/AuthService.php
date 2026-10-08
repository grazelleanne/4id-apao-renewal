<?php
declare(strict_types=1);
namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use PDO;
use PDOException;
use Throwable;
use Exception;
use App\Support\PdfReport;

final class AuthService
{
    public static function show_login(?string $error = null): never
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        \App\Support\SessionState::$data['captcha'] = $left + $right;
        echo render_view('login', [
            'captchaQuestion' => "{$left} + {$right} = ?",
            'loginError' => $error,
        ]);
        finish_response();
    }

    public static function login(): never
    {
        require_post();
        $input = request_data();
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $captcha = $input['captcha'] ?? '';
        $answer = \App\Support\SessionState::$data['captcha'] ?? null;
        unset(\App\Support\SessionState::$data['captcha']);
        $email = is_string($email) ? strtolower(trim($email)) : '';
        $password = is_string($password) ? $password : '';
        $captcha = is_string($captcha) ? $captcha : '';
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $key = 'login:' . $email . '|' . $ip;
        unset(\App\Support\SessionState::$data['login_otp'], \App\Support\SessionState::$data['action_otp']);
        if (login_attempts($key)['retryAfter'] > 0) {
            \App\Services\AuthService::login_error('Five failed attempts. Wait 3 minutes before trying again.', 429);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 4096
            || $answer === null || !ctype_digit($captcha) || (int) $captcha !== (int) $answer) {
            \App\Services\AuthService::login_failed($key, 'Invalid details or security answer. Try the new question.', 422);
        }
        $query = db()->prepare('SELECT * FROM users WHERE LOWER(email)=:email LIMIT 1');
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        $dummy = '$2y$12$MXfSXi/zXc56DdJMxnzQvueXTWnKjf1K9QAiKQWwmFXTCCbn488y2';
        if (!password_verify($password, (string) ($user['password'] ?? $dummy)) || !$user) {
            if ($user) {
                audit(['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']], 'login_failed', $email);
            }
            \App\Services\AuthService::login_failed($key, 'Invalid email or password.', 401);
        }
        if (!(int) $user['is_active'] || !in_array($user['role'], ['super_admin','admin','staff'], true)) {
            \App\Services\AuthService::login_error('This account cannot access the system.', 403);
        }
        login_attempts($key, 'reset');
        regenerate_app_session(true);
        \App\Support\SessionState::$data['user'] = [
            'id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'],
            'role' => $user['role'], 'session_version' => (int) $user['session_version'],
            'must_change_password' => (bool) ($user['must_change_password'] ?? false),
        ];
        \App\Support\SessionState::$data['_last_activity'] = time();
        db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=:id')->execute(['id' => $user['id']]);
        audit(\App\Support\SessionState::$data['user'], 'login', $user['email']);
        json_response([
            'success' => true,
            'message' => 'Welcome back, ' . $user['name'] . '!',
            'redirect' => !empty($user['must_change_password'])
                ? ($user['role'] === 'staff' ? '/staff/first-password' : '/admin/first-password')
                : ($user['role'] === 'staff' ? '/staff/dashboard' : '/admin/dashboard'),
        ]);
    }

    public static function staff_first_password(array $user): never
    {
        if (!in_array($user['role'], ['staff','admin','super_admin'], true) || empty($user['must_change_password'])) {
            json_response(['success' => false, 'message' => 'Your first-login password has already been set.'], 409);
        }
        if (!rate_limit('first-password:' . $user['id'], 10, 300)) {
            json_response(['success' => false, 'message' => 'Too many requests. Try again in a few minutes.'], 429);
        }
        $input = request_data();
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';
        $confirmation = is_string($input['password_confirmation'] ?? null) ? $input['password_confirmation'] : '';
        if (!password_is_strong($password) || $password !== $confirmation) {
            json_response(['success' => false, 'message' => 'Use at least 8 characters with uppercase, lowercase, a number and a symbol. Both passwords must match.'], 422);
        }
        $pdo = db();
        $temporaryPassword = $pdo->prepare('SELECT password FROM users WHERE id=:id');
        $temporaryPassword->execute(['id' => $user['id']]);
        $temporaryHash = $temporaryPassword->fetchColumn();
        if (!is_string($temporaryHash) || password_verify($password, $temporaryHash)) {
            json_response(['success' => false, 'message' => 'Choose a password different from your temporary password.'], 422);
        }
        \App\Services\ActionOtpService::require_action_otp($user, 'first-password', $input);
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = $pdo->prepare('SELECT password,must_change_password,is_active,session_version FROM users WHERE id=:id FOR UPDATE');
            $query->execute(['id' => $user['id']]);
            $record = $query->fetch();
            if (!$record || !$record['is_active'] || !$record['must_change_password']
                || (int) $record['session_version'] !== (int) $user['session_version']) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'Your session is no longer valid. Sign in again.'], 409);
            }
            if (password_verify($password, $record['password'])) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'Choose a password different from your temporary password.'], 422);
            }
            $pdo->prepare('UPDATE users SET password=:password,must_change_password=0,session_version=session_version+1,updated_at=NOW() WHERE id=:id')
                ->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
            audit($user, 'first_login_password_changed', $user['email']);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            throw $error;
        }
        regenerate_app_session(true);
        \App\Support\SessionState::$data['user'] = array_replace($user, ['must_change_password' => false, 'session_version' => (int) $user['session_version'] + 1]);
        \App\Support\SessionState::$data['_last_activity'] = time();
        json_response(['success' => true, 'redirect' => $user['role'] === 'staff' ? '/staff/dashboard' : '/admin/dashboard']);
    }

    public static function login_failed(string $key, string $message, int $status): never
    {
        $state = login_attempts($key, 'fail');
        if ($state['retryAfter'] > 0) \App\Services\AuthService::login_error('Five failed attempts. Wait 3 minutes before trying again.', 429);
        \App\Services\AuthService::login_error($message . ' ' . $state['remaining'] . ' attempts remaining.', $status);
    }

    public static function login_error(string $message, int $status): never
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        \App\Support\SessionState::$data['captcha'] = $left + $right;
        $acceptsJson = str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
            || str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json');
        if ($acceptsJson) {
            json_response(['success' => false, 'message' => $message, 'captcha_question' => "{$left} + {$right} = ?"], $status);
        }
        \App\Services\AuthService::show_login($message);
    }

}
