<?php

require_once __DIR__ . '/../bootstrap/autoload.php';

use Core\Migration;

$command = $argv[1] ?? 'run';

$migration = new Migration();

switch ($command) {
    case 'run':
        echo "Running migrations...\n";
        $migration->run();
        break;
        
    case 'rollback':
        $steps = (int) ($argv[2] ?? 1);
        echo "Rolling back {$steps} migration(s)...\n";
        $migration->rollback($steps);
        break;
        
    case 'reset':
        echo "Resetting all migrations...\n";
        $migration->reset();
        break;
        
    case 'status':
        $migration->status();
        break;
        
    default:
        echo "Usage: php migrate.php [run|rollback|reset|status] [steps]\n";
        echo "  run      - Run pending migrations\n";
        echo "  rollback - Rollback migrations (default: 1 step)\n";
        echo "  reset    - Reset all migrations\n";
        echo "  status   - Show migration status\n";
        break;
}
