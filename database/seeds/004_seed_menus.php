<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

return function (Connection $db): void {
    // Create 'main' menu
    $existing = $db->fetchOne("SELECT id FROM menus WHERE machine_name = 'main'");
    if ($existing === null) {
        $db->execute(
            'INSERT INTO menus (machine_name, label, description) VALUES (?, ?, ?)',
            ['main', 'Main Navigation', 'Primary site navigation menu'],
        );
        $menuId = (int) $db->lastInsertId();

        // Seed default items
        $items = [
            ['label' => 'Home', 'item_type' => 'internal', 'url' => '/', 'sort_order' => 0],
            ['label' => 'News', 'item_type' => 'internal', 'url' => '/news', 'sort_order' => 1],
        ];

        foreach ($items as $item) {
            $db->execute(
                'INSERT INTO menu_items (menu_id, label, item_type, url, sort_order)
                 VALUES (?, ?, ?, ?, ?)',
                [$menuId, $item['label'], $item['item_type'], $item['url'], $item['sort_order']],
            );
        }
    }

    // Create 'footer' menu
    $existing = $db->fetchOne("SELECT id FROM menus WHERE machine_name = 'footer'");
    if ($existing === null) {
        $db->execute(
            'INSERT INTO menus (machine_name, label, description) VALUES (?, ?, ?)',
            ['footer', 'Footer Navigation', 'Footer links'],
        );
    }
};
