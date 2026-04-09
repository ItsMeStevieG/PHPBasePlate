<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class PermissionRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne('SELECT * FROM permissions WHERE slug = ?', [$slug]);
    }

    public function getPermissionsForRole(int $roleId): array
    {
        return $this->db->fetchAll(
            'SELECT p.* FROM permissions p
             INNER JOIN permission_role pr ON p.id = pr.permission_id
             WHERE pr.role_id = ?',
            [$roleId],
        );
    }

    public function getPermissionsForUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT DISTINCT p.* FROM permissions p
             INNER JOIN permission_role pr ON p.id = pr.permission_id
             INNER JOIN role_user ru ON pr.role_id = ru.role_id
             WHERE ru.user_id = ?',
            [$userId],
        );
    }

    public function assignToRole(int $permissionId, int $roleId): void
    {
        $this->db->execute(
            'INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (?, ?)',
            [$permissionId, $roleId],
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM permissions ORDER BY group_name, name');
    }
}
