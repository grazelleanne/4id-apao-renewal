<?php
declare(strict_types=1);
class ListResponse extends RuntimeException {
    public function __construct(public array $payload) { parent::__construct(); }
}
function json_response(array $payload, int $status = 200): never { throw new ListResponse($payload); }
function personnel_rows(): array { return $GLOBALS['personnel']; }
function db(): object {
    return new class {
        public function query(string $sql): object {
            if (!str_contains($sql, "n.type='renewal_ready'") || !str_contains($sql, 'COALESCE(i.updated_at,i.created_at)')
                || !str_contains($sql, 'MAX(latest.id)')) throw new RuntimeException('Notification must belong to the latest inspection cycle.');
            return new class { public function fetchAll(int $mode): array { return $GLOBALS['notified']; } };
        }
    };
}
$source = file_get_contents(dirname(__DIR__) . '/public/index.php');
$start = strpos($source, 'function inspection_data(): never');
$end = strpos($source, 'function personnel_renewal_history(', $start);
eval(substr($source, $start, $end - $start));
$personnel = [
    ['itemNumber' => 1, 'inspectionStatus' => 'approved', 'icsStatus' => 'ready'],
    ['itemNumber' => 2, 'inspectionStatus' => 'approved', 'icsStatus' => 'ready'],
    ['itemNumber' => 3, 'inspectionStatus' => 'pending', 'icsStatus' => 'under'],
    ['itemNumber' => 4, 'inspectionStatus' => 'under', 'icsStatus' => 'under', 'approvedStatus' => 'new'],
    ['itemNumber' => 5, 'inspectionStatus' => 'pending', 'icsStatus' => 'inspection', 'approvedStatus' => 'new'],
    ['itemNumber' => 6, 'inspectionStatus' => null, 'icsStatus' => 'inspection', 'approvedStatus' => 'new'],
    ['itemNumber' => 7, 'inspectionStatus' => 'pending', 'icsStatus' => 'ready', 'approvedStatus' => 'renewed'],
    ['itemNumber' => 8, 'inspectionStatus' => null, 'icsStatus' => 'ready', 'approvedStatus' => 'renewed'],
];
foreach ([[1], []] as $notified) {
    try { inspection_data(); } catch (ListResponse $response) {
        $ids = array_column($response->payload['data'], 'itemNumber');
        if ($ids !== ($notified ? [2,3,4] : [1,2,3,4]) || $response->payload['approved'] !== ($notified ? 1 : 2)
            || $response->payload['under'] !== 1 || $response->payload['pending'] !== 1) {
            throw new RuntimeException('Notified records must leave both the list and ready count; unnotified inspection cycles remain available.');
        }
    }
}
echo "Ready-for-renewal notification list checks passed.\n";
