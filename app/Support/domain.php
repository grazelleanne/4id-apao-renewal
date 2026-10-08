<?php
declare(strict_types=1);

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rate_limit(string $key, int $limit, int $window): bool
{
    $dir = APP_ROOT . '/storage/limits';
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
        throw new RuntimeException('Cannot initialize private rate-limit storage.');
    }
    $file = $dir . '/' . hash('sha256', $key) . '.json';
    $handle = fopen($file, 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) {
        throw new RuntimeException('Cannot lock rate-limit state.');
    }
    try {
        $data = json_decode((string) stream_get_contents($handle), true);
        $now = time();
        if (!is_array($data) || ($data['start'] ?? 0) + $window <= $now) {
            $data = ['start' => $now, 'count' => 0];
        }
        if ($data['count'] >= $limit) {
            return false;
        }
        $data['count']++;
        rewind($handle);
        ftruncate($handle, 0);
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        if (fwrite($handle, $encoded) !== strlen($encoded) || !fflush($handle)) {
            throw new RuntimeException('Cannot persist rate-limit state.');
        }
        return true;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function password_is_strong(string $password): bool
{
    return strlen($password) >= 8 && strlen($password) <= 1024
        && preg_match('/[A-Z]/', $password) && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password) && preg_match('/[^A-Za-z0-9]/', $password);
}

function login_attempts(string $key, string $action = 'check'): array
{
    $dir = APP_ROOT . '/storage/limits';
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Cannot initialize login protection.');
    $handle = fopen($dir . '/login-' . hash('sha256', $key) . '.json', 'c+');
    if (!$handle || !flock($handle, LOCK_EX)) throw new RuntimeException('Cannot lock login protection.');
    try {
        $state = json_decode((string) stream_get_contents($handle), true);
        $now = time();
        if (!is_array($state) || ($state['until'] ?? 0) <= $now) $state = ['count' => 0, 'until' => $now + 180];
        if ($action === 'reset') $state = ['count' => 0, 'until' => $now + 180];
        if ($action === 'fail' && $state['count'] < 5) {
            $state['count']++;
            if ($state['count'] === 5) $state['until'] = $now + 180;
        }
        rewind($handle);
        ftruncate($handle, 0);
        $encoded = json_encode($state, JSON_THROW_ON_ERROR);
        if (fwrite($handle, $encoded) !== strlen($encoded) || !fflush($handle)) throw new RuntimeException('Cannot save login protection.');
        return ['remaining' => max(0, 5 - $state['count']), 'retryAfter' => $state['count'] >= 5 ? max(0, $state['until'] - $now) : 0];
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function birthday_renewal_validity(string $birthday, ?DateTimeImmutable $renewedAt = null): string
{
    $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthday);
    if (!$birth || $birth->format('Y-m-d') !== $birthday) {
        throw new InvalidArgumentException('A valid personnel date of birth is required for renewal.');
    }
    $renewedAt ??= new DateTimeImmutable('today');
    $year = (int) $renewedAt->format('Y') + 2;
    $month = (int) $birth->format('m');
    $day = (int) $birth->format('d');
    if (!checkdate($month, $day, $year)) $day = 28;
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function renewal_status_for_personnel(array $personnel, ?DateTimeImmutable $today = null): string
{
    $today ??= new DateTimeImmutable('today');
    if (strtolower(trim((string) ($personnel['approved_status'] ?? ''))) === 'new'
        && strtolower(trim((string) ($personnel['inspection_status'] ?? ''))) !== 'approved'
        && strtolower(trim((string) ($personnel['ics_status'] ?? ''))) !== 'ready') {
        return 'new';
    }
    $validity = $personnel['date_of_validity'] ?? null;
    if (is_string($validity) && trim($validity) !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr($validity, 0, 10));
        if ($date instanceof DateTimeImmutable) {
            if ($date < $today) {
                return 'expired';
            }
            if ($date <= $today->modify('+60 days')) {
                return 'within';
            }
            return 'renewed';
        }
    }
    $inspectionStatus = strtolower(trim((string) ($personnel['inspection_status'] ?? '')));
    $icsStatus = strtolower(trim((string) ($personnel['ics_status'] ?? '')));
    if ($inspectionStatus === 'approved' || $icsStatus === 'ready') {
        return 'renewed';
    }
    $stored = strtolower(trim((string) ($personnel['approved_status'] ?? 'pending')));
    return in_array($stored, ['renewed', 'within', 'expired', 'new', 'pending'], true) ? $stored : 'pending';
}

function audit(array $user, string $action, string $subject): void
{
    $query = db()->prepare('INSERT INTO audit_logs (user_id,user_name,user_role,action,subject,description,ip_address) VALUES (:id,:name,:role,:action,:subject,:description,:ip)');
    $query->execute([
        'id' => $user['id'] ?? null, 'name' => $user['name'] ?? 'System',
        'role' => $user['role'] ?? 'system', 'action' => $action, 'subject' => $subject,
        'description' => '{}', 'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
    ]);
}

function page(string $title, string $body): string
{
    return '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . h($title) . ' - APAO</title><style>body{font:16px system-ui;background:#f0f4f8;color:#1a202c;margin:0}'
        . 'header{background:#1b4332;color:white;padding:1rem 2rem}main{max-width:1000px;margin:2rem auto;padding:1.5rem;background:white;border-radius:12px}'
        . 'a{color:#2d6a4f}label{display:block;margin:.8rem 0 .25rem}input{padding:.65rem;width:min(100%,30rem);border:1px solid #aab4bd;border-radius:6px}'
        . 'button{padding:.65rem 1rem;border:0;border-radius:6px;background:#2d6a4f;color:white}table{border-collapse:collapse;width:100%}'
        . 'td,th{text-align:left;padding:.6rem;border-bottom:1px solid #ddd}.error{color:#b91c1c}nav{display:flex;gap:1rem}</style>'
        . '<body><header><strong>APAO Renewal System</strong></header><main>' . $body . '</main></body></html>';
}
