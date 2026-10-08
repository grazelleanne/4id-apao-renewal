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

final class DashboardService
{
    public static function dashboard_metrics(array $rows): array
    {
        $counts = ['totalNew' => 0, 'totalRenewed' => 0, 'withinRenewal' => 0, 'expired' => 0, 'pending' => 0];
        $map = ['new' => 'totalNew', 'renewed' => 'totalRenewed', 'within' => 'withinRenewal', 'expired' => 'expired', 'pending' => 'pending'];
        foreach ($rows as $row) {
            $key = $map[$row['approvedStatus']] ?? 'pending';
            $counts[$key]++;
        }
        return $counts;
    }

    public static function dashboard_data(): never
    {
        $rows = \App\Services\PersonnelService::personnel_rows();
        $counts = \App\Services\DashboardService::dashboard_metrics($rows);
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

    public static function archive_data(): never
    {
        json_response(['success' => true, 'data' => \App\Services\PersonnelService::personnel_rows(true)]);
    }

}
