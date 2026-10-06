<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
$source = str_replace("\r\n", "\n", file_get_contents(APP_ROOT . '/public/index.php'));
preg_match('/function personnel_image\(.*?\n}\n/s', $source, $match);
if (empty($match[0])) throw new RuntimeException('Image validator missing.');
eval($match[0]);
foreach ([
    [[], '', '', false],
    [['HTTPS' => 'on'], '', '', true],
    [['HTTPS' => 'off'], 'true', '', true],
    [[], '', 'production', true],
    [['HTTP_X_FORWARDED_PROTO' => 'https'], '', '', false],
] as [$server, $render, $environment, $expected]) {
    if (request_uses_https($server, $render, $environment) !== $expected) {
        throw new RuntimeException('HTTPS security detection failed.');
    }
}
$png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0ioAAAAASUVORK5CYII=';
if (personnel_image(['photo' => $png], 'photo') !== $png) throw new RuntimeException('Valid image rejected.');
foreach (['data:image/png;base64,' . base64_encode('<?php echo "bad";'), str_replace('image/png', 'image/jpeg', $png)] as $invalid) {
    try {
        personnel_image(['photo' => $invalid], 'photo');
    } catch (InvalidArgumentException) {
        continue;
    }
    throw new RuntimeException('Invalid image accepted.');
}
echo "Security hardening checks passed.\n";
