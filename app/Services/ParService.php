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

final class ParService
{
    public static function par_list(): never
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
        finish_response();
    }

    public static function par_pdf(int $id): never
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

}
