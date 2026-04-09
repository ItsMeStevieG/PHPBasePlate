<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Database\Migrator;
use ItsMeStevieG\PHPBasePlate\Core\Database\Seeder;

$db = $app->getContainer()->get(Connection::class);
$basePath = $app->getBasePath();

$action = $argv[1] ?? 'migrate';

if ($action === 'migrate' || $action === 'all') {
    echo "Running migrations...\n";
    $migrator = new Migrator($db);
    $executed = $migrator->run($basePath . '/database/migrations');

    if (empty($executed)) {
        echo "  Nothing to migrate.\n";
    } else {
        foreach ($executed as $name) {
            echo "  Migrated: {$name}\n";
        }
    }
}

if ($action === 'seed' || $action === 'all') {
    echo "Running seeders...\n";
    $seeder = new Seeder($db);
    $executed = $seeder->run($basePath . '/database/seeds');

    foreach ($executed as $name) {
        echo "  Seeded: {$name}\n";
    }
}

echo "Done.\n";
