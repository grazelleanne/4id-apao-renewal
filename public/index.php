<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require dirname(__DIR__) . '/src/PdfReport.php';
$path = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($path === '/staff/first-password' && in_array($method, ['GET', 'POST'], true)) {
        if ($method === 'POST') require_post();
        $user = require_user(['staff'], true);
        if ($method === 'POST') staff_first_password($user);
        if (!$user['must_change_password']) redirect('/staff/dashboard');
        echo render_view('staff_first_password', ['user' => (object) $user]);
        exit;
    }
    if (preg_match('#^/admin/personnel-data/(\d+)$#', $path, $matches) && in_array($method, ['PUT', 'DELETE'], true)) {
        require_post(['PUT', 'DELETE']);
        $user = require_user(['super_admin', 'admin']);
        personnel_change((int) $matches[1], $user, $method === 'DELETE');
    }
    if (preg_match('#^/personnel/(\d+)/image/(photo|signature)$#', $path, $matches) && $method === 'GET') {
        require_user(['super_admin', 'admin', 'staff']);
        personnel_image_response((int) $matches[1], $matches[2]);
    }
    if ($path === '/admin/archive/restore' && $method === 'POST') {
        require_post();
        $user = require_user(['super_admin', 'admin']);
        personnel_restore($user);
    }
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
    if ($path === '/staff/personnel/availability' && $method === 'POST') {
        require_post();
        require_user(['staff','super_admin','admin']);
        personnel_availability();
    }
    if ($path === '/staff/personnel' && $method === 'POST') {
        require_post();
        $user = require_user(['staff','super_admin','admin']);
        personnel_store($user);
    }
    if (preg_match('#^/staff/personnel/(\d+)/notify$#', $path, $matches) && $method === 'POST') {
        require_post();
        $user = require_user(['staff','super_admin','admin']);
        staff_notify_personnel((int) $matches[1], $user);
    }
    if (preg_match('#^/staff/ics/(\d+)/send-inspection$#', $path, $matches) && $method === 'POST') {
        require_post();
        $user = require_user(['staff','super_admin','admin']);
        ics_send_for_inspection((int) $matches[1], $user);
    }
    if ($path === '/admin/archive-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        archive_data();
    }
    if ($path === '/admin/users-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        users_data();
    }
    if ($path === '/admin/users' && $method === 'POST') {
        require_post();
        $user = require_user(['super_admin','admin']);
        users_store($user);
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
        notifications_mark_read($user);
    }
    if ($path === '/admin/inspection-data' && $method === 'GET') {
        require_user(['super_admin','admin']);
        inspection_data();
    }
    if (preg_match('#^/admin/personnel/(\d+)/renewal-history$#', $path, $matches) && $method === 'GET') {
        require_user(['super_admin','admin']);
        personnel_renewal_history((int) $matches[1]);
    }
    if (preg_match('#^/admin/inspection/(\d+)/detail$#', $path, $matches) && $method === 'GET') {
        require_user(['super_admin','admin']);
        inspection_detail((int) $matches[1]);
    }
    if ($path === '/admin/inspection/save' && $method === 'POST') {
        require_post();
        $user = require_user(['super_admin','admin']);
        inspection_save($user);
    }
    if ($path === '/admin/inspection/notify-staff' && $method === 'POST') {
        require_post();
        $user = require_user(['super_admin','admin']);
        inspection_notify_staff($user);
    }
    if (preg_match('#^/admin/inspection/(\d+)/print$#', $path, $matches) && $method === 'GET') {
        inspection_pdf((int) $matches[1]);
    }
    $adminViews = [
        '/admin/dashboard' => 'admin_dashboard', '/admin/personnel' => 'admin_personnel',
        '/admin/inspection' => 'admin_inspection', '/admin/reports' => 'admin_reports',
        '/admin/archive' => 'admin_archive', '/admin/users' => 'admin_users', '/admin/audit' => 'admin_audit',
    ];
    if (isset($adminViews[$path]) && $method === 'GET') {
        $user = require_user(['super_admin','admin']);
        $viewData = ['user' => (object) $user];
        if ($path === '/admin/reports') $viewData['initialPersonnel'] = personnel_rows();
        echo render_view($adminViews[$path], $viewData);
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
    if (request_expects_json()) {
        json_response(['success' => false, 'message' => 'The request could not be completed. Please try again.'], 500);
    }
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
    unset($_SESSION['login_otp']);
    if (login_attempts($key)['retryAfter'] > 0) {
        login_error('Five failed attempts. Wait 3 minutes before trying again.', 429);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 4096
        || $answer === null || !ctype_digit($captcha) || (int) $captcha !== (int) $answer) {
        login_failed($key, 'Invalid details or security answer. Try the new question.', 422);
    }
    $query = db()->prepare('SELECT * FROM users WHERE LOWER(email)=:email LIMIT 1');
    $query->execute(['email' => $email]);
    $user = $query->fetch();
    $dummy = '$2y$12$MXfSXi/zXc56DdJMxnzQvueXTWnKjf1K9QAiKQWwmFXTCCbn488y2';
    if (!password_verify($password, (string) ($user['password'] ?? $dummy)) || !$user) {
        if ($user) {
            audit(['id' => (int) $user['id'], 'name' => $user['name'], 'role' => $user['role']], 'login_failed', $email);
        }
        login_failed($key, 'Invalid email or password.', 401);
    }
    if (!(int) $user['is_active'] || !in_array($user['role'], ['super_admin','admin','staff'], true)) {
        login_error('This account cannot access the system.', 403);
    }
    login_attempts($key, 'reset');
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'],
        'role' => $user['role'], 'session_version' => (int) $user['session_version'],
        'must_change_password' => (bool) ($user['must_change_password'] ?? false),
    ];
    $_SESSION['_last_activity'] = time();
    db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=:id')->execute(['id' => $user['id']]);
    audit($_SESSION['user'], 'login', $user['email']);
    json_response([
        'success' => true,
        'message' => 'Welcome back, ' . $user['name'] . '!',
        'redirect' => $user['role'] === 'staff'
            ? (!empty($user['must_change_password']) ? '/staff/first-password' : '/staff/dashboard') : '/admin/dashboard',
    ]);
}

