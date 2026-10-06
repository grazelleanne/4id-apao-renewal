<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

if (PHP_SAPI !== 'cli' || $argc !== 3) {
    fwrite(STDERR, "Usage: php bin/create-staff.php <email> <name>\n");
    exit(2);
}

$email = strtolower(trim($argv[1]));
$name = trim($argv[2]);
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '' || strlen($name) > 255) {
    fwrite(STDERR, "Enter a valid email and name (up to 255 characters).\n");
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
    fwrite(STDERR, "Use at least 8 characters with uppercase, lowercase, a number, and a symbol.\n");
    exit(2);
}

$query = db()->prepare(
    "INSERT INTO users (name,email,password,role,is_active,session_version,created_at,updated_at)
     VALUES (:n,:e,:p,'staff',1,1,NOW(),NOW())"
);
try {
    $query->execute([
        'n' => $name,
        'e' => $email,
        'p' => password_hash($password, PASSWORD_DEFAULT),
    ]);
} catch (PDOException $exception) {
    if ((string) $exception->getCode() === '23000') {
        fwrite(STDERR, "An account already uses that email.\n");
        exit(1);
    }
    throw $exception;
}

fwrite(STDOUT, "Staff account created.\n");
