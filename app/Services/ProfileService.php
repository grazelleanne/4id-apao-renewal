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

final class ProfileService
{
    public static function profile_update(array $user): never
    {
        $input = request_data();
        if (!rate_limit('profile-update:' . $user['id'], 15, 300)) {
            json_response(['success' => false, 'message' => 'Too many attempts. Please try again later.'], 429);
        }
        $name = is_string($input['name'] ?? null) ? trim($input['name']) : '';
        $email = is_string($input['email'] ?? null) ? strtolower(trim($input['email'])) : '';
        if ($name === '' || strlen($name) > 255 || strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'message' => 'Enter a full name and valid email address, each within 255 characters.'], 422);
        }
        $emailChanged = $email !== strtolower($user['email']);
        $pdo = db();
        $hash = null;
        if ($emailChanged) {
            $current = $input['current_password'] ?? '';
            $query = $pdo->prepare('SELECT password FROM users WHERE id=:id');
            $query->execute(['id' => $user['id']]);
            $hash = $query->fetchColumn();
            if (!is_string($current) || !is_string($hash) || !password_verify($current, $hash)) {
                json_response(['success' => false, 'message' => 'Enter your correct current password to change your email.'], 403);
            }
        }
        if ($name === $user['name'] && !$emailChanged) {
            json_response(['success' => true, 'message' => 'Your profile is already up to date.', 'user' => ['name' => $name, 'email' => $email]]);
        }
        $query = $pdo->prepare('SELECT id FROM users WHERE LOWER(email)=:email AND id<>:id LIMIT 1');
        $query->execute(['email' => $email, 'id' => $user['id']]);
        if ($query->fetchColumn() !== false) {
            json_response(['success' => false, 'message' => 'This email address is already used by another account.'], 409);
        }
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $sql = 'UPDATE users SET name=:name,email=:email,session_version=session_version+1,updated_at=NOW() WHERE id=:id AND session_version=:version';
            $values = ['name' => $name, 'email' => $email, 'id' => $user['id'], 'version' => $user['session_version']];
            if ($emailChanged) { $sql .= ' AND password=:old_password'; $values['old_password'] = $hash; }
            $query = $pdo->prepare($sql);
            $query->execute($values);
            if ($query->rowCount() !== 1) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'Your account changed. Please sign in again.'], 409);
            }
            audit($user, 'profile_updated', $email);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062) {
                json_response(['success' => false, 'message' => 'This email address is already used by another account.'], 409);
            }
            throw $error;
        }
        regenerate_app_session(true);
        \App\Support\SessionState::$data['user'] = array_replace($user, ['name' => $name, 'email' => $email, 'session_version' => $user['session_version'] + 1]);
        unset(\App\Support\SessionState::$data['action_otp']);
        json_response(['success' => true, 'message' => 'Profile updated successfully. Use your updated email the next time you sign in.', 'user' => ['name' => $name, 'email' => $email]]);
    }

    public static function profile_change_password(array $user): never
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
        \App\Services\ActionOtpService::require_action_otp($user, 'profile-password', $input);
        $query = db()->prepare('UPDATE users SET password=:password,session_version=session_version+1,updated_at=NOW() WHERE id=:id AND session_version=:version AND password=:old');
        $query->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id'], 'version' => $user['session_version'], 'old' => $hash]);
        if ($query->rowCount() !== 1) json_response(['success' => false, 'message' => 'Your account changed. Sign in again.'], 409);
        regenerate_app_session(true);
        \App\Support\SessionState::$data['user']['session_version']++;
        audit(\App\Support\SessionState::$data['user'], 'password_changed', $user['email']);
        json_response(['success' => true, 'message' => 'Your password was changed successfully.']);
    }

}
