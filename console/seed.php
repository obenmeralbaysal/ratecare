<?php

require_once __DIR__ . '/../bootstrap/autoload.php';

use Core\Seeder;

$command = $argv[1] ?? 'run';
$seederName = $argv[2] ?? null;

$seeder = new Seeder();

switch ($command) {
    case 'run':
        if ($seederName) {
            echo "Running seeder: {$seederName}\n";
            $seeder->runSeeder($seederName);
        } else {
            echo "Running all seeders...\n";
            $seeder->run();
        }
        break;
        
    case 'truncate':
        if ($seederName) {
            echo "Truncating table: {$seederName}\n";
            $seeder->truncate($seederName);
        } else {
            echo "Truncating all tables...\n";
            $seeder->truncateAll();
        }
        break;
        
    default:
        echo "Usage: php seed.php [run|truncate] [seeder_name|table_name]\n";
        echo "  run      - Run seeders (all or specific)\n";
        echo "  truncate - Truncate tables (all or specific)\n";
        echo "\nExamples:\n";
        echo "  php seed.php run                 - Run all seeders\n";
        echo "  php seed.php run UserSeeder      - Run specific seeder\n";
        echo "  php seed.php truncate            - Truncate all tables\n";
        echo "  php seed.php truncate users      - Truncate specific table\n";
        break;
}
