<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/PdfReport.php';
if (!password_is_strong('StrongExample!123') || password_is_strong('weak')) {
    throw new RuntimeException('Password validation failed.');
}
$statusToday = new DateTimeImmutable('2026-10-04');
if (renewal_status_for_personnel(['date_of_validity' => '2026-10-03'], $statusToday) !== 'expired'
    || renewal_status_for_personnel(['date_of_validity' => '2026-11-01'], $statusToday) !== 'within'
    || renewal_status_for_personnel(['date_of_validity' => '2027-10-04'], $statusToday) !== 'renewed'
    || renewal_status_for_personnel(['inspection_status' => 'approved'], $statusToday) !== 'renewed'
    || renewal_status_for_personnel(['approved_status' => 'pending'], $statusToday) !== 'pending') {
    throw new RuntimeException('Personnel renewal status calculation failed.');
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
$inspectionView = render_view('admin_inspection', ['user' => $viewUser]);
if (str_contains($inspectionView, "return p.icsStatus === 'under';")
    || !str_contains($inspectionView, "p.inspectionStatus || 'pending'")) {
    throw new RuntimeException('Pending inspection filter regression detected.');
}
$router = file_get_contents(dirname(__DIR__) . '/public/index.php');
if (!is_string($router) || !str_contains($router, "'personnelUpdatedAt' => \$p['updated_at'] ?? \$p['created_at'] ?? null")) {
    throw new RuntimeException('Personnel update timestamp is missing from dashboard data.');
}
if (!str_contains($router, "'%s already exists under %s (Item #%d)%s.'")) {
    throw new RuntimeException('Duplicate personnel details must identify the existing record owner.');
}
if (!is_string($router)
    || !str_contains($router, "'/admin/inspection/save'")
    || !str_contains($router, "'/admin/inspection/notify-staff'")
    || !str_contains($router, '/staff/personnel/(\\d+)/notify$#')
    || !str_contains($router, 'https://api.brevo.com/v3/smtp/email')
    || !str_contains($router, "'email_sent'")
    || !str_contains($router, '/staff/ics/(\\d+)/send-inspection$#')
    || !str_contains($router, '/detail$#')) {
    throw new RuntimeException('Admin inspection workflow routes are missing.');
}
$staff = render_view('staff_dashboard', [
    'user' => (object) array_merge((array) $viewUser, ['role' => 'staff']),
    'initialDashboardData' => ['personnel' => []], 'initialActiveTab' => 'registration',
    'initialFocusItem' => null,
]);
if (!str_contains($staff, 'Staff Dashboard') || str_contains($staff, '{{')) {
    throw new RuntimeException('Staff dashboard rendering failed.');
}
if (!str_contains($staff, 'id="notifyEmailInput"')
    || !str_contains($staff, 'readonly aria-readonly="true"')
    || !str_contains($staff, 'Always use the address saved during personnel registration.')
    || !str_contains($staff, 'p.itemNumber == currentNotifyId')) {
    throw new RuntimeException('Registered personnel email must remain locked in the notification form.');
}
if (!str_contains($staff, 'Successfully sent for inspection')
    || !str_contains($staff, 'was successfully sent to the Admin for inspection.')) {
    throw new RuntimeException('Staff inspection submission success modal is missing.');
}
if (!str_contains($staff, 'value="status-asc" selected')
    || !str_contains($staff, 'const statusOrder = {new:0, pending:1, within:2, renewed:3, expired:4}')) {
    throw new RuntimeException('Personnel list status ordering is missing or incorrect.');
}
$inspectionReport = render_view('pdf.inspection_report', [
    'p' => [
        'last_name' => 'Dela Cruz', 'first_name' => 'Juan', 'middle_name' => 'Santos',
        'rank' => 'SGT', 'email' => 'juan@example.com', 'unit' => '4ID',
        'date_of_birth' => '1990-01-01', 'afp_serial_number' => 'AFP-001',
        'pistol_nomenclature' => 'Pistol 9mm', 'pistol_type' => 'Glock 17',
        'pistol_serial_number' => 'P-001', 'date_of_validity' => null,
    ],
    'inspection' => ['status' => 'under', 'barrel' => 'serviceable'],
]);
if (!str_contains($inspectionReport, 'INSPECTION REPORT OF SERVICEABLE AND UNSERVICEABLE FIREARMS')
    || str_contains($inspectionReport, 'Carbon\\Carbon')
    || str_contains($inspectionReport, '$__env')) {
    throw new RuntimeException('Plain PHP inspection report rendering failed.');
}
echo "Smoke checks passed.\n";
