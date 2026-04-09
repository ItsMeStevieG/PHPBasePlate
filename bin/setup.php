<?php

declare(strict_types=1);

/**
 * PHPBasePlate V3 - Setup Tool
 *
 * Usage:
 *   php bin/setup.php              Run full setup
 *   php bin/setup.php --check      Run environment check only
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;
use ItsMeStevieG\PHPBasePlate\Core\Database\Migrator;
use ItsMeStevieG\PHPBasePlate\Core\Database\Seeder;
use ItsMeStevieG\PHPBasePlate\Content\Services\SchemaService;

$action = $argv[1] ?? 'setup';

if ($action === '--check') {
    require __DIR__ . '/check.php';
    exit(0);
}

echo "PHPBasePlate V3 - Setup\n";
echo str_repeat('=', 40) . "\n\n";

// Boot the app
$app = require_once $basePath . '/bootstrap/app.php';
$container = $app->getContainer();
$driver = $app->getStorageDriver();

echo "Storage driver: \033[36m{$driver}\033[0m\n\n";

try {
    if ($driver === 'database') {
        // --- DATABASE SETUP ---
        $db = $container->get(Connection::class);

        echo "[1/4] Testing database connection...\n";
        $db->getPdo();
        echo "  Database connection OK.\n\n";

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

        echo "[3/4] Running seeders...\n";
        $seeder = new Seeder($db);
        $executed = $seeder->run($basePath . '/database/seeds');
        foreach ($executed as $name) {
            echo "  Seeded: {$name}\n";
        }
        echo "\n";

        echo "[4/4] Syncing content schemas to database...\n";
        $schemaService = $container->get(SchemaService::class);
        $result = $schemaService->loadAndSync(syncToDb: true);
        foreach ($result['loaded'] as $name) {
            echo "  Synced: {$name}\n";
        }

    } else {
        // --- JSON FLAT-FILE SETUP ---
        $store = $container->get(JsonStore::class);

        echo "[1/3] Initialising JSON storage in storage/data/...\n";
        echo "  Storage path: {$store->getBasePath()}\n\n";

        echo "[2/3] Seeding default data...\n";
        seedJsonData($store);
        echo "\n";

        echo "[3/3] Syncing content schemas...\n";
        $schemaService = $container->get(SchemaService::class);
        $result = $schemaService->loadAndSync(syncToDb: true);
        foreach ($result['loaded'] as $name) {
            echo "  Synced: {$name}\n";
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
    echo "Or set STORAGE_DRIVER=json in .env to use flat-file storage.\n";
    exit(1);
} catch (\Throwable $e) {
    echo "\n\033[31mSetup failed:\033[0m\n";
    echo "  {$e->getMessage()}\n";
    echo "  {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}

/**
 * Seed default data into JSON flat files.
 */
function seedJsonData(JsonStore $store): void
{
    // Roles
    if ($store->count('roles') === 0) {
        $store->insert('roles', ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access']);
        $store->insert('roles', ['name' => 'Content Admin', 'slug' => 'content-admin', 'description' => 'Manage content, publish, menus, media']);
        $store->insert('roles', ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Create and edit draft content']);
        echo "  Seeded: roles\n";
    }

    // Permissions
    if ($store->count('permissions') === 0) {
        $perms = [
            ['name' => 'View Content', 'slug' => 'content.view', 'group_name' => 'content'],
            ['name' => 'Create Content', 'slug' => 'content.create', 'group_name' => 'content'],
            ['name' => 'Edit Content', 'slug' => 'content.edit', 'group_name' => 'content'],
            ['name' => 'Delete Content', 'slug' => 'content.delete', 'group_name' => 'content'],
            ['name' => 'Publish Content', 'slug' => 'content.publish', 'group_name' => 'content'],
            ['name' => 'Upload Media', 'slug' => 'media.upload', 'group_name' => 'media'],
            ['name' => 'Delete Media', 'slug' => 'media.delete', 'group_name' => 'media'],
            ['name' => 'Manage Menus', 'slug' => 'menus.manage', 'group_name' => 'menus'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group_name' => 'settings'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group_name' => 'users'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group_name' => 'users'],
            ['name' => 'Manage API Tokens', 'slug' => 'api.tokens.manage', 'group_name' => 'api'],
        ];
        foreach ($perms as $p) {
            $store->insert('permissions', $p);
        }
        echo "  Seeded: permissions\n";
    }

    // Admin user
    if ($store->count('users') === 0) {
        $userId = $store->insert('users', [
            'name' => 'Admin',
            'email' => 'admin@phpbaseplate.local',
            'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
            'status' => 'active',
            'last_login_at' => null,
        ]);

        $superAdmin = $store->findWhere('roles', ['slug' => 'super-admin']);
        if ($superAdmin) {
            $store->insert('role_user', ['user_id' => $userId, 'role_id' => $superAdmin['id']]);
        }
        echo "  Seeded: admin user\n";
    }

    // Settings
    if ($store->count('settings_groups') === 0) {
        $groupId = $store->insert('settings_groups', [
            'machine_name' => 'site',
            'label' => 'Site Settings',
            'description' => 'General site configuration',
        ]);

        $items = [
            ['machine_name' => 'site_name', 'label' => 'Site Name', 'field_type' => 'text', 'value_json' => json_encode('PHPBasePlate'), 'sort_order' => 0],
            ['machine_name' => 'site_tagline', 'label' => 'Tagline', 'field_type' => 'text', 'value_json' => json_encode('A reusable PHP content platform'), 'sort_order' => 1],
            ['machine_name' => 'admin_email', 'label' => 'Admin Email', 'field_type' => 'text', 'value_json' => json_encode('admin@phpbaseplate.local'), 'sort_order' => 2],
            ['machine_name' => 'footer_text', 'label' => 'Footer Text', 'field_type' => 'text', 'value_json' => json_encode('© PHPBasePlate'), 'sort_order' => 3],
            ['machine_name' => 'maintenance_mode', 'label' => 'Maintenance Mode', 'field_type' => 'boolean', 'value_json' => json_encode(false), 'sort_order' => 4],
        ];
        foreach ($items as $item) {
            $item['settings_group_id'] = $groupId;
            $item['is_secret'] = 0;
            $store->insert('settings_items', $item);
        }
        echo "  Seeded: settings\n";
    }

    // Menus
    if ($store->count('menus') === 0) {
        $menuId = $store->insert('menus', [
            'machine_name' => 'main',
            'label' => 'Main Navigation',
            'description' => 'Primary site navigation menu',
        ]);

        $store->insert('menu_items', ['menu_id' => $menuId, 'label' => 'Home', 'item_type' => 'internal', 'url' => '/', 'sort_order' => 0, 'parent_id' => null]);
        $store->insert('menu_items', ['menu_id' => $menuId, 'label' => 'News', 'item_type' => 'internal', 'url' => '/news', 'sort_order' => 1, 'parent_id' => null]);

        $store->insert('menus', [
            'machine_name' => 'footer',
            'label' => 'Footer Navigation',
            'description' => 'Footer links',
        ]);
        echo "  Seeded: menus\n";
    }
}
