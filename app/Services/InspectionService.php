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

final class InspectionService
{
    public static function inspection_pdf(int $itemNumber): never
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
        app_header('Content-Type: text/html; charset=utf-8');
        app_header('Content-Disposition: inline; filename="inspection-' . $itemNumber . '.html"');
        echo render_view('pdf.inspection_report', ['p' => $p, 'inspection' => $i]);
        finish_response();
    }

    public static function inspection_data(): never
    {
        $rows = \App\Services\PersonnelService::personnel_rows();
        $notifiedItems = array_fill_keys(db()->query("SELECT DISTINCT i.item_number FROM inspections i
            JOIN notifications n ON n.personnel_id=i.personnel_id AND n.type='renewal_ready'
            AND n.created_at >= COALESCE(i.updated_at,i.created_at)
            WHERE i.id=(SELECT MAX(latest.id) FROM inspections latest WHERE latest.item_number=i.item_number)
            AND i.status='approved'")->fetchAll(PDO::FETCH_COLUMN), true);
        $pending = 0;
        $under = 0;
        $approved = 0;
        foreach ($rows as &$row) {
            $row['dateRegistered'] = $row['inspectionDateRegistered'] ?? null;
            $status = strtolower(trim((string) ($row['inspectionStatus'] ?? '')));
            $icsStatus = strtolower(trim((string) ($row['icsStatus'] ?? '')));
            // A legacy ICS-ready flag is not proof that the latest inspection passed.
            if ($icsStatus === 'ready' && $status !== 'approved') {
                $row['inspectionStatus'] = 'unsubmitted';
                continue;
            }
            // Staff must submit a new registration before it enters the admin queue.
            if (in_array($icsStatus, ['', 'inspection'], true) && !in_array($status, ['under', 'approved'], true)) {
                $row['inspectionStatus'] = 'unsubmitted';
                continue;
            }
            if (in_array($row['approvedStatus'] ?? '', ['within', 'expired'], true) && ($icsStatus !== 'under' || $status === 'approved')) {
                $row['inspectionStatus'] = $row['approvedStatus'];
            } elseif ($status === 'pending' && $icsStatus === 'under') {
                $pending++;
                $row['inspectionStatus'] = 'pending';
            } elseif ($status === 'approved') {
                if (isset($notifiedItems[$row['itemNumber']])) {
                    $row['inspectionStatus'] = 'notified';
                    continue;
                }
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
        $rows = array_values(array_filter($rows, static fn (array $row): bool => !in_array($row['inspectionStatus'], ['renewed', 'within', 'expired', 'notified', 'unsubmitted'], true)));
        json_response(['success' => true, 'data' => $rows, 'pending' => $pending, 'under' => $under, 'approved' => $approved]);
    }

    public static function personnel_renewal_history(int $itemNumber): never
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

    public static function ics_send_for_inspection(int $itemNumber, array $user): never
    {
        if ($itemNumber < 1) {
            json_response(['success' => false, 'message' => 'Invalid personnel item number.'], 422);
        }

        $pdo = db();
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $personnelQuery = $pdo->prepare(
                'SELECT id,item_number,first_name,middle_name,last_name,afp_serial_number,pistol_type,ics_status,date_of_validity,approved_status
                 FROM personnel WHERE item_number=:item AND archived_at IS NULL LIMIT 1 FOR UPDATE'
            );
            $personnelQuery->execute(['item' => $itemNumber]);
            $personnel = $personnelQuery->fetch();
            if (!$personnel) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'Personnel record not found.'], 404);
            }

            $currentStatus = strtolower(trim((string) ($personnel['ics_status'] ?? 'inspection')));
            $renewalDue = in_array(renewal_status_for_personnel($personnel), ['within', 'expired'], true);
            if ($currentStatus === 'ready' && !$renewalDue) {
                \Illuminate\Support\Facades\DB::rollBack();
                json_response(['success' => false, 'message' => 'This inspection has already been approved.'], 409);
            }

            $alreadySent = $currentStatus === 'under';
            if (!$alreadySent) {
                $pdo->prepare('UPDATE personnel SET ics_status=\'under\',approved_status=CASE WHEN approved_status=\'new\' THEN \'pending\' ELSE approved_status END,updated_at=NOW() WHERE id=:id')
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
            \Illuminate\Support\Facades\DB::commit();

            json_response([
                'success' => true,
                'status' => 'under',
                'alreadySent' => $alreadySent,
                'message' => $alreadySent
                    ? 'This record is already waiting for administrator inspection.'
                    : 'Inspection request sent to the administrator successfully.',
            ]);
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
            }
            throw $error;
        }
    }

    public static function inspection_parts(): array
    {
        return [
            'barrel', 'slide', 'recoil_spring_assembly', 'firing_pin', 'firing_pin_safety',
            'extractor', 'rear_sight', 'front_sight', 'frame', 'magazine', 'magazine_catch',
            'magazine_catch_spring', 'trigger', 'trigger_spring', 'trigger_bar', 'slide_stop_lever',
            'trigger_pin', 'trigger_mechanism_housing', 'trigger_housing_pin', 'locking_block',
            'locking_block_pin', 'slide_lock', 'slide_lock_spring', 'connector', 'guide_rod',
        ];
    }

    public static function inspection_find_personnel(int $itemNumber): ?array
    {
        return \App\Services\PersonnelService::personnel_rows(false, $itemNumber)[0] ?? null;
    }

    public static function inspection_detail(int $itemNumber): never
    {
        $personnel = \App\Services\InspectionService::inspection_find_personnel($itemNumber);
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
            'checklistParts' => \App\Services\InspectionService::inspection_parts(),
        ]);
    }

    public static function inspection_signature(array $input, string $key): ?string
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
        if (preg_match('#^https?://#i', $value)) {
            $url = parse_url($value);
            $origin = parse_url(request()->getSchemeAndHttpHost());
            if (!$url || isset($url['user']) || isset($url['pass'])
                || strtolower($url['host'] ?? '') !== strtolower($origin['host'] ?? '')
                || ($url['port'] ?? (($url['scheme'] ?? '') === 'https' ? 443 : 80))
                    !== ($origin['port'] ?? (($origin['scheme'] ?? '') === 'https' ? 443 : 80))) {
                throw new InvalidArgumentException('A signature image must belong to this application.');
            }
            $value = $url['path'] ?? '';
        }
        $value = rawurldecode($value);
        if (!preg_match('#^/images/[A-Za-z0-9_ .-]+\.(?:png|jpe?g|webp)$#i', $value)
            || !is_file(public_path(ltrim($value, '/')))) {
            throw new InvalidArgumentException('A signature image path is invalid.');
        }
        return substr($value, 0, 1000);
    }

    public static function inspection_save(array $user): never
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
        $personnel = \App\Services\InspectionService::inspection_find_personnel($itemNumber);
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
        foreach (\App\Services\InspectionService::inspection_parts() as $part) {
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
                'inspected_by_sig' => \App\Services\InspectionService::inspection_signature($input, 'inspectedBySig'),
                'witnessed_by_sig' => \App\Services\InspectionService::inspection_signature($input, 'witnessedBySig'),
                'approved_by_sig' => \App\Services\InspectionService::inspection_signature($input, 'approvedBySig'),
                'noted_by_sig' => \App\Services\InspectionService::inspection_signature($input, 'notedBySig'),
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
            $values[$column] = \App\Services\PersonnelService::personnel_text($input, $key) ?: null;
        }
        $values = array_merge($values, $signatures, [
            'status' => $status,
            'remarks' => \App\Services\PersonnelService::personnel_text($input, 'remarks', 5000) ?: null,
            'user_id' => $user['id'],
            'item' => $itemNumber,
        ]);

        $pdo = db();
        \Illuminate\Support\Facades\DB::beginTransaction();
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
            foreach (array_merge(\App\Services\InspectionService::inspection_parts(), array_keys($textFields), array_keys($signatures)) as $column) {
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
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $response) { throw $response; } catch (Throwable $error) {
            if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                \Illuminate\Support\Facades\DB::rollBack();
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

    public static function inspection_notify_staff(array $user): never
    {
        $input = request_data();
        $itemNumber = filter_var($input['itemNumber'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $message = \App\Services\PersonnelService::personnel_text($input, 'message', 2000);
        if ($itemNumber === false || $message === '') {
            json_response(['success' => false, 'error' => 'Personnel and notification message are required.'], 422);
        }
        $personnel = \App\Services\InspectionService::inspection_find_personnel($itemNumber);
        if (!$personnel) {
            json_response(['success' => false, 'error' => 'Personnel record not found.'], 404);
        }
        $statusQuery = db()->prepare('SELECT status FROM inspections WHERE item_number=:item ORDER BY id DESC LIMIT 1');
        $statusQuery->execute(['item' => $itemNumber]);
        if (strtolower(trim((string) $statusQuery->fetchColumn())) !== 'approved') {
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

}
