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
                i.date_registered AS inspection_date_registered,
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
            'inspectionDateRegistered' => $p['inspection_date_registered'] ?? null,
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

function personnel_duplicate_errors(array $input): array
{
    $checks = [
        'afpSerialNumber' => ['column' => 'afp_serial_number', 'message' => 'This AFP serial number is already registered.'],
        'email' => ['column' => 'email', 'message' => 'This email address is already registered.'],
        'contactNumber' => ['column' => 'contact_number', 'message' => 'This contact number is already registered.'],
        'pistolSerialNumber' => ['column' => 'pistol_serial_number', 'message' => 'This pistol serial number is already registered.'],
    ];
    $errors = [];
    foreach ($checks as $field => $check) {
        $value = is_string($input[$field] ?? null) ? trim($input[$field]) : '';
        if ($value === '') {
            continue;
        }
        $comparison = $field === 'email' ? 'LOWER(`email`)=LOWER(:value)' : '`' . $check['column'] . '`=:value';
        $statement = db()->prepare('SELECT 1 FROM personnel WHERE ' . $comparison . ' LIMIT 1');
        $statement->execute(['value' => $value]);
        if ($statement->fetchColumn()) {
            $errors[$field] = [$check['message']];
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
        $row['dateRegistered'] = $row['inspectionDateRegistered'] ?? null;
        $status = strtolower(trim((string) ($row['inspectionStatus'] ?? '')));
        $icsStatus = strtolower(trim((string) ($row['icsStatus'] ?? '')));
        if ($status === 'approved') {
            $approved++;
            $row['inspectionStatus'] = 'approved';
        } elseif ($status === 'under' || $icsStatus === 'under') {
            $under++;
            $row['inspectionStatus'] = 'under';
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
