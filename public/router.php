<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$decodedPath = is_string($path) ? rawurldecode($path) : '';
if (preg_match('#(?:^|/)\.|\.(?:env|sql|log|ini|bak|pem|key)$#i', $decodedPath)
    || $decodedPath === '/router.php') {
    http_response_code(404);
    exit;
}
$root = realpath(__DIR__);
$file = is_string($path) ? realpath(__DIR__ . $path) : false;
if ($root !== false && $file !== false && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)) {
    return false;
}
require __DIR__ . '/index.php';
