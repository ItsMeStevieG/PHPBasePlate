<?php

declare(strict_types=1);

/**
 * PHPBasePlate V3 - Storage Sync Tool
 *
 * Keeps JSON flat files and MySQL database in sync as a "last known good" snapshot.
 *
 * Usage:
 *   php bin/sync.php db-to-json     Export database to JSON files (snapshot for failover)
 *   php bin/sync.php json-to-db     Import JSON files into database
 *   php bin/sync.php status         Show current driver and data counts
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$container = $app->getContainer();

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;
use ItsMeStevieG\PHPBasePlate\Core\Database\SyncService;

$action = $argv[1] ?? 'status';

$jsonStore = $container->get(JsonStore::class);
$driver = $app->getStorageDriver();

echo "PHPBasePlate V3 - Storage Sync\n";
echo str_repeat('=', 40) . "\n";
echo "Active driver: \033[36m{$driver}\033[0m\n\n";

if ($action === 'status') {
    echo "JSON flat files (storage/data/):\n";
    $collections = ['users', 'roles', 'content_entries', 'media_files', 'settings_items', 'menus'];
    foreach ($collections as $col) {
        $count = $jsonStore->count($col);
        echo "  {$col}: {$count} records\n";
    }

    if ($driver === 'database') {
        echo "\nMySQL database:\n";
        try {
            $db = $container->get(Connection::class);
            $db->getPdo();
            foreach ($collections as $col) {
                try {
                    $row = $db->fetchOne("SELECT COUNT(*) as cnt FROM {$col}");
                    echo "  {$col}: {$row['cnt']} records\n";
                } catch (\Throwable) {
                    echo "  {$col}: (table not found)\n";
                }
            }
        } catch (\Throwable $e) {
            echo "  \033[31mConnection failed: {$e->getMessage()}\033[0m\n";
        }
    }

    echo "\nRun 'php bin/sync.php db-to-json' or 'php bin/sync.php json-to-db' to sync.\n";
    exit(0);
}

if ($action === 'db-to-json') {
    echo "Exporting database -> JSON files...\n\n";

    try {
        $db = $container->get(Connection::class);
        $sync = new SyncService($jsonStore, $db);
        $result = $sync->databaseToJson();

        foreach ($result['synced'] as $table) {
            echo "  \033[32m✓\033[0m {$table}\n";
        }
        foreach ($result['errors'] as $error) {
            echo "  \033[33m!\033[0m {$error}\n";
        }

        echo "\n\033[32mSnapshot complete.\033[0m JSON files updated in storage/data/\n";
        echo "These files will be used if the database becomes unavailable.\n";
    } catch (\PDOException $e) {
        echo "\033[31mDatabase connection failed:\033[0m {$e->getMessage()}\n";
        exit(1);
    }
    exit(0);
}

if ($action === 'json-to-db') {
    echo "Importing JSON files -> database...\n\n";

    try {
        $db = $container->get(Connection::class);
        $db->getPdo();

        $sync = new SyncService($jsonStore, $db);
        $result = $sync->jsonToDatabase();

        foreach ($result['synced'] as $info) {
            echo "  \033[32m✓\033[0m {$info}\n";
        }
        foreach ($result['errors'] as $error) {
            echo "  \033[33m!\033[0m {$error}\n";
        }

        echo "\n\033[32mImport complete.\033[0m Database now matches JSON files.\n";
    } catch (\PDOException $e) {
        echo "\033[31mDatabase connection failed:\033[0m {$e->getMessage()}\n";
        echo "Ensure STORAGE_DRIVER=database and the database exists.\n";
        exit(1);
    }
    exit(0);
}

echo "Unknown action: {$action}\n";
echo "Usage: php bin/sync.php [status|db-to-json|json-to-db]\n";
exit(1);
