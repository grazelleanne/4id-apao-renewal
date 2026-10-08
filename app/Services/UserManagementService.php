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

final class UserManagementService
{
    public static function users_store(array $admin): never
    {
        $input = request_data();
        $email = strtolower(\App\Services\PersonnelService::personnel_text($input, 'username', 255));
        $name = \App\Services\PersonnelService::personnel_text($input, 'fullName', 255);
        $role = \App\Services\PersonnelService::personnel_text($input, 'role', 32);
        $status = \App\Services\PersonnelService::personnel_text($input, 'status', 32);
        $password = is_string($input['password'] ?? null) ? $input['password'] : '';
        $confirmation = is_string($input['adminPassword'] ?? null) ? $input['adminPassword'] : '';
        if (!rate_limit('create-user:' . $admin['id'], 10, 300)) {
            json_response(['success' => false, 'message' => 'Too many requests. Please wait a few minutes.'], 429);
        }
        $query = db()->prepare('SELECT password FROM users WHERE id=:id');
        $query->execute(['id' => $admin['id']]);
        $hash = $query->fetchColumn();
        if (!is_string($hash) || !password_verify($confirmation, $hash)) {
            json_response(['success' => false, 'message' => 'Your administrator password is incorrect.'], 403);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === ''
            || !in_array($role, ['staff', 'admin'], true) || !in_array($status, ['Active', 'Inactive'], true)) {
            json_response(['success' => false, 'message' => 'Enter a valid email, full name, role, and account status.'], 422);
        }
        if (!password_is_strong($password)) {
            json_response(['success' => false, 'message' => 'Use 8–1024 characters with uppercase, lowercase, a number, and a symbol.'], 422);
        }
        \App\Services\ActionOtpService::require_action_otp($admin, 'create-user', $input);
        $pdo = db();
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $pdo->prepare('INSERT INTO users (name,email,password,role,is_active,session_version,must_change_password,created_at,updated_at)
                VALUES (:name,:email,:password,:role,:active,1,:firstPassword,NOW(),NOW())')->execute([
                    'name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role, 'active' => $status === 'Active' ? 1 : 0,
                    'firstPassword' => 1,
                ]);
            audit($admin, 'user_created', $email);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            if ($error instanceof PDOException && $error->getCode() === '42S22'
                && str_contains($error->getMessage(), 'must_change_password')) {
                error_log('[APAO PHP] Account creation requires database/add_staff_first_login_password.sql.');
                json_response(['success' => false, 'message' => 'The first-login database update has not been applied. Run add_staff_first_login_password.sql in Adminer, then retry.'], 503);
            }
            if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062) {
                json_response(['success' => false, 'message' => 'An account with this email already exists.'], 409);
            }
            throw $error;
        }
        json_response(['success' => true, 'message' => 'User account created successfully.'], 201);
    }

    public static function users_data(): never
    {
        $rows = db()->query('SELECT id,name,email,contact_number,role,is_active,last_login_at,created_at FROM users ORDER BY id')->fetchAll();
        $users = array_map(static fn (array $row): array => [
            'id' => (int) $row['id'], 'name' => $row['name'], 'email' => $row['email'],
            'username' => $row['email'], 'fullName' => $row['name'],
            'contactNumber' => $row['contact_number'] ?? '', 'role' => $row['role'],
            'isActive' => (bool) $row['is_active'], 'status' => (int) $row['is_active'] ? 'Active' : 'Inactive',
            'lastLoginAt' => $row['last_login_at'],
            'createdAt' => $row['created_at'],
        ], $rows);
        json_response(['success' => true, 'users' => $users, 'data' => $users]);
    }

    public static function users_update(array $admin): never
    {
        $input = request_data();
        if (!rate_limit('manage-user:' . $admin['id'], 15, 300)) json_response(['success' => false, 'message' => 'Too many attempts. Please try again later.'], 429);
        $email = is_string($input['username'] ?? null) ? strtolower(trim($input['username'])) : '';
        $name = is_string($input['fullName'] ?? null) ? trim($input['fullName']) : '';
        $role = $input['role'] ?? '';
        $status = $input['status'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($name) > 255
            || !in_array($role, ['staff','admin'], true) || !in_array($status, ['Active','Inactive'], true)) {
            json_response(['success' => false, 'message' => 'Enter a valid account, name, role and status.'], 422);
        }
        $pdo = db();
        $reset = array_key_exists('newPassword', $input);
        if ($reset) {
            $newPassword = $input['newPassword'];
            if (!is_string($newPassword) || !password_is_strong($newPassword)
                || $newPassword !== ($input['newPasswordConfirmation'] ?? null)) {
                json_response(['success' => false, 'message' => 'Use a strong temporary password and matching confirmation.'], 422);
            }
            if ($email === strtolower($admin['email'])) {
                json_response(['success' => false, 'message' => 'Use Profile Settings to change your own password.'], 403);
            }
            $check = $pdo->prepare('SELECT password FROM users WHERE id=:id');
            $check->execute(['id' => $admin['id']]);
            $actorHash = $check->fetchColumn();
            $confirmation = $input['adminPassword'] ?? '';
            if (!is_string($actorHash) || !is_string($confirmation) || !password_verify($confirmation, $actorHash)) {
                json_response(['success' => false, 'message' => 'Your administrator password is incorrect.'], 403);
            }
            \App\Services\ActionOtpService::require_action_otp($admin, 'reset-user-password', $input);
        }
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Lock in a consistent order so concurrent changes cannot remove all administrators.
            $rows = $pdo->query('SELECT id,name,email,role,is_active,password,session_version FROM users ORDER BY id FOR UPDATE')->fetchAll();
            $actor = null; $target = null; $activeAdmins = 0;
            foreach ($rows as $row) {
                if ((int) $row['id'] === (int) $admin['id']) $actor = $row;
                if (strtolower($row['email']) === $email) $target = $row;
                if ($row['is_active'] && in_array($row['role'], ['admin','super_admin'], true)) $activeAdmins++;
            }
            $reject = static function(string $message, int $status) use ($pdo): never {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => $message], $status);
            };
            if (!$actor || !$actor['is_active'] || !in_array($actor['role'], ['admin','super_admin'], true)
                || (int) $actor['session_version'] !== (int) $admin['session_version']) $reject('Your session changed. Sign in again.', 403);
            if (!$target) $reject('User account not found.', 404);
            if ($target['role'] === 'super_admin' && $actor['role'] !== 'super_admin') $reject('Only a System Administrator can manage this account.', 403);
            // The UI displays super_admin as Admin; changing status must preserve that role.
            $newRole = $target['role'] === 'super_admin' && $role === 'admin' ? 'super_admin' : $role;
            $active = $status === 'Active' ? 1 : 0;
            $sensitive = $reset || $newRole !== $target['role'] || $active !== (int) $target['is_active'];
            if ($sensitive) {
                $password = $input['adminPassword'] ?? '';
                if (!is_string($password) || !password_verify($password, $actor['password'])) $reject('Your administrator password is incorrect.', 403);
                if ((int) $target['id'] === (int) $actor['id'] && (!$active || $newRole !== $target['role'])) $reject('You cannot deactivate or demote your own account.', 403);
                if ($target['is_active'] && in_array($target['role'], ['admin','super_admin'], true)
                    && (!$active || !in_array($newRole, ['admin','super_admin'], true)) && $activeAdmins <= 1) $reject('The last active administrator cannot be deactivated or demoted.', 403);
            }
            $values = ['name' => $name, 'role' => $newRole, 'active' => $active, 'increment' => $sensitive ? 1 : 0, 'id' => $target['id']];
            $resetSql = '';
            if ($reset) {
                $resetSql = ',password=:password,must_change_password=1';
                $values['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            $pdo->prepare('UPDATE users SET name=:name,role=:role,is_active=:active,session_version=session_version+:increment,updated_at=NOW()' . $resetSql . ' WHERE id=:id')->execute($values);
            audit($admin, $reset ? 'user_password_reset' : 'user_updated', $target['email']);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            throw $error;
        }
        json_response(['success' => true, 'message' => 'User account updated successfully.']);
    }

}
