<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/PdfReport.php';
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($path === '/login' && $method === 'GET') {
        if (current_user()) {
            redirect('/dashboard');
        }
        show_login();
    }
    if ($path === '/login' && $method === 'POST') {
        login();
    }
    if ($path === '/logout' && $method === 'POST') {
        require_post();
        $user = current_user();
        if ($user) {
            audit($user, 'logout', $user['email']);
        }
        $_SESSION = [];
        session_regenerate_id(true);
        redirect('/login');
    }
    if ($path === '/session/status') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(current_user() ? 200 : 401);
        echo json_encode(['authenticated' => current_user() !== null], JSON_THROW_ON_ERROR);
        exit;
    }
    if ($path === '/login/captcha' && $method === 'GET') {
        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $_SESSION['captcha'] = $left + $right;
        json_response(['question' => "{$left} + {$right} = ?"]);
    }
    if ($path === '/forgot-password' && $method === 'GET') {
        echo render_view('forgot_password');
        exit;
    }
    if ($path === '/register') {
        redirect('/login');
    }
    if ($path === '/admin/dashboard-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        dashboard_data();
    }
    if (($path === '/admin/personnel-data' || $path === '/staff/dashboard-data') && $method === 'GET') {
        require_user(['super_admin','admin','staff']);
        personnel_data();
    }
    if ($path === '/admin/archive-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        archive_data();
    }
    if ($path === '/admin/users-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        users_data();
    }
    if ($path === '/admin/audit-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        audit_data();
    }
    if (in_array($path, ['/admin/notifications','/staff/notifications'], true) && $method === 'GET') {
        $user = require_user(['super_admin','admin','staff']);
        notifications_data($user);
    }
    if (in_array($path, ['/admin/notifications/read','/staff/notifications/read'], true) && $method === 'POST') {
        require_post();
        $user = require_user(['super_admin','admin','staff']);
        $field = $user['role'] === 'staff' ? 'read_by_staff' : 'read_by_admin';
        db()->exec("UPDATE notifications SET {$field}=1,updated_at=NOW() WHERE {$field}=0");
        json_response(['success' => true]);
    }
    if ($path === '/admin/inspection-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        inspection_data();
    }
    $adminViews = [
        '/admin/dashboard' => 'admin_dashboard', '/admin/personnel' => 'admin_personnel',
        '/admin/inspection' => 'admin_inspection', '/admin/reports' => 'admin_reports',
        '/admin/archive' => 'admin_archive', '/admin/users' => 'admin_users', '/admin/audit' => 'admin_audit',
    ];
    if (isset($adminViews[$path]) && $method === 'GET') {
        $user = require_user(['super_admin','admin']);
        echo render_view($adminViews[$path], ['user' => (object) $user]);
        exit;
    }
    if ($path === '/staff/dashboard' && $method === 'GET') {
        $user = require_user(['staff','super_admin','admin']);
        echo render_view('staff_dashboard', [
            'user' => (object) $user,
            'initialDashboardData' => ['personnel' => personnel_rows()],
            'initialActiveTab' => is_string($_GET['tab'] ?? null) ? $_GET['tab'] : 'registration',
            'initialFocusItem' => is_string($_GET['item'] ?? null) ? $_GET['item'] : null,
        ]);
        exit;
    }
    if ($path === '/' || $path === '/dashboard') {
        $user = require_user(['super_admin','admin','staff']);
        redirect($user['role'] === 'staff' ? '/staff/dashboard' : '/admin/dashboard');
    }
    if ($path === '/personnel' && $method === 'GET') {
        personnel_list();
    }
    if ($path === '/par' && $method === 'GET') {
        par_list();
    }
    if (preg_match('#^/personnel/(\d+)/pdf$#', $path, $m)) {
        personnel_pdf((int) $m[1]);
    }
    if (preg_match('#^/inspection/(\d+)/pdf$#', $path, $m)) {
        inspection_pdf((int) $m[1]);
    }
    if (preg_match('#^/par/(\d+)/pdf$#', $path, $m)) {
        par_pdf((int) $m[1]);
    }
    page_error('Page not found.', 404);
} catch (Throwable $error) {
    error_log('[APAO PHP] ' . $error->getMessage());
    page_error('The request could not be completed. Check the server log or contact the administrator.', 500);
}

