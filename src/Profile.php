<?php
declare(strict_types=1);

function profile_update(array $user): never
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
    $pdo->beginTransaction();
    try {
        $sql = 'UPDATE users SET name=:name,email=:email,session_version=session_version+1,updated_at=NOW() WHERE id=:id AND session_version=:version';
        $values = ['name' => $name, 'email' => $email, 'id' => $user['id'], 'version' => $user['session_version']];
        if ($emailChanged) { $sql .= ' AND password=:old_password'; $values['old_password'] = $hash; }
        $query = $pdo->prepare($sql);
        $query->execute($values);
        if ($query->rowCount() !== 1) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Your account changed. Please sign in again.'], 409);
        }
        audit($user, 'profile_updated', $email);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062) {
            json_response(['success' => false, 'message' => 'This email address is already used by another account.'], 409);
        }
        throw $error;
    }
    session_regenerate_id(true);
    $_SESSION['user'] = array_replace($user, ['name' => $name, 'email' => $email, 'session_version' => $user['session_version'] + 1]);
    unset($_SESSION['action_otp']);
    json_response(['success' => true, 'message' => 'Profile updated successfully. Use your updated email the next time you sign in.', 'user' => ['name' => $name, 'email' => $email]]);
}
