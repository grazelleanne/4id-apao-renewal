<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/PdfReport.php';
if (!password_is_strong('StrongExample!123') || password_is_strong('weak')) {
    throw new RuntimeException('Password validation failed.');
}
$method = new ReflectionMethod(PdfReport::class, 'build');
$pdf = $method->invoke(null, ['Test', 'Hello (PHP)']);
if (!str_starts_with($pdf, "%PDF-1.4\n") || !str_ends_with($pdf, '%%EOF')
    || !preg_match('/startxref\n(\d+)\n%%EOF$/', $pdf, $match)
    || substr($pdf, (int) $match[1], 4) !== 'xref') {
    throw new RuntimeException('PDF structure validation failed.');
}
$sql = file_get_contents(dirname(__DIR__) . '/database/schema.sql');
if (!is_string($sql) || !preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?personnel\b/i', $sql)
    || preg_match('/^\s*DROP\s/im', $sql)) {
    throw new RuntimeException('Schema validation failed.');
}
$viewUser = (object) [
    'id' => 1, 'name' => 'Test User', 'email' => 'test@example.com', 'role' => 'admin',
];
foreach (['login', 'forgot_password', 'admin_dashboard', 'admin_personnel', 'admin_inspection',
          'admin_reports', 'admin_archive', 'admin_users', 'admin_audit'] as $view) {
    $html = render_view($view, ['user' => $viewUser, 'captchaQuestion' => '1 + 1 = ?']);
    if (!str_contains(strtolower($html), '<!doctype html>') || str_contains($html, '{{')) {
        throw new RuntimeException("View rendering failed: {$view}");
    }
}
$staff = render_view('staff_dashboard', [
    'user' => (object) array_merge((array) $viewUser, ['role' => 'staff']),
    'initialDashboardData' => ['personnel' => []], 'initialActiveTab' => 'registration',
    'initialFocusItem' => null,
]);
if (!str_contains($staff, 'Staff Dashboard') || str_contains($staff, '{{')) {
    throw new RuntimeException('Staff dashboard rendering failed.');
}
echo "Smoke checks passed.\n";