function show_login(?string $error = null): never
{
    $left = random_int(1, 9);
    $right = random_int(1, 9);
    $_SESSION['captcha'] = $left + $right;
    echo render_view('login', [
        'captchaQuestion' => "{$left} + {$right} = ?",
        'loginError' => $error,
    ]);
    exit;
}

function login(): never
{
    require_post();
    $input = request_data();
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $captcha = $input['captcha'] ?? '';
    $answer = $_SESSION['captcha'] ?? null;
    unset($_SESSION['captcha']);
    $email = is_string($email) ? strtolower(trim($email)) : '';
    $password = is_string($password) ? $password : '';
    $captcha = is_string($captcha) ? $captcha : '';
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = 'login:' . $email . '|' . $ip;
    if (!rate_limit($key, 5, 300)) {
        login_error('Too many failed attempts. Wait five minutes before trying again.', 429);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 4096
        || $answer === null || !ctype_digit($captcha) || (int) $captcha !== (int) $answer) {
        login_error('Invalid details or security answer. Try the new question.', 422);
    }
    $query = db()->prepare('SELECT id,name,email,password,role,is_active,session_version FROM users WHERE LOWER(email)=:email LIMIT 1');
    $query->execute(['email' => $email]);
    $user = $query->fetch();
    $dummy = '$2y$12$MXfSXi/zXc56DdJMxnzQvueXTWnKjf1K9QAiKQWwmFXTCCbn488y2';
    if (!password_verify($password, (string) ($user['password'] ?? $dummy)) || !$user) {
        if ($user) {
            audit(['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']], 'login_failed', $email);
        }
        login_error('Invalid email or password.', 401);
    }
    if (!(int) $user['is_active'] || !in_array($user['role'], ['super_admin','admin','staff'], true)) {
        login_error('This account cannot access the system.', 403);
    }
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'],
        'role' => $user['role'], 'session_version' => (int) $user['session_version'],
    ];
    $_SESSION['_last_activity'] = time();
    db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=:id')->execute(['id' => $user['id']]);
    audit($_SESSION['user'], 'login', $email);
    json_response([
        'success' => true,
        'message' => 'Welcome back, ' . $user['name'] . '!',
        'redirect' => $user['role'] === 'staff' ? '/staff/dashboard' : '/admin/dashboard',
    ]);
}

function login_error(string $message, int $status): never
{
    $left = random_int(1, 9);
    $right = random_int(1, 9);
    $_SESSION['captcha'] = $left + $right;
    $acceptsJson = str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
        || str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json');
    if ($acceptsJson) {
        json_response(['success' => false, 'message' => $message, 'captcha_question' => "{$left} + {$right} = ?"], $status);
    }
    show_login($message);
}

function personnel_list(): never
{
    require_user(['super_admin','admin','staff']);
    $q = $_GET['q'] ?? '';
    if (!is_string($q) || strlen($q) > 100) {
        page_error('Invalid search.', 422);
    }
    $statement = db()->prepare(
        'SELECT id,item_number,`rank`,first_name,middle_name,last_name,afp_serial_number,unit,approved_status,date_of_validity
         FROM personnel WHERE archived_at IS NULL AND (:empty = "" OR first_name LIKE :q1 OR middle_name LIKE :q2
         OR last_name LIKE :q3 OR afp_serial_number LIKE :q4 OR CAST(item_number AS CHAR) LIKE :q5)
         ORDER BY item_number LIMIT 500'
    );
    $needle = '%' . trim($q) . '%';
    $statement->execute(['empty' => $q, 'q1' => $needle, 'q2' => $needle, 'q3' => $needle, 'q4' => $needle, 'q5' => $needle]);
    $html = '<h1>Personnel</h1><p><a href="/dashboard">Dashboard</a></p><form><label>Search</label>'
        . '<input name="q" maxlength="100" value="' . h($q) . '"><button>Search</button></form>'
        . '<table><tr><th>Item</th><th>Name</th><th>Serial</th><th>Status</th><th>PDF</th></tr>';
    foreach ($statement->fetchAll() as $person) {
        $name = trim(implode(' ', array_filter([$person['rank'],$person['first_name'],$person['middle_name'],$person['last_name']])));
        $html .= '<tr><td>' . h($person['item_number']) . '</td><td>' . h($name) . '</td><td>'
            . h($person['afp_serial_number']) . '</td><td>' . h($person['approved_status'])
            . '</td><td><a href="/personnel/' . (int) $person['id'] . '/pdf">Record</a> · '
            . '<a href="/inspection/' . (int) $person['item_number'] . '/pdf">Inspection</a></td></tr>';
    }
    echo page('Personnel', $html . '</table><p>First 500 matching records.</p>');
    exit;
}

