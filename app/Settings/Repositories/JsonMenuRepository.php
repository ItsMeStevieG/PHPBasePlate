<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonMenuRepository
{
    private const MENUS = 'menus';
    private const ITEMS = 'menu_items';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function allMenus(): array
    {
        return $this->store->all(self::MENUS);
    }

    public function findByMachineName(string $machineName): ?array
    {
        return $this->store->findWhere(self::MENUS, ['machine_name' => $machineName]);
    }

    public function getItems(int $menuId): array
    {
        $items = $this->store->where(self::ITEMS, ['menu_id' => $menuId]);

        usort($items, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));

        return $items;
    }

    public function createItem(array $data): int
    {
        return $this->store->insert(self::ITEMS, [
            'menu_id' => $data['menu_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'label' => $data['label'],
            'item_type' => $data['item_type'] ?? 'internal',
            'url' => $data['url'] ?? null,
            'route_name' => $data['route_name'] ?? null,
            'content_entry_id' => $data['content_entry_id'] ?? null,
            'target' => $data['target'] ?? null,
            'css_class' => $data['css_class'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_json' => json_encode($data['meta'] ?? []),
        ]);
    }

    public function updateItem(int $id, array $data): void
    {
        $this->store->update(self::ITEMS, $id, [
            'label' => $data['label'],
            'item_type' => $data['item_type'] ?? 'internal',
            'url' => $data['url'] ?? null,
            'route_name' => $data['route_name'] ?? null,
            'content_entry_id' => $data['content_entry_id'] ?? null,
            'target' => $data['target'] ?? null,
            'css_class' => $data['css_class'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    public function deleteItem(int $id): void
    {
        $this->store->delete(self::ITEMS, $id);
    }
}
