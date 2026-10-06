<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/bootstrap.php';
echo render_view('admin_reports', [
    'user' => (object) ['id' => 1, 'name' => 'Test Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
    'initialPersonnel' => [['itemNumber' => 1, 'firstName' => 'Test', 'lastName' => 'Personnel',
        'unit' => 'APAO', 'approvedStatus' => 'renewed', 'dateOfValidity' => '2028-01-01',
        'middleName' => "Invalid legacy byte: \x80"]],
]);
