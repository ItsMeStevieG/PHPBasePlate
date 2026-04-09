<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonRoleRepository
{
    private const ROLES = 'roles';
    private const ROLE_USER = 'role_user';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->store->find(self::ROLES, $id);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->store->findWhere(self::ROLES, ['slug' => $slug]);
    }

    public function getRolesForUser(int $userId): array
    {
        $pivots = $this->store->where(self::ROLE_USER, ['user_id' => $userId]);
        $roles = [];

        foreach ($pivots as $pivot) {
            $role = $this->store->find(self::ROLES, (int) $pivot['role_id']);
            if ($role !== null) {
                $roles[] = $role;
            }
        }

        return $roles;
    }

    public function assignRole(int $userId, int $roleId): void
    {
        $existing = $this->store->findWhere(self::ROLE_USER, [
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        if ($existing === null) {
            $this->store->insert(self::ROLE_USER, [
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function all(): array
    {
        return $this->store->all(self::ROLES);
    }
}