function par_list(): never
{
    require_user(['super_admin','admin','staff']);
    $rows = db()->query('SELECT r.id,r.par_number,r.status,r.issued_date,p.item_number,p.first_name,p.last_name FROM property_acknowledgement_receipts r JOIN personnel p ON p.id=r.personnel_id ORDER BY r.id DESC LIMIT 500');
    $html = '<h1>PAR records</h1><p><a href="/dashboard">Dashboard</a></p><table><tr><th>PAR</th><th>Personnel</th><th>Status</th><th>Issued</th><th>PDF</th></tr>';
    foreach ($rows->fetchAll() as $row) {
        $html .= '<tr><td>' . h($row['par_number']) . '</td><td>' . h($row['first_name'] . ' ' . $row['last_name'])
            . ' (' . h($row['item_number']) . ')</td><td>' . h($row['status']) . '</td><td>'
            . h($row['issued_date']) . '</td><td><a href="/par/' . (int) $row['id'] . '/pdf">Download</a></td></tr>';
    }
    echo page('PAR records', $html . '</table>');
    exit;
}

function personnel_pdf(int $id): never
{
    $user = require_user(['super_admin','admin','staff']);
    $statement = db()->prepare('SELECT * FROM personnel WHERE id=:id');
    $statement->execute(['id' => $id]);
    $p = $statement->fetch();
    if (!$p) {
        page_error('Personnel record not found.', 404);
    }
    audit($user, 'personnel_pdf_generated', (string) $p['item_number']);
    PdfReport::download('personnel-' . (int) $p['item_number'] . '.pdf', 'Personnel Record', [
        'Item number' => $p['item_number'], 'Name' => trim($p['rank'] . ' ' . $p['first_name'] . ' ' . $p['middle_name'] . ' ' . $p['last_name']),
        'Date of birth' => $p['date_of_birth'], 'Citizenship' => $p['citizenship'], 'Email' => $p['email'],
        'Contact number' => $p['contact_number'], 'AFP serial' => $p['afp_serial_number'], 'Unit' => $p['unit'],
        'Firearm' => $p['pistol_nomenclature'], 'Firearm serial' => $p['pistol_serial_number'],
        'Status' => $p['approved_status'], 'Valid until' => $p['date_of_validity'],
    ]);
}

function inspection_pdf(int $itemNumber): never
{
    $user = require_user(['super_admin','admin','staff']);
    $pQuery = db()->prepare('SELECT * FROM personnel WHERE item_number=:item');
    $pQuery->execute(['item' => $itemNumber]);
    $p = $pQuery->fetch();
    if (!$p) {
        page_error('Personnel record not found.', 404);
    }
    $iQuery = db()->prepare('SELECT * FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1');
    $iQuery->execute(['item' => $itemNumber]);
    $i = $iQuery->fetch() ?: [];
    audit($user, 'inspection_pdf_generated', (string) $itemNumber);
    $fields = ['Item number' => $itemNumber, 'Name' => $p['first_name'] . ' ' . $p['last_name'],
        'Firearm' => $p['pistol_nomenclature'], 'Serial' => $p['pistol_serial_number'],
        'Inspection status' => $i['status'] ?? 'Not inspected', 'Remarks' => $i['remarks'] ?? '',
        'Next renewal' => $i['next_renewal_date'] ?? ''];
    foreach (['barrel','slide','frame','trigger','firing_pin','extractor','front_sight','rear_sight'] as $part) {
        $fields[ucwords(str_replace('_', ' ', $part))] = $i[$part] ?? '';
    }
    PdfReport::download("inspection-{$itemNumber}.pdf", 'Firearm Inspection Report', $fields);
}

