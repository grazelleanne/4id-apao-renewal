<?php
declare(strict_types=1);
namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use PDO;
use PDOException;
use Throwable;
use Exception;
use App\Support\PdfReport;

final class PersonnelService
{
    public static function personnel_list(): never
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
        finish_response();
    }

    public static function personnel_pdf(int $id): never
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

    public static function personnel_rows(bool $archived = false, ?int $itemNumber = null): array
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
                    COALESCE(NULLIF(CAST(i.date_registered AS CHAR),\'0000-00-00\'),
                             DATE(p.created_at),DATE(i.created_at)) AS inspection_date_registered,
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
            $icsStatus = $p['ics_status'] ?? 'inspection';
            if (in_array($renewalStatus, ['within', 'expired'], true) && $icsStatus !== 'under') {
                $icsStatus = $renewalStatus === 'expired' ? 'expired' : 'inspection';
            }
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
                'icsStatus' => $icsStatus, 'dateApproved' => $p['date_approved'],
                'parEligible' => strtolower(trim((string) ($p['inspection_status'] ?? ''))) === 'approved'
                    && $icsStatus === 'ready' && !in_array($renewalStatus, ['within', 'expired'], true),
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

    public static function personnel_data(): never
    {
        $rows = \App\Services\PersonnelService::personnel_rows();
        json_response([
            'success' => true,
            'personnel' => $rows,
            'data' => $rows,
            'metrics' => \App\Services\DashboardService::dashboard_metrics($rows),
        ]);
    }

    public static function personnel_image_response(int $itemNumber, string $kind): never
    {
        $column = $kind === 'signature' ? 'signature' : 'photo';
        $query = db()->prepare("SELECT {$column} FROM personnel WHERE item_number=:item LIMIT 1");
        $query->execute(['item' => $itemNumber]);
        $value = $query->fetchColumn();
        if (!is_string($value) || !preg_match('#^data:(image/(?:png|jpeg|jpg|webp));base64,(.+)$#s', $value, $match)
            || ($bytes = base64_decode($match[2], true)) === false) {
            app_status(404);
            finish_response();
        }
        app_header('Content-Type: ' . ($match[1] === 'image/jpg' ? 'image/jpeg' : $match[1]));
        app_header('Content-Length: ' . strlen($bytes));
        echo $bytes;
        finish_response();
    }

    public static function personnel_restore(array $user): never
    {
        $input = request_data();
        $item = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($item === false) json_response(['success' => false, 'message' => 'Invalid personnel item number.'], 422);
        $pdo = db();
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = $pdo->prepare('SELECT id,first_name,last_name FROM personnel WHERE item_number=:item AND archived_at IS NOT NULL FOR UPDATE');
            $query->execute(['item' => $item]);
            $record = $query->fetch();
            if (!$record) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'Archived personnel not found.'], 404);
            }
            $pdo->prepare('UPDATE personnel SET archived_at=NULL,is_archived=0,status=\'active\',updated_at=NOW() WHERE id=:id')
                ->execute(['id' => $record['id']]);
            audit($user, 'personnel_restored', $record['first_name'] . ' ' . $record['last_name']);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            throw $error;
        }
        json_response(['success' => true, 'message' => 'Personnel restored.']);
    }

    public static function personnel_change(int $itemNumber, array $user, bool $archive): never
    {
        $pdo = db();
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = $pdo->prepare('SELECT id,first_name,last_name FROM personnel WHERE item_number=:item AND archived_at IS NULL FOR UPDATE');
            $query->execute(['item' => $itemNumber]);
            $record = $query->fetch();
            if (!$record) {
                \Illuminate\Support\Facades\DB::rollBack();
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
                    $value = \App\Services\PersonnelService::personnel_text($input, $key);
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
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) \Illuminate\Support\Facades\DB::rollBack();
            if ($error instanceof InvalidArgumentException) json_response(['success' => false, 'message' => $error->getMessage()], 422);
            if ($error instanceof PDOException && $error->getCode() === '23000') {
                json_response(['success' => false, 'message' => 'The AFP or pistol serial number is already assigned to another personnel.'], 422);
            }
            throw $error;
        }
        json_response(['success' => true, 'message' => $archive ? 'Personnel archived.' : 'Personnel updated.']);
    }

    public static function personnel_duplicate_errors(array $input): array
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

    public static function personnel_availability(): never
    {
        $input = request_data();
        $errors = \App\Services\PersonnelService::personnel_duplicate_errors($input);
        json_response([
            'success' => $errors === [],
            'available' => $errors === [],
            'errors' => $errors,
        ], $errors === [] ? 200 : 409);
    }

    public static function staff_notify_personnel(int $itemNumber, array $user): never
    {
        $input = request_data();
        $message = \App\Services\PersonnelService::personnel_text($input, 'message', 5000);
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
            \App\Services\BrevoService::brevo_send_transactional_email($recipientEmail, $recipientName, $subject, $html);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            error_log('[APAO Brevo] ' . $error->getMessage());
            json_response([
                'success' => false,
                'error' => !function_exists('curl_init')
                    ? 'Email sending requires the PHP cURL extension. Enable cURL in php.ini and restart the PHP server.'
                    : 'The email could not be sent. Check the server mail configuration and PHP error log.',
            ], 502);
        }

        audit($user, 'email_sent', (string) $itemNumber);
        json_response(['success' => true, 'message' => 'Notification email sent successfully.']);
    }

    public static function personnel_text(array $input, string $key, int $maximum = 255): string
    {
        $value = is_string($input[$key] ?? null) ? trim($input[$key]) : '';
        return function_exists('mb_substr') ? mb_substr($value, 0, $maximum) : substr($value, 0, $maximum);
    }

    public static function personnel_image(array $input, string $key): ?string
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

    public static function personnel_store(array $user): never
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
            if (\App\Services\PersonnelService::personnel_text($input, $field) === '') {
                $errors[$field] = [$label . ' is required.'];
            }
        }
        $email = strtolower(\App\Services\PersonnelService::personnel_text($input, 'email'));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Enter a valid email address.'];
        }
        $contact = \App\Services\PersonnelService::personnel_text($input, 'contactNumber', 20);
        if ($contact !== '' && !preg_match('/^[0-9+() .-]{7,20}$/', $contact)) {
            $errors['contactNumber'] = ['Enter a valid contact number.'];
        }
        $birthDate = \App\Services\PersonnelService::personnel_text($input, 'dateOfBirth', 10);
        $issueDate = \App\Services\PersonnelService::personnel_text($input, 'dateIssued', 20);
        if ($issueDate !== '') {
            $parsedIssueDate = DateTimeImmutable::createFromFormat('!Y-m-d', $issueDate);
            if (!$parsedIssueDate || $parsedIssueDate->format('Y-m-d') !== $issueDate || $parsedIssueDate > new DateTimeImmutable('today')) {
                $errors['dateIssued'] = ['Date issued must be a valid date on or before today.'];
            }
        }
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $birthDate || $parsedDate > new DateTimeImmutable('today')) {
            $errors['dateOfBirth'] = ['Enter a valid date of birth that is not in the future.'];
        }
        $ammo = filter_var($input['qtyAmmo'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($ammo === false) {
            $errors['qtyAmmo'] = ['Quantity of ammunition must be a whole number of 0 or more.'];
        }
        $errors = array_merge($errors, \App\Services\PersonnelService::personnel_duplicate_errors($input));
        if ($errors !== []) {
            json_response(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
        }

        try {
            $photo = \App\Services\PersonnelService::personnel_image($input, 'photo');
            $signature = \App\Services\PersonnelService::personnel_image($input, 'signature');
        } catch (InvalidArgumentException $error) {
            json_response(['success' => false, 'message' => $error->getMessage()], 422);
        }

        $pdo = db();
        $lockAcquired = (int) $pdo->query("SELECT GET_LOCK('apao_personnel_item_number', 5)")->fetchColumn() === 1;
        if (!$lockAcquired) {
            json_response(['success' => false, 'message' => 'Registration is busy. Please try again.'], 503);
        }
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            $itemNumber = (int) $pdo->query('SELECT COALESCE(MAX(item_number), 0) + 1 FROM personnel')->fetchColumn();
            $insert = $pdo->prepare(
                'INSERT INTO personnel
                 (item_number,date_of_validity,last_name,first_name,middle_name,`rank`,afp_serial_number,afos_mos,branch,email,
                  contact_number,issued_by,date_of_birth,citizenship,civil_status,pistol_nomenclature,pistol_serial_number,
                  pistol_type,qty_ammo,unit,approved_status,status,ics_status,is_archived,photo,signature,created_at,updated_at)
                 VALUES
                 (:item,NULL,:last_name,:first_name,:middle_name,:rank,:afp_serial,:afos_mos,:branch,:email,
                  :contact,:issued_by,:birth_date,:citizenship,:civil_status,:pistol_name,:pistol_serial,
                  :pistol_type,:ammo,:unit,\'new\',\'active\',\'inspection\',0,:photo,:signature,NOW(),NOW())'
            );
            $insert->execute([
                'item' => $itemNumber,
                'last_name' => \App\Services\PersonnelService::personnel_text($input, 'lastName'),
                'first_name' => \App\Services\PersonnelService::personnel_text($input, 'firstName'),
                'middle_name' => \App\Services\PersonnelService::personnel_text($input, 'middleName') ?: null,
                'rank' => \App\Services\PersonnelService::personnel_text($input, 'rank'),
                'afp_serial' => \App\Services\PersonnelService::personnel_text($input, 'afpSerialNumber'),
                'afos_mos' => \App\Services\PersonnelService::personnel_text($input, 'afosMos') ?: null,
                'branch' => \App\Services\PersonnelService::personnel_text($input, 'branch') ?: null,
                'email' => $email,
                'contact' => $contact ?: null,
                'issued_by' => \App\Services\PersonnelService::personnel_text($input, 'issuedBy'),
                'birth_date' => $birthDate,
                'citizenship' => \App\Services\PersonnelService::personnel_text($input, 'citizenship', 100) ?: 'Filipino',
                'civil_status' => \App\Services\PersonnelService::personnel_text($input, 'civilStatus', 30) ?: null,
                'pistol_name' => \App\Services\PersonnelService::personnel_text($input, 'pistolNomenclature'),
                'pistol_serial' => \App\Services\PersonnelService::personnel_text($input, 'pistolSerialNumber'),
                'pistol_type' => \App\Services\PersonnelService::personnel_text($input, 'pistolType'),
                'ammo' => $ammo,
                'unit' => \App\Services\PersonnelService::personnel_text($input, 'unit'),
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
                'afp_serial' => \App\Services\PersonnelService::personnel_text($input, 'afpSerialNumber'),
                'pistol_type' => \App\Services\PersonnelService::personnel_text($input, 'pistolType'),
                'remarks' => \App\Services\PersonnelService::personnel_text($input, 'remarks', 5000) ?: null,
            ]);
            $name = trim(\App\Services\PersonnelService::personnel_text($input, 'firstName') . ' ' . \App\Services\PersonnelService::personnel_text($input, 'middleName') . ' ' . \App\Services\PersonnelService::personnel_text($input, 'lastName'));
            $notification = $pdo->prepare(
                'INSERT INTO notifications
                 (type,title,message,personnel_name,personnel_id,read_by_admin,read_by_staff,created_at,updated_at)
                 VALUES (\'personnel_added\',\'New personnel registration\',:message,:name,:id,0,1,NOW(),NOW())'
            );
            $notification->execute([
                'message' => $name . ' was submitted for inspection.', 'name' => $name, 'id' => $personnelId,
            ]);
            audit($user, 'personnel_registered', (string) $itemNumber);
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            if ($error instanceof PDOException && (string) $error->getCode() === '23000') {
                json_response([
                    'success' => false,
                    'message' => 'One of these personnel details is already registered.',
                    'errors' => \App\Services\PersonnelService::personnel_duplicate_errors($input),
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

}
