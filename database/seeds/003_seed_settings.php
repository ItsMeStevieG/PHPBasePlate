<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

return function (Connection $db): void {
    // Create 'site' settings group
    $existing = $db->fetchOne("SELECT id FROM settings_groups WHERE machine_name = 'site'");
    if ($existing === null) {
        $db->execute(
            'INSERT INTO settings_groups (machine_name, label, description) VALUES (?, ?, ?)',
            ['site', 'Site Settings', 'General site configuration'],
        );
        $groupId = (int) $db->lastInsertId();
    } else {
        $groupId = (int) $existing['id'];
    }

    $items = [
        ['machine_name' => 'site_name', 'label' => 'Site Name', 'field_type' => 'text', 'value' => 'PHPBasePlate', 'sort_order' => 0],
        ['machine_name' => 'site_tagline', 'label' => 'Tagline', 'field_type' => 'text', 'value' => 'A reusable PHP content platform', 'sort_order' => 1],
        ['machine_name' => 'admin_email', 'label' => 'Admin Email', 'field_type' => 'text', 'value' => 'admin@phpbaseplate.local', 'sort_order' => 2],
        ['machine_name' => 'meta_description', 'label' => 'Default Meta Description', 'field_type' => 'textarea', 'value' => '', 'sort_order' => 3],
        ['machine_name' => 'meta_keywords', 'label' => 'Default Meta Keywords', 'field_type' => 'text', 'value' => '', 'sort_order' => 4],
        ['machine_name' => 'footer_text', 'label' => 'Footer Text', 'field_type' => 'text', 'value' => '© PHPBasePlate', 'sort_order' => 5],
        ['machine_name' => 'maintenance_mode', 'label' => 'Maintenance Mode', 'field_type' => 'boolean', 'value' => false, 'sort_order' => 6],
    ];

    foreach ($items as $item) {
        $existingItem = $db->fetchOne(
            'SELECT id FROM settings_items WHERE settings_group_id = ? AND machine_name = ?',
            [$groupId, $item['machine_name']],
        );

        if ($existingItem === null) {
            $db->execute(
                'INSERT INTO settings_items (settings_group_id, machine_name, label, field_type, value_json, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [$groupId, $item['machine_name'], $item['label'], $item['field_type'], json_encode($item['value']), $item['sort_order']],
            );
        }
    }
};