function par_pdf(int $id): never
{
    $user = require_user(['super_admin','admin','staff']);
    $query = db()->prepare('SELECT r.*,p.item_number,p.first_name,p.middle_name,p.last_name,p.`rank` AS `rank`,p.afp_serial_number FROM property_acknowledgement_receipts r JOIN personnel p ON p.id=r.personnel_id WHERE r.id=:id');
    $query->execute(['id' => $id]);
    $r = $query->fetch();
    if (!$r) {
        page_error('PAR record not found.', 404);
    }
    audit($user, 'par_pdf_generated', $r['par_number']);
    PdfReport::download($r['par_number'] . '.pdf', 'Property Acknowledgement Receipt', [
        'PAR number' => $r['par_number'], 'Personnel' => $r['rank'] . ' ' . $r['first_name'] . ' ' . $r['middle_name'] . ' ' . $r['last_name'],
        'Item number' => $r['item_number'], 'AFP serial' => $r['afp_serial_number'], 'Unit' => $r['unit'],
        'Firearm' => $r['firearm'], 'Firearm serial' => $r['firearm_serial_number'],
        'Ammunition quantity' => $r['ammunition_quantity'], 'Status' => $r['status'],
        'Issued date' => $r['issued_date'], 'Valid until' => $r['valid_until'],
        'Issued by' => $r['issued_by'], 'Approved by' => $r['approved_by'], 'Remarks' => $r['remarks'],
    ]);
}

function personnel_rows(bool $archived = false): array
{
    $statement = db()->prepare(
        'SELECT p.*, i.status AS inspection_status, i.remarks AS inspection_remarks,
                i.updated_at AS inspection_updated_at
         FROM personnel p
         LEFT JOIN inspections i ON i.id = (
             SELECT i2.id FROM inspections i2 WHERE i2.item_number=p.item_number ORDER BY i2.id DESC LIMIT 1
         )
         WHERE ' . ($archived ? 'p.archived_at IS NOT NULL' : 'p.archived_at IS NULL') . '
         ORDER BY p.item_number'
    );
    $statement->execute();
    return array_map(static function (array $p): array {
        return [
            'id' => (int) $p['id'], 'itemNumber' => (int) $p['item_number'],
            'dateOfValidity' => $p['date_of_validity'], 'lastName' => $p['last_name'] ?? '',
            'firstName' => $p['first_name'] ?? '', 'middleName' => $p['middle_name'] ?? '',
            'rank' => $p['rank'] ?? '', 'afpSerialNumber' => $p['afp_serial_number'] ?? '',
            'afosMos' => $p['afos_mos'] ?? '', 'branch' => $p['branch'] ?? '',
            'email' => $p['email'] ?? '', 'contactNumber' => $p['contact_number'] ?? '',
            'issuedBy' => $p['issued_by'] ?? '', 'dateOfBirth' => $p['date_of_birth'],
            'citizenship' => $p['citizenship'] ?? 'Filipino', 'civilStatus' => $p['civil_status'] ?? '',
            'pistolNomenclature' => $p['pistol_nomenclature'] ?? '',
            'pistolSerialNumber' => $p['pistol_serial_number'] ?? '',
            'pistolType' => $p['pistol_type'] ?? '', 'parNumber' => $p['par_number'] ?? '',
            'qtyAmmo' => (int) ($p['qty_ammo'] ?? 0), 'unit' => $p['unit'] ?? '',
            'approvedStatus' => $p['approved_status'] ?? 'pending', 'status' => $p['status'] ?? 'active',
            'icsStatus' => $p['ics_status'] ?? 'inspection', 'dateApproved' => $p['date_approved'],
            'photo' => $p['photo'], 'signature' => $p['signature'],
            'inspectionStatus' => $p['inspection_status'] ?? null,
            'inspectionResult' => $p['inspection_remarks'] ?? '',
            'inspectionRemarks' => $p['inspection_remarks'] ?? '',
            'inspectionUpdatedAt' => $p['inspection_updated_at'] ?? null,
            'dateArchived' => $p['archived_at'] ?? null,
        ];
    }, $statement->fetchAll());
}

