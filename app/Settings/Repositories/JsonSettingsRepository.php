<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonSettingsRepository
{
    private const GROUPS = 'settings_groups';
    private const ITEMS = 'settings_items';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function allGroups(): array
    {
        return $this->store->all(self::GROUPS);
    }

    public function findGroup(string $machineName): ?array
    {
        return $this->store->findWhere(self::GROUPS, ['machine_name' => $machineName]);
    }

    public function getItems(int $groupId): array
    {
        $items = $this->store->where(self::ITEMS, ['settings_group_id' => $groupId]);

        usort($items, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $items;
    }

    public function getValue(string $groupName, string $itemName): mixed
    {
        $group = $this->findGroup($groupName);
        if ($group === null) {
            return null;
        }

        $item = $this->store->findWhere(self::ITEMS, [
            'settings_group_id' => $group['id'],
            'machine_name' => $itemName,
        ]);

        if ($item === null) {
            return null;
        }

        return json_decode($item['value_json'] ?? 'null', true);
    }

    public function updateItem(int $itemId, mixed $value): void
    {
        $this->store->update(self::ITEMS, $itemId, [
            'value_json' => json_encode($value),
        ]);
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
