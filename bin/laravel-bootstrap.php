<?php
declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
$application=require dirname(__DIR__).'/bootstrap/app.php';
$application->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
