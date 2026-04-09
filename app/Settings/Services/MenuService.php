<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Services;

class MenuService
{
    public function __construct(private readonly object $repo)
    {
    }

    public function allMenus(): array
    {
        return $this->repo->allMenus();
    }

    public function getMenu(string $machineName): ?array
    {
        $menu = $this->repo->findByMachineName($machineName);
        if ($menu === null) {
            return null;
        }

        $items = $this->repo->getItems((int) $menu['id']);
        $menu['items'] = $this->buildTree($items);

        return $menu;
    }

    public function getMenuItems(string $machineName): array
    {
        $menu = $this->getMenu($machineName);

        return $menu['items'] ?? [];
    }

    public function getFlatItems(int $menuId): array
    {
        return $this->repo->getItems($menuId);
    }

    public function saveItems(int $menuId, array $items): void
    {
        // Delete existing items and re-create
        $existing = $this->repo->getItems($menuId);
        foreach ($existing as $item) {
            $this->repo->deleteItem((int) $item['id']);
        }

        foreach ($items as $index => $item) {
            $this->repo->createItem([
                'menu_id' => $menuId,
                'parent_id' => $item['parent_id'] ?? null,
                'label' => $item['label'],
                'item_type' => $item['item_type'] ?? 'internal',
                'url' => $item['url'] ?? null,
                'route_name' => $item['route_name'] ?? null,
                'content_entry_id' => $item['content_entry_id'] ?? null,
                'target' => $item['target'] ?? null,
                'css_class' => $item['css_class'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Build a nested tree from flat menu items.
     */
    private function buildTree(array $items, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($items as $item) {
            $itemParent = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;

            if ($itemParent === $parentId) {
                $item['children'] = $this->buildTree($items, (int) $item['id']);
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
