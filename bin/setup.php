<?php

declare(strict_types=1);

/**
 * PHPBasePlate V3 - Setup Tool
 *
 * Usage:
 *   php bin/setup.php              Run full setup (migrate + seed)
 *   php bin/setup.php --check      Run environment check only
 *   php bin/setup.php --fresh      Drop all tables and re-run migrations + seeds
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Database\Migrator;
use ItsMeStevieG\PHPBasePlate\Core\Database\Seeder;
use ItsMeStevieG\PHPBasePlate\Content\Services\SchemaService;

$action = $argv[1] ?? 'setup';

// Run env check first
if ($action === '--check') {
    require __DIR__ . '/check.php';
    exit(0);
}

echo "PHPBasePlate V3 - Setup\n";
echo str_repeat('=', 40) . "\n\n";

// Boot the app
$app = require_once $basePath . '/bootstrap/app.php';
$container = $app->getContainer();

try {
    $db = $container->get(Connection::class);

    // Test connection
    echo "[1/4] Testing database connection...\n";
    $db->getPdo();
    echo "  Database connection OK.\n\n";

    // Run migrations
    echo "[2/4] Running migrations...\n";
    $migrator = new Migrator($db);
    $executed = $migrator->run($basePath . '/database/migrations');

    if (empty($executed)) {
        echo "  Nothing to migrate.\n\n";
    } else {
        foreach ($executed as $name) {
            echo "  Migrated: {$name}\n";
        }
        echo "\n";
    }

    // Run seeders
    echo "[3/4] Running seeders...\n";
    $seeder = new Seeder($db);
    $executed = $seeder->run($basePath . '/database/seeds');

    foreach ($executed as $name) {
        echo "  Seeded: {$name}\n";
    }
    echo "\n";

    // Sync schemas to database
    echo "[4/4] Syncing content schemas to database...\n";
    $schemaService = $container->get(SchemaService::class);
    $result = $schemaService->loadAndSync(syncToDb: true);

    foreach ($result['loaded'] as $name) {
        echo "  Synced: {$name}\n";
    }

    if (!empty($result['errors'])) {
        echo "  Errors:\n";
        foreach ($result['errors'] as $file => $errors) {
            foreach ($errors as $error) {
                echo "    {$error}\n";
            }
        }
    }

    echo "\n" . str_repeat('=', 40) . "\n";
    echo "\033[32mSetup complete!\033[0m\n\n";
    echo "Default admin credentials:\n";
    echo "  Email:    admin@phpbaseplate.local\n";
    echo "  Password: admin\n\n";
    echo "\033[33mPlease change the admin password after first login.\033[0m\n";
    echo "\nStart the dev server:\n";
    echo "  php -S localhost:8000 -t public/\n\n";

} catch (\PDOException $e) {
    echo "\n\033[31mDatabase connection failed:\033[0m\n";
    echo "  {$e->getMessage()}\n\n";
    echo "Please check your .env file and ensure the database exists.\n";
    exit(1);
} catch (\Throwable $e) {
    echo "\n\033[31mSetup failed:\033[0m\n";
    echo "  {$e->getMessage()}\n";
    echo "  {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}
