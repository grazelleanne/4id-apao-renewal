<?php
declare(strict_types=1);

require __DIR__ . '/laravel-bootstrap.php';
if (PHP_SAPI !== 'cli' || $argc !== 2 || !is_readable($argv[1])) {
    fwrite(STDERR, "Usage: php bin/import-personnel.php <personnel.csv>\n");
    exit(2);
}
$allowed = [
    'id','item_number','date_of_validity','last_name','first_name','middle_name','rank',
    'afp_serial_number','afos_mos','branch','email','contact_number','issued_by','date_of_birth',
    'citizenship','civil_status','pistol_nomenclature','pistol_serial_number','pistol_type','par_number',
    'last_renewed_at','qty_ammo','unit','approved_status','status','ics_status','date_approved',
    'is_archived','archived_at','photo','signature','created_at','updated_at',
];
$handle = fopen($argv[1], 'rb');
$header = fgetcsv($handle, null, ',', '"', '');
if (!is_array($header) || !$header) {
    throw new RuntimeException('CSV header is missing.');
}
$header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
$header = array_map(static fn ($field): string => trim((string) $field), $header);
if (count($header) !== count(array_unique($header)) || array_diff($header, $allowed)
    || array_diff(['item_number','first_name','last_name'], $header)) {
    throw new RuntimeException('CSV has duplicate/unknown columns or misses required columns.');
}
$pdo = db();
$pdo->beginTransaction();
$count = 0;
$line = 1;
try {
    while (($values = fgetcsv($handle, null, ',', '"', '')) !== false) {
        $line++;
        if (count($values) !== count($header)) {
            throw new RuntimeException("Wrong number of fields.");
        }
        $row = array_combine($header, $values);
        if (!ctype_digit(trim((string) $row['item_number'])) || (int) $row['item_number'] < 1
            || trim((string) $row['first_name']) === '' || trim((string) $row['last_name']) === '') {
            throw new RuntimeException("Required values are invalid.");
        }
        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("Email address is invalid.");
        }
        if (!empty($row['contact_number']) && !preg_match('/^[0-9]{10,20}$/', $row['contact_number'])) {
            throw new RuntimeException("Contact number is invalid.");
        }
        $insert = [];
        foreach ($header as $column) {
            $value = trim((string) $row[$column]);
            $insert[$column] = $value === '' && !in_array($column, ['item_number','first_name','last_name'], true) ? null : $value;
        }
        $columns = array_map(static fn ($c) => '`' . $c . '`', array_keys($insert));
        $placeholders = array_map(static fn ($c) => ':' . $c, array_keys($insert));
        $query = $pdo->prepare('INSERT INTO personnel (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')');
        $query->execute($insert);
        $count++;
    }
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fclose($handle);
    fwrite(STDERR, "Import failed on CSV row {$line}; all rows were rolled back. " . $exception->getMessage() . "\n");
    exit(1);
}
fclose($handle);
fwrite(STDOUT, "Imported {$count} record(s).\n");
