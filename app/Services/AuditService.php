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

final class AuditService
{
    public static function audit_data(): never
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

}