function staff_first_password(array $user): never
{
    if ($user['role'] !== 'staff' || empty($user['must_change_password'])) {
        json_response(['success' => false, 'message' => 'Your first-login password has already been set.'], 409);
    }
    if (!rate_limit('first-password:' . $user['id'], 10, 300)) {
        json_response(['success' => false, 'message' => 'Too many requests. Try again in a few minutes.'], 429);
    }
    $input = request_data();
    $password = is_string($input['password'] ?? null) ? $input['password'] : '';
    $confirmation = is_string($input['password_confirmation'] ?? null) ? $input['password_confirmation'] : '';
    if (!password_is_strong($password) || $password !== $confirmation) {
        json_response(['success' => false, 'message' => 'Use at least 8 characters with uppercase, lowercase, a number and a symbol. Both passwords must match.'], 422);
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $query = $pdo->prepare('SELECT password,must_change_password,is_active,session_version FROM users WHERE id=:id FOR UPDATE');
        $query->execute(['id' => $user['id']]);
        $record = $query->fetch();
        if (!$record || !$record['is_active'] || !$record['must_change_password']
            || (int) $record['session_version'] !== (int) $user['session_version']) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Your session is no longer valid. Sign in again.'], 409);
        }
        if (password_verify($password, $record['password'])) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Choose a password different from your temporary password.'], 422);
        }
        $pdo->prepare('UPDATE users SET password=:password,must_change_password=0,session_version=session_version+1,updated_at=NOW() WHERE id=:id')
            ->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        audit($user, 'first_login_password_changed', $user['email']);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
    session_regenerate_id(true);
    $_SESSION['user'] = array_replace($user, ['must_change_password' => false, 'session_version' => (int) $user['session_version'] + 1]);
    $_SESSION['_last_activity'] = time();
    json_response(['success' => true, 'redirect' => '/staff/dashboard']);
}

function login_failed(string $key, string $message, int $status): never
{
    $state = login_attempts($key, 'fail');
    if ($state['retryAfter'] > 0) login_error('Five failed attempts. Wait 3 minutes before trying again.', 429);
    login_error($message . ' ' . $state['remaining'] . ' attempts remaining.', $status);
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
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: inline; filename="inspection-' . $itemNumber . '.html"');
    echo render_view('pdf.inspection_report', ['p' => $p, 'inspection' => $i]);
    exit;
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

function personnel_rows(bool $archived = false, ?int $itemNumber = null): array
{
    $statement = db()->prepare(
        'SELECT p.id,p.item_number,p.date_of_validity,p.last_name,p.first_name,p.middle_name,p.`rank`,
                p.afp_serial_number,p.afos_mos,p.branch,p.email,p.contact_number,p.issued_by,p.date_of_birth,
                p.citizenship,p.civil_status,p.pistol_nomenclature,p.pistol_serial_number,p.pistol_type,
                p.par_number,p.qty_ammo,p.unit,p.approved_status,p.status,p.ics_status,p.date_approved,
                p.updated_at,p.created_at,p.archived_at,
                (OCTET_LENGTH(p.photo) > 0) AS has_photo,
                (OCTET_LENGTH(p.signature) > 0) AS has_signature,
                i.status AS inspection_status, i.remarks AS inspection_remarks,
                i.date_registered AS inspection_date_registered,
                i.updated_at AS inspection_updated_at
         FROM personnel p
         LEFT JOIN inspections i ON i.id = (
             SELECT i2.id FROM inspections i2 WHERE i2.item_number=p.item_number ORDER BY i2.id DESC LIMIT 1
         )
         WHERE ' . ($archived ? 'p.archived_at IS NOT NULL' : 'p.archived_at IS NULL') . '
         ' . ($itemNumber === null ? '' : 'AND p.item_number=:item') . '
         ORDER BY p.item_number'
    );
    $statement->execute($itemNumber === null ? [] : ['item' => $itemNumber]);
    return array_map(static function (array $p): array {
        $renewalStatus = renewal_status_for_personnel($p);
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
            'pistolType' => trim((string) ($p['pistol_type'] ?? '')) ?: trim((string) ($p['pistol_nomenclature'] ?? '')), 'parNumber' => $p['par_number'] ?? '',
            'qtyAmmo' => (int) ($p['qty_ammo'] ?? 0), 'unit' => $p['unit'] ?? '',
            'approvedStatus' => $renewalStatus, 'status' => $p['status'] ?? 'active',
            'icsStatus' => $renewalStatus === 'expired' && ($p['ics_status'] ?? '') !== 'under' ? 'expired' : ($p['ics_status'] ?? 'inspection'), 'dateApproved' => $p['date_approved'],
            'photo' => $p['has_photo'] ? '/personnel/' . (int) $p['item_number'] . '/image/photo' : null,
            'signature' => $p['has_signature'] ? '/personnel/' . (int) $p['item_number'] . '/image/signature' : null,
            'inspectionStatus' => $p['inspection_status'] ?? null,
            'inspectionDateRegistered' => $p['inspection_date_registered'] ?? null,
            'inspectionResult' => $p['inspection_remarks'] ?? '',
            'inspectionRemarks' => $p['inspection_remarks'] ?? '',
            'inspectionUpdatedAt' => $p['inspection_updated_at'] ?? null,
            'personnelUpdatedAt' => $p['updated_at'] ?? $p['created_at'] ?? null,
            'dateArchived' => $p['archived_at'] ?? null,
        ];
    }, $statement->fetchAll());
}

function personnel_data(): never
{
    $rows = personnel_rows();
    json_response([
        'success' => true,
        'personnel' => $rows,
        'data' => $rows,
        'metrics' => dashboard_metrics($rows),
    ]);
}

function personnel_image_response(int $itemNumber, string $kind): never
{
    $column = $kind === 'signature' ? 'signature' : 'photo';
    $query = db()->prepare("SELECT {$column} FROM personnel WHERE item_number=:item LIMIT 1");
    $query->execute(['item' => $itemNumber]);
    $value = $query->fetchColumn();
    if (!is_string($value) || !preg_match('#^data:(image/(?:png|jpeg|jpg|webp));base64,(.+)$#s', $value, $match)
        || ($bytes = base64_decode($match[2], true)) === false) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . ($match[1] === 'image/jpg' ? 'image/jpeg' : $match[1]));
    header('Content-Length: ' . strlen($bytes));
    echo $bytes;
    exit;
}

function personnel_restore(array $user): never
{
    $input = request_data();
    $item = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($item === false) json_response(['success' => false, 'message' => 'Invalid personnel item number.'], 422);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $query = $pdo->prepare('SELECT id,first_name,last_name FROM personnel WHERE item_number=:item AND archived_at IS NOT NULL FOR UPDATE');
        $query->execute(['item' => $item]);
        $record = $query->fetch();
        if (!$record) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Archived personnel not found.'], 404);
        }
        $pdo->prepare('UPDATE personnel SET archived_at=NULL,is_archived=0,status=\'active\',updated_at=NOW() WHERE id=:id')
            ->execute(['id' => $record['id']]);
        audit($user, 'personnel_restored', $record['first_name'] . ' ' . $record['last_name']);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
    json_response(['success' => true, 'message' => 'Personnel restored.']);
}

