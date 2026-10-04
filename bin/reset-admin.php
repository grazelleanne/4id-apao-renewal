<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI !== 'cli' || $argc !== 3) {
    fwrite(STDERR, "Usage: php bin/reset-admin.php <current-email> <new-email>\n");
    exit(2);
}

$currentEmail = strtolower(trim($argv[1]));
$newEmail = strtolower(trim($argv[2]));
if (!filter_var($currentEmail, FILTER_VALIDATE_EMAIL) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Enter valid current and new email addresses.\n");
    exit(2);
}

fwrite(STDOUT, 'New password: ');
if (PHP_OS_FAMILY === 'Windows') {
    $script = '$b=New-Object System.Text.StringBuilder; while ($true) { $k=[Console]::ReadKey($true); if ($k.Key -eq [ConsoleKey]::Enter) { break }; if ($k.Key -eq [ConsoleKey]::Backspace) { if ($b.Length -gt 0) { $b.Length-- }; continue }; [void]$b.Append($k.KeyChar) }; [Console]::WriteLine(); [Console]::Write($b.ToString())';
    $encoded = base64_encode((string) iconv('UTF-8', 'UTF-16LE', $script));
    $password = shell_exec('powershell.exe -NoProfile -EncodedCommand ' . escapeshellarg($encoded));
} else {
    $stty = shell_exec('stty -g 2>/dev/null');
    if (!is_string($stty) || $stty === '') {
        throw new RuntimeException('Run this command from an interactive terminal.');
    }
    shell_exec('stty -echo');
    try {
        $password = fgets(STDIN);
    } finally {
        shell_exec('stty ' . escapeshellarg(trim($stty)));
        fwrite(STDOUT, "\n");
    }
}

$password = trim((string) $password);
if (!password_is_strong($password)) {
    fwrite(STDERR, "Use at least 12 characters with uppercase, lowercase, a number, and a symbol.\n");
    exit(2);
}

$query = db()->prepare(
    "UPDATE users
     SET email=:new_email, password=:password, session_version=session_version+1, updated_at=NOW()
     WHERE LOWER(email)=:current_email AND role IN ('super_admin','admin')"
);
try {
    $query->execute([
        'new_email' => $newEmail,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'current_email' => $currentEmail,
    ]);
} catch (PDOException $exception) {
    if ((string) $exception->getCode() === '23000') {
        fwrite(STDERR, "Another account already uses the new email address.\n");
        exit(1);
    }
    throw $exception;
}

if ($query->rowCount() !== 1) {
    fwrite(STDERR, "No administrator account uses the current email address.\n");
    exit(1);
}

fwrite(STDOUT, "Administrator email and password updated; existing sessions were invalidated.\n");
