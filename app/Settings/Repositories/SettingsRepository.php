<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class SettingsRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function allGroups(): array
    {
        return $this->db->fetchAll('SELECT * FROM settings_groups ORDER BY label');
    }

    public function findGroup(string $machineName): ?array
    {
        return $this->db->fetchOne('SELECT * FROM settings_groups WHERE machine_name = ?', [$machineName]);
    }

    public function getItems(int $groupId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM settings_items WHERE settings_group_id = ? ORDER BY sort_order',
            [$groupId],
        );
    }

    public function getValue(string $groupName, string $itemName): mixed
    {
        $group = $this->findGroup($groupName);
        if ($group === null) {
            return null;
        }

        $item = $this->db->fetchOne(
            'SELECT value_json FROM settings_items WHERE settings_group_id = ? AND machine_name = ?',
            [$group['id'], $itemName],
        );

        if ($item === null) {
            return null;
        }

        return json_decode($item['value_json'], true);
    }

    public function updateItem(int $itemId, mixed $value): void
    {
        $this->db->execute(
            'UPDATE settings_items SET value_json = ? WHERE id = ?',
            [json_encode($value), $itemId],
        );
    }

    public function getGroupValues(string $groupName): array
    {
        $group = $this->findGroup($groupName);
        if ($group === null) {
            return [];
        }

        $items = $this->getItems((int) $group['id']);
        $values = [];

        foreach ($items as $item) {
            $values[$item['machine_name']] = json_decode($item['value_json'] ?? 'null', true);
        }

        return $values;
    }
}