function personnel_change(int $itemNumber, array $user, bool $archive): never
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $query = $pdo->prepare('SELECT id,first_name,last_name FROM personnel WHERE item_number=:item AND archived_at IS NULL FOR UPDATE');
        $query->execute(['item' => $itemNumber]);
        $record = $query->fetch();
        if (!$record) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Personnel record not found.'], 404);
        }
        if ($archive) {
            $pdo->prepare('UPDATE personnel SET archived_at=CURDATE(),is_archived=1,status=\'archived\',updated_at=NOW() WHERE id=:id')
                ->execute(['id' => $record['id']]);
            audit($user, 'personnel_archived', $record['first_name'] . ' ' . $record['last_name']);
        } else {
            $input = request_data();
            $columns = ['rank' => 'rank', 'lastName' => 'last_name', 'firstName' => 'first_name',
                'middleName' => 'middle_name', 'afpSerialNumber' => 'afp_serial_number', 'afosMos' => 'afos_mos',
                'branch' => 'branch', 'unit' => 'unit', 'dateOfValidity' => 'date_of_validity',
                'dateOfBirth' => 'date_of_birth', 'pistolNomenclature' => 'pistol_nomenclature',
                'pistolSerialNumber' => 'pistol_serial_number', 'qtyAmmo' => 'qty_ammo', 'approvedStatus' => 'approved_status'];
            $values = ['id' => $record['id']];
            $sets = [];
            foreach ($columns as $key => $column) {
                if (!array_key_exists($key, $input)) continue;
                $value = personnel_text($input, $key);
                if (in_array($key, ['firstName', 'lastName'], true) && $value === '') {
                    throw new InvalidArgumentException('First name and last name are required.');
                }
                if (in_array($key, ['dateOfBirth', 'dateOfValidity'], true)) {
                    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                    if ($value !== '' && (!$date || $date->format('Y-m-d') !== $value || (int) $date->format('Y') < 1900)) {
                        throw new InvalidArgumentException('Enter a valid date, or leave it blank.');
                    }
                    $value = $value === '' ? null : $value;
                } elseif ($key === 'qtyAmmo') {
                    $value = filter_var($input[$key], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
                    if ($value === false) throw new InvalidArgumentException('Ammo quantity must be a nonnegative whole number.');
                } elseif ($key === 'approvedStatus' && !in_array($value, ['pending','new','renewed','within','expired'], true)) {
                    throw new InvalidArgumentException('Invalid approval status.');
                } elseif (in_array($key, ['afpSerialNumber','pistolSerialNumber'], true) && $value === '') {
                    $value = null;
                }
                $sets[] = '`' . $column . '`=:' . $key;
                $values[$key] = $value;
            }
            if (!$sets) throw new InvalidArgumentException('No personnel changes supplied.');
            $pdo->prepare('UPDATE personnel SET ' . implode(',', $sets) . ',updated_at=NOW() WHERE id=:id')->execute($values);
            audit($user, 'personnel_updated', $record['first_name'] . ' ' . $record['last_name']);
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($error instanceof InvalidArgumentException) json_response(['success' => false, 'message' => $error->getMessage()], 422);
        if ($error instanceof PDOException && $error->getCode() === '23000') {
            json_response(['success' => false, 'message' => 'The AFP or pistol serial number is already assigned to another personnel.'], 422);
        }
        throw $error;
    }
    json_response(['success' => true, 'message' => $archive ? 'Personnel archived.' : 'Personnel updated.']);
}

function personnel_duplicate_errors(array $input): array
{
    $checks = [
        'afpSerialNumber' => ['column' => 'afp_serial_number', 'label' => 'AFP serial number'],
        'email' => ['column' => 'email', 'label' => 'Email address'],
        'contactNumber' => ['column' => 'contact_number', 'label' => 'Contact number'],
        'pistolSerialNumber' => ['column' => 'pistol_serial_number', 'label' => 'Pistol serial number'],
    ];
    $errors = [];
    foreach ($checks as $field => $check) {
        $value = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
        if ($value === '') {
            continue;
        }
        $comparison = $field === 'email' ? 'LOWER(`email`)=LOWER(:value)' : '`' . $check['column'] . '`=:value';
        $statement = db()->prepare(
            'SELECT item_number,first_name,middle_name,last_name,`rank`,archived_at
             FROM personnel WHERE ' . $comparison . ' LIMIT 1'
        );
        $statement->execute(['value' => $value]);
        $existing = $statement->fetch();
        if ($existing) {
            $ownerName = trim(implode(' ', array_filter([
                $existing['rank'] ?? null,
                $existing['first_name'] ?? null,
                $existing['middle_name'] ?? null,
                $existing['last_name'] ?? null,
            ])));
            $recordState = empty($existing['archived_at']) ? '' : ' (archived record)';
            $errors[$field] = [sprintf(
                '%s already exists under %s (Item #%d)%s.',
                $check['label'],
                $ownerName !== '' ? $ownerName : 'another personnel record',
                (int) $existing['item_number'],
                $recordState,
            )];
        }
    }
    return $errors;
}

function personnel_availability(): never
{
    $input = request_data();
    $errors = personnel_duplicate_errors($input);
    json_response([
        'success' => $errors === [],
        'available' => $errors === [],
        'errors' => $errors,
    ], $errors === [] ? 200 : 409);
}

function brevo_send_transactional_email(string $recipientEmail, string $recipientName, string $subject, string $html): string
{
    $apiKey = trim(env_value('BREVO_API_KEY'));
    $senderEmail = strtolower(trim(env_value('BREVO_SENDER_EMAIL')));
    $senderName = trim(env_value('BREVO_SENDER_NAME', 'APAO Renewal System'));
    if ($apiKey === '' || preg_match('/[\r\n]/', $apiKey) || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Brevo is not configured.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is unavailable.');
    }

    $payload = [
        'sender' => ['name' => $senderName !== '' ? $senderName : 'APAO Renewal System', 'email' => $senderEmail],
        'to' => [['name' => $recipientName, 'email' => $recipientEmail]],
        'subject' => $subject,
        'htmlContent' => $html,
        'tags' => ['apao-renewal'],
    ];
    $replyTo = strtolower(trim(env_value('BREVO_REPLY_TO_EMAIL')));
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $payload['replyTo'] = ['email' => $replyTo, 'name' => $senderName];
    }

    $handle = curl_init('https://api.brevo.com/v3/smtp/email');
    if ($handle === false) {
        throw new RuntimeException('Unable to initialize the Brevo request.');
    }
    curl_setopt_array($handle, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'api-key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
    ]);
    $response = curl_exec($handle);
    $curlError = curl_error($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    if (!is_string($response) || $curlError !== '' || $status < 200 || $status >= 300) {
        error_log('[APAO Brevo] HTTP ' . $status . ($curlError !== '' ? ': ' . $curlError : ''));
        throw new RuntimeException('Brevo rejected the email request.');
    }
    $decoded = json_decode($response, true);
    return is_array($decoded) && is_string($decoded['messageId'] ?? null) ? $decoded['messageId'] : '';
}

