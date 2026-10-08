<?php
declare(strict_types=1);
namespace App\Services;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;
use PDO;
use PDOException;
use Throwable;
use Exception;
use App\Support\PdfReport;

final class NotificationService
{
    public static function notifications_data(array $user): never
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

    public static function notifications_mark_read(array $user): never
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

}