function personnel_data(): never
{
    json_response(['success' => true, 'personnel' => personnel_rows(), 'data' => personnel_rows()]);
}

function dashboard_data(): never
{
    $rows = personnel_rows();
    $counts = ['totalNew' => 0, 'totalRenewed' => 0, 'withinRenewal' => 0, 'expired' => 0, 'pending' => 0];
    $map = ['new' => 'totalNew', 'renewed' => 'totalRenewed', 'within' => 'withinRenewal', 'expired' => 'expired', 'pending' => 'pending'];
    foreach ($rows as $row) {
        $key = $map[$row['approvedStatus']] ?? 'pending';
        $counts[$key]++;
    }
    $activities = db()->query('SELECT user_name,user_role,action,subject,target,created_at FROM audit_logs ORDER BY id DESC LIMIT 8')->fetchAll();
    json_response([
        'success' => true,
        'totalUsers' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'metrics' => $counts,
        'personnel' => $rows,
        'recentActivity' => array_map(static fn (array $row): array => [
            'userName' => $row['user_name'] ?? 'System', 'userRole' => $row['user_role'] ?? 'system',
            'action' => $row['action'] ?? '', 'target' => $row['subject'] ?? $row['target'] ?? '',
            'createdAt' => $row['created_at'],
        ], $activities),
    ]);
}

function archive_data(): never
{
    json_response(['success' => true, 'data' => personnel_rows(true)]);
}

function users_data(): never
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

function audit_data(): never
{
    $rows = db()->query('SELECT * FROM audit_logs ORDER BY id DESC LIMIT 1000')->fetchAll();
    $logs = array_map(static fn (array $row): array => [
        'id' => (int) $row['id'], 'userName' => $row['user_name'] ?? 'System',
        'userRole' => $row['user_role'] ?? 'system', 'action' => $row['action'] ?? '',
        'target' => $row['target'] ?? $row['subject'] ?? '',
        'details' => json_decode((string) ($row['description'] ?? '{}'), true) ?: [],
        'description' => $row['description'] ?? '',
        'ipAddress' => $row['ip_address'] ?? '', 'createdAt' => $row['created_at'],
    ], $rows);
    json_response(['success' => true, 'data' => $logs, 'logs' => $logs, 'total' => count($logs)]);
}

function inspection_data(): never
{
    $rows = personnel_rows();
    $pending = 0;
    $under = 0;
    $approved = 0;
    foreach ($rows as &$row) {
        $row['dateRegistered'] = $row['dateApproved'] ?? null;
        $status = $row['inspectionStatus'];
        if ($status === 'approved') {
            $approved++;
        } elseif ($status === 'under' || $row['icsStatus'] === 'under') {
            $under++;
        } else {
            $pending++;
            $row['inspectionStatus'] = 'pending';
        }
    }
    unset($row);
    json_response(['success' => true, 'data' => $rows, 'pending' => $pending, 'under' => $under, 'approved' => $approved]);
}

function notifications_data(array $user): never
{
    $field = $user['role'] === 'staff' ? 'read_by_staff' : 'read_by_admin';
    $rows = db()->query("SELECT id,type,title,message,personnel_name,personnel_id,{$field} AS is_read,created_at FROM notifications ORDER BY id DESC LIMIT 50")->fetchAll();
    $notifications = array_map(static fn (array $row): array => [
        'id' => (int) $row['id'], 'type' => $row['type'], 'title' => $row['title'],
        'message' => $row['message'], 'personnelName' => $row['personnel_name'],
        'personnelId' => $row['personnel_id'], 'read' => (bool) $row['is_read'],
        'createdAt' => $row['created_at'],
    ], $rows);
    json_response([
        'success' => true, 'notifications' => $notifications,
        'unreadCount' => count(array_filter($notifications, static fn (array $row): bool => !$row['read'])),
    ]);
}