function staff_notify_personnel(int $itemNumber, array $user): never
{
    $input = request_data();
    $message = personnel_text($input, 'message', 5000);
    if ($itemNumber < 1 || $message === '') {
        json_response(['success' => false, 'error' => 'Personnel and notification message are required.'], 422);
    }
    if (!rate_limit('brevo-personnel:' . ($user['id'] ?? 0), 20, 300)) {
        json_response(['success' => false, 'error' => 'Too many emails were requested. Please wait a few minutes.'], 429);
    }

    $statement = db()->prepare(
        'SELECT id,item_number,first_name,middle_name,last_name,`rank`,email,date_of_validity,
                approved_status,ics_status
         FROM personnel WHERE item_number=:item AND archived_at IS NULL LIMIT 1'
    );
    $statement->execute(['item' => $itemNumber]);
    $personnel = $statement->fetch();
    if (!$personnel) {
        json_response(['success' => false, 'error' => 'Personnel record not found.'], 404);
    }

    $recipientEmail = strtolower(trim((string) ($personnel['email'] ?? '')));
    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'error' => 'This personnel record has no valid registered email address.'], 422);
    }
    $recipientName = trim(implode(' ', array_filter([
        $personnel['rank'] ?? null,
        $personnel['first_name'] ?? null,
        $personnel['middle_name'] ?? null,
        $personnel['last_name'] ?? null,
    ])));
    $status = renewal_status_for_personnel($personnel);
    $subject = match ($status) {
        'expired' => 'Action Required: Pistol License Renewal',
        'within' => 'Pistol License Renewal Reminder',
        'renewed' => 'Pistol License Renewal Approved',
        default => 'APAO Pistol License Notification',
    };
    $html = render_view('emails.personnel_notify', [
        'personnelName' => $recipientName,
        'body' => $message,
    ]);

    try {
        brevo_send_transactional_email($recipientEmail, $recipientName, $subject, $html);
    } catch (Throwable $error) {
        error_log('[APAO Brevo] ' . $error->getMessage());
        json_response([
            'success' => false,
            'error' => 'The email could not be sent. Check the Brevo API key and verified sender in Render.',
        ], 502);
    }

    audit($user, 'email_sent', (string) $itemNumber);
    json_response(['success' => true, 'message' => 'Notification email sent successfully.']);
}

function personnel_text(array $input, string $key, int $maximum = 255): string
{
    $value = is_string($input[$key] ?? null) ? trim($input[$key]) : '';
    return function_exists('mb_substr') ? mb_substr($value, 0, $maximum) : substr($value, 0, $maximum);
}

function personnel_image(array $input, string $key): ?string
{
    $value = $input[$key] ?? null;
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_string($value) || strlen($value) > 3_000_000
        || !preg_match('#^data:image/(?:png|jpe?g|webp);base64,([A-Za-z0-9+/=]+)$#', $value, $matches)) {
        throw new InvalidArgumentException('Invalid image upload. Use a PNG, JPEG, or WebP image under 2 MB.');
    }
    $decoded = base64_decode($matches[1], true);
    if ($decoded === false || strlen($decoded) > 2_000_000) {
        throw new InvalidArgumentException('Invalid image upload. Use an image under 2 MB.');
    }
    $image = @getimagesizefromstring($decoded);
    $declaredMime = strtolower(substr($value, 5, strpos($value, ';') - 5));
    if ($declaredMime === 'image/jpg') {
        $declaredMime = 'image/jpeg';
    }
    if (!$image || !in_array($image['mime'] ?? '', ['image/png', 'image/jpeg', 'image/webp'], true)
        || $image['mime'] !== $declaredMime || $image[0] > 6000 || $image[1] > 6000) {
        throw new InvalidArgumentException('Upload a valid PNG, JPEG, or WebP image no larger than 6000 pixels per side.');
    }
    return $value;
}

