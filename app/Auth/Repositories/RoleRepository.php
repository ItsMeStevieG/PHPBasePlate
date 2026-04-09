<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class RoleRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM roles WHERE id = ?', [$id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne('SELECT * FROM roles WHERE slug = ?', [$slug]);
    }

    public function getRolesForUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT r.* FROM roles r
             INNER JOIN role_user ru ON r.id = ru.role_id
             WHERE ru.user_id = ?',
            [$userId],
        );
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $this->db->execute(
            'INSERT IGNORE INTO role_user (user_id, role_id) VALUES (?, ?)',
            [$userId, $roleId],
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM roles ORDER BY name');
    }
}
