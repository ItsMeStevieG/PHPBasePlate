<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class MenuRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function allMenus(): array
    {
        return $this->db->fetchAll('SELECT * FROM menus ORDER BY label');
    }

    public function findByMachineName(string $machineName): ?array
    {
        return $this->db->fetchOne('SELECT * FROM menus WHERE machine_name = ?', [$machineName]);
    }

    public function getItems(int $menuId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort_order',
            [$menuId],
        );
    }

    public function createItem(array $data): int
    {
        $this->db->execute(
            'INSERT INTO menu_items (menu_id, parent_id, label, item_type, url, route_name,
             content_entry_id, target, css_class, sort_order, meta_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['menu_id'],
                $data['parent_id'] ?? null,
                $data['label'],
                $data['item_type'] ?? 'internal',
                $data['url'] ?? null,
                $data['route_name'] ?? null,
                $data['content_entry_id'] ?? null,
                $data['target'] ?? null,
                $data['css_class'] ?? null,
                $data['sort_order'] ?? 0,
                json_encode($data['meta'] ?? []),
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateItem(int $id, array $data): void
    {
        $this->db->execute(
            'UPDATE menu_items SET label = ?, item_type = ?, url = ?, route_name = ?,
             content_entry_id = ?, target = ?, css_class = ?, sort_order = ?, parent_id = ?
             WHERE id = ?',
            [
                $data['label'],
                $data['item_type'] ?? 'internal',
                $data['url'] ?? null,
                $data['route_name'] ?? null,
                $data['content_entry_id'] ?? null,
                $data['target'] ?? null,
                $data['css_class'] ?? null,
                $data['sort_order'] ?? 0,
                $data['parent_id'] ?? null,
                $id,
            ],
        );
    }

    public function deleteItem(int $id): void
    {
        $this->db->execute('DELETE FROM menu_items WHERE id = ?', [$id]);
    }
}