function personnel_store(array $user): never
{
    $input = request_data();
    $required = [
        'lastName' => 'Last name', 'firstName' => 'First name', 'rank' => 'Rank',
        'afpSerialNumber' => 'AFP serial number', 'unit' => 'Unit / organization',
        'dateOfBirth' => 'Date of birth', 'email' => 'Email address',
        'pistolNomenclature' => 'Pistol nomenclature', 'pistolType' => 'Pistol type',
        'pistolSerialNumber' => 'Pistol serial number', 'issuedBy' => 'Issued by',
    ];
    $errors = [];
    foreach ($required as $field => $label) {
        if (personnel_text($input, $field) === '') {
            $errors[$field] = [$label . ' is required.'];
        }
    }
    $email = strtolower(personnel_text($input, 'email'));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = ['Enter a valid email address.'];
    }
    $contact = personnel_text($input, 'contactNumber', 20);
    if ($contact !== '' && !preg_match('/^[0-9+() .-]{7,20}$/', $contact)) {
        $errors['contactNumber'] = ['Enter a valid contact number.'];
    }
    $birthDate = personnel_text($input, 'dateOfBirth', 10);
    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $birthDate || $parsedDate > new DateTimeImmutable('today')) {
        $errors['dateOfBirth'] = ['Enter a valid date of birth that is not in the future.'];
    }
    $ammo = filter_var($input['qtyAmmo'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($ammo === false) {
        $errors['qtyAmmo'] = ['Quantity of ammunition must be a whole number of 0 or more.'];
    }
    $errors = array_merge($errors, personnel_duplicate_errors($input));
    if ($errors !== []) {
        json_response(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
    }

    try {
        $photo = personnel_image($input, 'photo');
        $signature = personnel_image($input, 'signature');
    } catch (InvalidArgumentException $error) {
        json_response(['success' => false, 'message' => $error->getMessage()], 422);
    }

    $pdo = db();
    $lockAcquired = (int) $pdo->query("SELECT GET_LOCK('apao_personnel_item_number', 5)")->fetchColumn() === 1;
    if (!$lockAcquired) {
        json_response(['success' => false, 'message' => 'Registration is busy. Please try again.'], 503);
    }
    try {
        $pdo->beginTransaction();
        $itemNumber = (int) $pdo->query('SELECT COALESCE(MAX(item_number), 0) + 1 FROM personnel')->fetchColumn();
        $insert = $pdo->prepare(
            'INSERT INTO personnel
             (item_number,date_of_validity,last_name,first_name,middle_name,`rank`,afp_serial_number,afos_mos,branch,email,
              contact_number,issued_by,date_of_birth,citizenship,civil_status,pistol_nomenclature,pistol_serial_number,
              pistol_type,qty_ammo,unit,approved_status,status,ics_status,is_archived,photo,signature,created_at,updated_at)
             VALUES
             (:item,NULL,:last_name,:first_name,:middle_name,:rank,:afp_serial,:afos_mos,:branch,:email,
              :contact,:issued_by,:birth_date,:citizenship,:civil_status,:pistol_name,:pistol_serial,
              :pistol_type,:ammo,:unit,\'pending\',\'active\',\'inspection\',0,:photo,:signature,NOW(),NOW())'
        );
        $insert->execute([
            'item' => $itemNumber,
            'last_name' => personnel_text($input, 'lastName'),
            'first_name' => personnel_text($input, 'firstName'),
            'middle_name' => personnel_text($input, 'middleName') ?: null,
            'rank' => personnel_text($input, 'rank'),
            'afp_serial' => personnel_text($input, 'afpSerialNumber'),
            'afos_mos' => personnel_text($input, 'afosMos') ?: null,
            'branch' => personnel_text($input, 'branch') ?: null,
            'email' => $email,
            'contact' => $contact ?: null,
            'issued_by' => personnel_text($input, 'issuedBy'),
            'birth_date' => $birthDate,
            'citizenship' => personnel_text($input, 'citizenship', 100) ?: 'Filipino',
            'civil_status' => personnel_text($input, 'civilStatus', 30) ?: null,
            'pistol_name' => personnel_text($input, 'pistolNomenclature'),
            'pistol_serial' => personnel_text($input, 'pistolSerialNumber'),
            'pistol_type' => personnel_text($input, 'pistolType'),
            'ammo' => $ammo,
            'unit' => personnel_text($input, 'unit'),
            'photo' => $photo,
            'signature' => $signature,
        ]);
        $personnelId = (int) $pdo->lastInsertId();
        $inspection = $pdo->prepare(
            'INSERT INTO inspections
             (personnel_id,item_number,afp_serial_number,pistol_type,date_registered,status,remarks,created_at,updated_at)
             VALUES (:personnel_id,:item,:afp_serial,:pistol_type,CURDATE(),\'pending\',:remarks,NOW(),NOW())'
        );
        $inspection->execute([
            'personnel_id' => $personnelId, 'item' => $itemNumber,
            'afp_serial' => personnel_text($input, 'afpSerialNumber'),
            'pistol_type' => personnel_text($input, 'pistolType'),
            'remarks' => personnel_text($input, 'remarks', 5000) ?: null,
        ]);
        $name = trim(personnel_text($input, 'firstName') . ' ' . personnel_text($input, 'middleName') . ' ' . personnel_text($input, 'lastName'));
        $notification = $pdo->prepare(
            'INSERT INTO notifications
             (type,title,message,personnel_name,personnel_id,read_by_admin,read_by_staff,created_at,updated_at)
             VALUES (\'personnel_added\',\'New personnel registration\',:message,:name,:id,0,1,NOW(),NOW())'
        );
        $notification->execute([
            'message' => $name . ' was submitted for inspection.', 'name' => $name, 'id' => $personnelId,
        ]);
        audit($user, 'personnel_registered', (string) $itemNumber);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($error instanceof PDOException && (string) $error->getCode() === '23000') {
            json_response([
                'success' => false,
                'message' => 'One of these personnel details is already registered.',
                'errors' => personnel_duplicate_errors($input),
            ], 409);
        }
        throw $error;
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('apao_personnel_item_number')");
    }

    json_response([
        'success' => true,
        'message' => 'Personnel registration submitted for inspection.',
        'data' => ['id' => $personnelId, 'itemNumber' => $itemNumber],
    ], 201);
}

function dashboard_metrics(array $rows): array
{
    $counts = ['totalNew' => 0, 'totalRenewed' => 0, 'withinRenewal' => 0, 'expired' => 0, 'pending' => 0];
    $map = ['new' => 'totalNew', 'renewed' => 'totalRenewed', 'within' => 'withinRenewal', 'expired' => 'expired', 'pending' => 'pending'];
    foreach ($rows as $row) {
        $key = $map[$row['approvedStatus']] ?? 'pending';
        $counts[$key]++;
    }
    return $counts;
}

function dashboard_data(): never
{
    $rows = personnel_rows();
    $counts = dashboard_metrics($rows);
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

function users_store(array $admin): never
{
    $input = request_data();
    $email = strtolower(personnel_text($input, 'username', 255));
    $name = personnel_text($input, 'fullName', 255);
    $role = personnel_text($input, 'role', 32);
    $status = personnel_text($input, 'status', 32);
    $password = is_string($input['password'] ?? null) ? $input['password'] : '';
    $confirmation = is_string($input['adminPassword'] ?? null) ? $input['adminPassword'] : '';
    if (!rate_limit('create-user:' . $admin['id'], 10, 300)) {
        json_response(['success' => false, 'message' => 'Too many requests. Please wait a few minutes.'], 429);
    }
    $query = db()->prepare('SELECT password FROM users WHERE id=:id');
    $query->execute(['id' => $admin['id']]);
    $hash = $query->fetchColumn();
    if (!is_string($hash) || !password_verify($confirmation, $hash)) {
        json_response(['success' => false, 'message' => 'Your administrator password is incorrect.'], 403);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === ''
        || !in_array($role, ['staff', 'admin'], true) || !in_array($status, ['Active', 'Inactive'], true)) {
        json_response(['success' => false, 'message' => 'Enter a valid email, full name, role, and account status.'], 422);
    }
    if (!password_is_strong($password)) {
        json_response(['success' => false, 'message' => 'Use 8–1024 characters with uppercase, lowercase, a number, and a symbol.'], 422);
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO users (name,email,password,role,is_active,session_version,must_change_password,created_at,updated_at)
            VALUES (:name,:email,:password,:role,:active,1,:firstPassword,NOW(),NOW())')->execute([
                'name' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role, 'active' => $status === 'Active' ? 1 : 0,
                'firstPassword' => $role === 'staff' ? 1 : 0,
            ]);
        audit($admin, 'user_created', $email);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($error instanceof PDOException && (int) ($error->errorInfo[1] ?? 0) === 1062) {
            json_response(['success' => false, 'message' => 'An account with this email already exists.'], 409);
        }
        throw $error;
    }
    json_response(['success' => true, 'message' => 'User account created successfully.'], 201);
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
    $conditions = [];
    $parameters = [];
    foreach (['action' => 'action', 'role' => 'user_role'] as $filter => $column) {
        $value = $_GET[$filter] ?? '';
        if ($value === '') continue;
        if (!is_string($value) || strlen($value) > 128
            || ($filter === 'role' && !in_array($value, ['super_admin','admin','staff','system'], true))) {
            json_response(['success' => false, 'message' => 'Invalid audit filter.'], 422);
        }
        $conditions[] = $column . '=:' . $filter;
        $parameters[$filter] = $value;
    }
    $search = $_GET['search'] ?? '';
    if (!is_string($search) || strlen($search) > 255) {
        json_response(['success' => false, 'message' => 'Search must be at most 255 characters.'], 422);
    }
    if (trim($search) !== '') {
        $conditions[] = '(LOCATE(:search_user,user_name)>0 OR LOCATE(:search_target,target)>0 OR LOCATE(:search_subject,subject)>0 OR LOCATE(:search_ip,ip_address)>0)';
        foreach (['search_user','search_target','search_subject','search_ip'] as $key) $parameters[$key] = trim($search);
    }
    foreach (['date_from', 'date_to'] as $field) {
        $value = $_GET[$field] ?? '';
        if ($value === '') continue;
        $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
        if (!$date || $date->format('Y-m-d') !== $value) {
            json_response(['success' => false, 'message' => 'Enter valid filter dates.'], 422);
        }
        $conditions[] = $field === 'date_from' ? 'created_at >= :date_from' : 'created_at < :date_to';
        $parameters[$field] = $field === 'date_from' ? $date->format('Y-m-d') : $date->modify('+1 day')->format('Y-m-d');
    }
    if (isset($_GET['date_from'], $_GET['date_to']) && $_GET['date_from'] !== '' && $_GET['date_to'] !== '' && $_GET['date_from'] > $_GET['date_to']) {
        json_response(['success' => false, 'message' => 'Start date must not be after end date.'], 422);
    }
    $statement = db()->prepare('SELECT * FROM audit_logs' . ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') . ' ORDER BY id DESC LIMIT 1000');
    $statement->execute($parameters);
    $rows = $statement->fetchAll();
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
        $row['dateRegistered'] = $row['inspectionDateRegistered'] ?? null;
        $status = strtolower(trim((string) ($row['inspectionStatus'] ?? '')));
        $icsStatus = strtolower(trim((string) ($row['icsStatus'] ?? '')));
        if (($row['approvedStatus'] ?? '') === 'expired' && ($icsStatus !== 'under' || $status === 'approved')) {
            $row['inspectionStatus'] = 'expired';
        } elseif ($status === 'pending' && $icsStatus === 'under') {
            $pending++;
            $row['inspectionStatus'] = 'pending';
        } elseif ($status === 'approved' || $icsStatus === 'ready') {
            $approved++;
            $row['inspectionStatus'] = 'approved';
        } elseif ($status === 'under' || $icsStatus === 'under') {
            $under++;
            $row['inspectionStatus'] = 'under';
        } elseif (($row['approvedStatus'] ?? '') === 'renewed') {
            $row['inspectionStatus'] = 'renewed';
        } else {
            $pending++;
            $row['inspectionStatus'] = 'pending';
        }
    }
    unset($row);
    $rows = array_values(array_filter($rows, static fn (array $row): bool => !in_array($row['inspectionStatus'], ['renewed', 'expired'], true)));
    json_response(['success' => true, 'data' => $rows, 'pending' => $pending, 'under' => $under, 'approved' => $approved]);
}

function personnel_renewal_history(int $itemNumber): never
{
    $personnel = db()->prepare('SELECT id FROM personnel WHERE item_number=:item LIMIT 1');
    $personnel->execute(['item' => $itemNumber]);
    if (!$personnel->fetchColumn()) {
        json_response(['success' => false, 'message' => 'Personnel record not found.'], 404);
    }
    $query = db()->prepare(
        'SELECT id,action,date_of_validity,previous_validity,inspected_by,remarks,created_at
         FROM renewal_history WHERE item_number=:item ORDER BY created_at DESC,id DESC'
    );
    $query->execute(['item' => $itemNumber]);
    $history = array_map(static fn (array $row): array => [
        'id' => (int) $row['id'], 'action' => $row['action'], 'date' => $row['created_at'],
        'dateOfValidity' => $row['date_of_validity'], 'previousValidity' => $row['previous_validity'],
        'inspectedBy' => $row['inspected_by'], 'remarks' => $row['remarks'],
    ], $query->fetchAll());
    json_response(['success' => true, 'history' => $history]);
}

function ics_send_for_inspection(int $itemNumber, array $user): never
{
    if ($itemNumber < 1) {
        json_response(['success' => false, 'message' => 'Invalid personnel item number.'], 422);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $personnelQuery = $pdo->prepare(
            'SELECT id,item_number,first_name,middle_name,last_name,afp_serial_number,pistol_type,ics_status,date_of_validity,approved_status
             FROM personnel WHERE item_number=:item AND archived_at IS NULL LIMIT 1 FOR UPDATE'
        );
        $personnelQuery->execute(['item' => $itemNumber]);
        $personnel = $personnelQuery->fetch();
        if (!$personnel) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Personnel record not found.'], 404);
        }

        $currentStatus = strtolower(trim((string) ($personnel['ics_status'] ?? 'inspection')));
        $expired = renewal_status_for_personnel($personnel) === 'expired';
        if ($currentStatus === 'ready' && !$expired) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'This inspection has already been approved.'], 409);
        }

        $alreadySent = $currentStatus === 'under';
        if (!$alreadySent) {
            $pdo->prepare('UPDATE personnel SET ics_status=\'under\',updated_at=NOW() WHERE id=:id')
                ->execute(['id' => $personnel['id']]);

            $inspectionQuery = $pdo->prepare(
                'SELECT id,status FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1 FOR UPDATE'
            );
            $inspectionQuery->execute(['item' => $itemNumber]);
            $inspection = $inspectionQuery->fetch();
            if ($inspection && strtolower((string) $inspection['status']) === 'approved') {
                $inspection = false;
            }
            if ($inspection) {
                $pdo->prepare('UPDATE inspections SET status=\'pending\',updated_at=NOW() WHERE id=:id')
                    ->execute(['id' => $inspection['id']]);
            } else {
                $pdo->prepare(
                    'INSERT INTO inspections
                     (personnel_id,item_number,afp_serial_number,pistol_type,date_registered,status,created_at,updated_at)
                     VALUES (:personnel_id,:item,:serial,:pistol_type,CURDATE(),\'pending\',NOW(),NOW())'
                )->execute([
                    'personnel_id' => $personnel['id'], 'item' => $itemNumber,
                    'serial' => $personnel['afp_serial_number'], 'pistol_type' => $personnel['pistol_type'],
                ]);
            }

            $name = trim(implode(' ', array_filter([
                $personnel['first_name'], $personnel['middle_name'], $personnel['last_name'],
            ])));
            $pdo->prepare(
                'INSERT INTO notifications
                 (type,title,message,personnel_name,personnel_id,read_by_admin,read_by_staff,created_at,updated_at)
                 VALUES (\'inspection_submitted\',\'Inspection request submitted\',:message,:name,:id,0,1,NOW(),NOW())'
            )->execute([
                'message' => $name . ' was sent by staff for administrator inspection.',
                'name' => $name, 'id' => $personnel['id'],
            ]);
            audit($user, 'inspection_submitted', (string) $itemNumber);
        }
        $pdo->commit();

        json_response([
            'success' => true,
            'status' => 'under',
            'alreadySent' => $alreadySent,
            'message' => $alreadySent
                ? 'This record is already waiting for administrator inspection.'
                : 'Inspection request sent to the administrator successfully.',
        ]);
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function inspection_parts(): array
{
    return [
        'barrel', 'slide', 'recoil_spring_assembly', 'firing_pin', 'firing_pin_safety',
        'extractor', 'rear_sight', 'front_sight', 'frame', 'magazine', 'magazine_catch',
        'magazine_catch_spring', 'trigger', 'trigger_spring', 'trigger_bar', 'slide_stop_lever',
        'trigger_pin', 'trigger_mechanism_housing', 'trigger_housing_pin', 'locking_block',
        'locking_block_pin', 'slide_lock', 'slide_lock_spring', 'connector', 'guide_rod',
    ];
}

function inspection_find_personnel(int $itemNumber): ?array
{
    return personnel_rows(false, $itemNumber)[0] ?? null;
}

function inspection_detail(int $itemNumber): never
{
    $personnel = inspection_find_personnel($itemNumber);
    if (!$personnel) {
        json_response(['success' => false, 'message' => 'Personnel record not found.'], 404);
    }
    $statement = db()->prepare('SELECT * FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1');
    $statement->execute(['item' => $itemNumber]);
    $inspection = $statement->fetch() ?: ['status' => 'pending', 'remarks' => ''];
    $camelFields = [
        'inspectedByName' => 'inspected_by_name', 'inspectedByRank' => 'inspected_by_rank',
        'inspectedByPosition' => 'inspected_by_position', 'inspectedBySig' => 'inspected_by_sig',
        'witnessedByName' => 'witnessed_by_name', 'witnessedByRank' => 'witnessed_by_rank',
        'witnessedByPosition' => 'witnessed_by_position', 'witnessedBySig' => 'witnessed_by_sig',
        'approvedByName' => 'approved_by_name', 'approvedByRank' => 'approved_by_rank',
        'approvedByPosition' => 'approved_by_position', 'approvedBySig' => 'approved_by_sig',
        'notedByName' => 'noted_by_name', 'notedByRank' => 'noted_by_rank',
        'notedByPosition' => 'noted_by_position', 'notedBySig' => 'noted_by_sig',
    ];
    foreach ($camelFields as $camel => $column) {
        $inspection[$camel] = $inspection[$column] ?? null;
    }
    $ics = db()->query('SELECT * FROM ics_settings ORDER BY id DESC LIMIT 1')->fetch() ?: [];
    json_response([
        'success' => true,
        'personnel' => $personnel,
        'inspection' => $inspection,
        'ics' => $ics,
        'checklistParts' => inspection_parts(),
    ]);
}

function inspection_signature(array $input, string $key): ?string
{
    $value = $input[$key] ?? null;
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_string($value) || strlen($value) > 3_000_000) {
        throw new InvalidArgumentException('A signature image is too large or invalid.');
    }
    if (str_starts_with($value, 'data:image/')) {
        if (!preg_match('#^data:image/(?:png|jpe?g|webp);base64,([A-Za-z0-9+/=]+)$#', $value, $matches)) {
            throw new InvalidArgumentException('A signature image is invalid.');
        }
        $decoded = base64_decode($matches[1], true);
        if ($decoded === false || strlen($decoded) > 2_000_000) {
            throw new InvalidArgumentException('A signature image must be under 2 MB.');
        }
        return $value;
    }
    if (!str_starts_with($value, '/')) {
        throw new InvalidArgumentException('A signature image path is invalid.');
    }
    return substr($value, 0, 1000);
}

function inspection_save(array $user): never
{
    $input = request_data();
    $itemNumber = filter_var($input['itemNumber'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($itemNumber === false) {
        json_response(['success' => false, 'error' => 'Invalid personnel item number.'], 422);
    }
    $status = is_string($input['status'] ?? null) ? strtolower(trim($input['status'])) : '';
    if (!in_array($status, ['under', 'approved'], true)) {
        json_response(['success' => false, 'error' => 'Invalid inspection status.'], 422);
    }
    $personnel = inspection_find_personnel($itemNumber);
    if (!$personnel) {
        json_response(['success' => false, 'error' => 'Personnel record not found.'], 404);
    }

    $newValidity = null;
    if ($status === 'approved') {
        try {
            $newValidity = birthday_renewal_validity((string) ($personnel['dateOfBirth'] ?? ''));
        } catch (InvalidArgumentException $error) {
            json_response(['success' => false, 'error' => $error->getMessage()], 422);
        }
    }

    $allowedResults = ['serviceable', 'repair', 'replace', 'unserviceable', 'na', 'missing', 'damaged'];
    $partValues = [];
    foreach (inspection_parts() as $part) {
        $value = is_string($input[$part] ?? null) ? strtolower(trim($input[$part])) : 'serviceable';
        if (!in_array($value, $allowedResults, true)) {
            json_response(['success' => false, 'error' => 'Invalid checklist value.'], 422);
        }
        $partValues[$part] = $value;
    }
    $results = array_values($partValues);
    $condition = 'Serviceable';
    if (in_array('unserviceable', $results, true)) {
        $condition = 'Unserviceable';
    } elseif (in_array('replace', $results, true)) {
        $condition = 'For Replacement';
    } elseif (array_intersect(['repair', 'missing', 'damaged'], $results)) {
        $condition = 'For Repair';
    }
    if ($status === 'approved' && $condition !== 'Serviceable') {
        json_response([
            'success' => false,
            'error' => 'This firearm cannot be marked for renewal while its condition is ' . $condition . '.',
            'rpcspRemark' => $condition,
        ], 422);
    }

    try {
        $signatures = [
            'inspected_by_sig' => inspection_signature($input, 'inspectedBySig'),
            'witnessed_by_sig' => inspection_signature($input, 'witnessedBySig'),
            'approved_by_sig' => inspection_signature($input, 'approvedBySig'),
            'noted_by_sig' => inspection_signature($input, 'notedBySig'),
        ];
    } catch (InvalidArgumentException $error) {
        json_response(['success' => false, 'error' => $error->getMessage()], 422);
    }

    $textFields = [
        'inspected_by_name' => 'inspectedByName', 'inspected_by_rank' => 'inspectedByRank',
        'inspected_by_position' => 'inspectedByPosition',
        'witnessed_by_name' => 'witnessedByName', 'witnessed_by_rank' => 'witnessedByRank',
        'witnessed_by_position' => 'witnessedByPosition',
        'approved_by_name' => 'approvedByName', 'approved_by_rank' => 'approvedByRank',
        'approved_by_position' => 'approvedByPosition',
        'noted_by_name' => 'notedByName', 'noted_by_rank' => 'notedByRank',
        'noted_by_position' => 'notedByPosition',
    ];
    $values = $partValues;
    foreach ($textFields as $column => $key) {
        $values[$column] = personnel_text($input, $key) ?: null;
    }
    $values = array_merge($values, $signatures, [
        'status' => $status,
        'remarks' => personnel_text($input, 'remarks', 5000) ?: null,
        'user_id' => $user['id'],
        'item' => $itemNumber,
    ]);

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare('SELECT id FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1 FOR UPDATE');
        $find->execute(['item' => $itemNumber]);
        $inspectionId = $find->fetchColumn();
        if (!$inspectionId) {
            $create = $pdo->prepare(
                'INSERT INTO inspections (personnel_id,item_number,afp_serial_number,pistol_type,date_registered,status,created_at,updated_at)
                 VALUES (:personnel_id,:item,:serial,:pistol_type,CURDATE(),\'pending\',NOW(),NOW())'
            );
            $create->execute([
                'personnel_id' => $personnel['id'], 'item' => $itemNumber,
                'serial' => $personnel['afpSerialNumber'], 'pistol_type' => $personnel['pistolType'],
            ]);
            $inspectionId = (int) $pdo->lastInsertId();
        }
        $assignments = [];
        foreach (array_merge(inspection_parts(), array_keys($textFields), array_keys($signatures)) as $column) {
            $assignments[] = '`' . $column . '`=:' . $column;
        }
        $assignments[] = '`status`=:status';
        $assignments[] = '`remarks`=:remarks';
        if ($status === 'approved') $assignments[] = '`next_renewal_date`=:next_validity';
        $assignments[] = '`inspected_by_user_id`=:user_id';
        $updateValues = $values;
        if ($status === 'approved') $updateValues['next_validity'] = $newValidity;
        unset($updateValues['item']);
        $updateValues['id'] = $inspectionId;
        $update = $pdo->prepare(
            'UPDATE inspections SET ' . implode(',', $assignments) . ',inspected_at=NOW(),updated_at=NOW() WHERE id=:id'
        );
        $update->execute($updateValues);

        $personnelUpdate = $pdo->prepare(
            'UPDATE personnel SET ics_status=:ics_status,date_approved=' . ($status === 'approved' ? 'CURDATE()' : 'NULL')
            . ($status === 'approved' ? ',approved_status=\'renewed\',date_of_validity=:validity,last_renewed_at=NOW()' : '') . ',updated_at=NOW()
             WHERE item_number=:item'
        );
        $personnelValues = [
            'ics_status' => $status === 'approved' ? 'ready' : 'under', 'item' => $itemNumber,
        ];
        if ($status === 'approved') $personnelValues['validity'] = $newValidity;
        $personnelUpdate->execute($personnelValues);
        if ($status === 'approved' && $personnel['dateOfValidity'] !== $newValidity) {
            $pdo->prepare('INSERT INTO renewal_history
                (item_number,action,date_of_validity,previous_validity,inspected_by,remarks,created_at,updated_at)
                VALUES (:item,\'renewed\',:validity,:previous,:name,:remarks,NOW(),NOW())')
                ->execute(['item' => $itemNumber, 'validity' => $newValidity,
                    'previous' => $personnel['dateOfValidity'], 'name' => $user['name'], 'remarks' => $values['remarks']]);
        }
        audit($user, $status === 'approved' ? 'inspection_approved' : 'inspection_saved', (string) $itemNumber);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }

    json_response([
        'success' => true,
        'status' => $status,
        'rpcspRemark' => $condition,
        'message' => $status === 'approved' ? 'Inspection marked for renewal.' : 'Inspection saved.',
    ]);
}

function inspection_notify_staff(array $user): never
{
    $input = request_data();
    $itemNumber = filter_var($input['itemNumber'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $message = personnel_text($input, 'message', 2000);
    if ($itemNumber === false || $message === '') {
        json_response(['success' => false, 'error' => 'Personnel and notification message are required.'], 422);
    }
    $personnel = inspection_find_personnel($itemNumber);
    if (!$personnel) {
        json_response(['success' => false, 'error' => 'Personnel record not found.'], 404);
    }
    $statusQuery = db()->prepare('SELECT status FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1');
    $statusQuery->execute(['item' => $itemNumber]);
    if ($statusQuery->fetchColumn() !== 'approved') {
        json_response(['success' => false, 'error' => 'Complete and approve the inspection before notifying staff.'], 409);
    }
    $name = trim($personnel['firstName'] . ' ' . $personnel['middleName'] . ' ' . $personnel['lastName']);
    $statement = db()->prepare(
        'INSERT INTO notifications
         (type,title,message,personnel_name,personnel_id,read_by_admin,read_by_staff,created_at,updated_at)
         VALUES (\'renewal_ready\',\'Personnel ready for renewal\',:message,:name,:id,1,0,NOW(),NOW())'
    );
    $statement->execute([
        'message' => $message, 'name' => $name, 'id' => $personnel['id'],
    ]);
    audit($user, 'staff_notified_for_renewal', (string) $itemNumber);
    json_response(['success' => true, 'message' => 'Staff notification sent.']);
}

function notifications_data(array $user): never
{
    $field = $user['role'] === 'staff' ? 'read_by_staff' : 'read_by_admin';
    $rows = db()->query("SELECT id,type,title,message,personnel_name,personnel_id,{$field} AS is_read,created_at FROM notifications ORDER BY id DESC LIMIT 50")->fetchAll();
    $notifications = array_map(static fn (array $row): array => [
        'id' => (int) $row['id'], 'type' => $row['type'], 'title' => $row['title'],
        'message' => $row['message'], 'personnelName' => $row['personnel_name'],
        'personnelId' => $row['personnel_id'], 'read' => (bool) $row['is_read'],
        'createdAt' => $row['created_at'] ? (new DateTimeImmutable($row['created_at']))->format(DateTimeInterface::ATOM) : null,
    ], $rows);
    $unreadCount = (int) db()->query("SELECT COUNT(*) FROM notifications WHERE {$field}=0")->fetchColumn();
    json_response([
        'success' => true, 'notifications' => $notifications,
        'unreadCount' => $unreadCount,
    ]);
}

function notifications_mark_read(array $user): never
{
    $field = $user['role'] === 'staff' ? 'read_by_staff' : 'read_by_admin';
    $input = request_data();
    $notificationId = $input['id'] ?? null;

    if ($notificationId !== null && $notificationId !== '') {
        $notificationId = filter_var($notificationId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($notificationId === false) {
            json_response(['success' => false, 'message' => 'Invalid notification.'], 422);
        }
        $statement = db()->prepare("UPDATE notifications SET {$field}=1,updated_at=NOW() WHERE id=:id AND {$field}=0");
        $statement->execute(['id' => $notificationId]);
    } else {
        db()->exec("UPDATE notifications SET {$field}=1,updated_at=NOW() WHERE {$field}=0");
    }

    $unreadCount = (int) db()->query("SELECT COUNT(*) FROM notifications WHERE {$field}=0")->fetchColumn();
    json_response(['success' => true, 'unreadCount' => $unreadCount]);
}
