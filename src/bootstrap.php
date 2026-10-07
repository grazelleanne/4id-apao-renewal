<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
require __DIR__ . '/View.php';

function load_env(string $file): void
{
    if (!is_file($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) {
            putenv(trim($key) . '=' . trim(trim($value), "\"'"));
        }
    }
}
load_env(APP_ROOT . '/.env');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
date_default_timezone_set('Asia/Manila');
// Render terminates TLS at its load balancer. Its runtime flag, rather than
// client-controlled forwarded headers, determines production cookie security.
$https = request_uses_https($_SERVER, (string) getenv('RENDER'), (string) getenv('APP_ENV'));
ini_set('expose_php', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('APAOSESSID');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax',
]);
session_start();

function request_uses_https(array $server, string $render = '', string $environment = ''): bool
{
    return (!empty($server['HTTPS']) && strtolower((string) $server['HTTPS']) !== 'off')
        || $render === 'true' || $environment === 'production';
}

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
function security_headers(): void
{
    if (request_uses_https($_SERVER, env_value('RENDER'), env_value('APP_ENV'))) {
        header('Strict-Transport-Security: max-age=31536000');
    }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
    header('Pragma: no-cache');
}
security_headers();
function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $host = env_value('DB_HOST', '127.0.0.1');
    $port = env_value('DB_PORT', '3306');
    $name = env_value('DB_DATABASE', 'apao_vanilla');
    if (!preg_match('/^[A-Za-z0-9_.-]+$/', $host) || !preg_match('/^\d{1,5}$/', $port)
        || !preg_match('/^[A-Za-z0-9_]+$/', $name)) {
        throw new RuntimeException('Invalid MySQL configuration.');
    }
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $sslCa = env_value('DB_SSL_CA');
    if ($sslCa !== '') {
        if (!is_file($sslCa) || !is_readable($sslCa)) {
            throw new RuntimeException('The configured MySQL CA certificate is not readable.');
        }
        $sslCaAttribute = PHP_VERSION_ID >= 80500
            ? Pdo\Mysql::ATTR_SSL_CA
            : PDO::MYSQL_ATTR_SSL_CA;
        $verifyCertificateAttribute = PHP_VERSION_ID >= 80500
            ? Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT
            : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;
        $options[$sslCaAttribute] = $sslCa;
        $options[$verifyCertificateAttribute] = true;
    }
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        env_value('DB_USERNAME'),
        env_value('DB_PASSWORD'),
        $options,
    );
    return $pdo;
}
function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string
{
    return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . h(csrf_token()) . '">';
}
function request_data(): array
{
    static $data;
    if (is_array($data)) {
        return $data;
    }
    $data = $_POST;
    $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($contentType, 'application/json')) {
        $decoded = json_decode((string) file_get_contents('php://input'), true);
        $data = is_array($decoded) ? $decoded : [];
    }
    return $data;
}
function request_expects_json(): bool
{
    return str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
        || str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json');
}
function require_post(array $allowedMethods = ['POST']): void
{
    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', $allowedMethods, true)) {
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
        }
        page_error('Method not allowed', 405);
    }
    $input = request_data();
    $token = $input['_token'] ?? $input['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Session expired. Refresh and retry.'], 419);
        }
        page_error('Session expired. Refresh and retry.', 419);
    }
}
function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    exit;
}
function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}
function require_user(array $roles = [], bool $allowTemporaryPassword = false): array
{
    $user = current_user();
    if (!$user) {
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Please sign in again.'], 401);
        }
        redirect('/login');
    }
    $lifetime = max(900, (int) env_value('APP_SESSION_LIFETIME', '7200'));
    if (time() - (int) ($_SESSION['_last_activity'] ?? 0) > $lifetime) {
        $_SESSION = [];
        session_regenerate_id(true);
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Your session expired. Please sign in again.'], 401);
        }
        redirect('/login');
    }
    $query = db()->prepare('SELECT * FROM users WHERE id=:id');
    $query->execute(['id' => $user['id'] ?? 0]);
    $fresh = $query->fetch();
    if (!$fresh || !(int) $fresh['is_active'] || (int) $fresh['session_version'] !== (int) ($user['session_version'] ?? 0)) {
        $_SESSION = [];
        session_regenerate_id(true);
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Your session is no longer valid. Please sign in again.'], 401);
        }
        redirect('/login');
    }
    $_SESSION['user'] = $user = [
        'id' => (int) $fresh['id'], 'name' => $fresh['name'], 'email' => $fresh['email'],
        'role' => $fresh['role'], 'session_version' => (int) $fresh['session_version'],
        'must_change_password' => (bool) ($fresh['must_change_password'] ?? false),
    ];
    $_SESSION['_last_activity'] = time();
    if ($roles && !in_array($user['role'], $roles, true)) {
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Access denied.'], 403);
        }
        page_error('Access denied.', 403);
    }
    if (!$allowTemporaryPassword && $user['must_change_password']) {
        $passwordRedirect = $user['role'] === 'staff' ? '/staff/first-password' : '/admin/first-password';
        if (request_expects_json()) {
            json_response(['success' => false, 'message' => 'Create your new password before accessing the system.', 'redirect' => $passwordRedirect], 403);
        }
        redirect($passwordRedirect);
    }
    // Read requests must not serialize dashboard, notification and detail queries
    // behind the same session-file lock.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        csrf_token();
        session_write_close();
    }
    return $user;
}
function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
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
function page_error(string $message, int $status = 500): never
{
    http_response_code($status);
    echo page('Request error', '<h1>' . h($message) . '</h1><p><a href="/login">Return</a></p>');
    exit;
}
