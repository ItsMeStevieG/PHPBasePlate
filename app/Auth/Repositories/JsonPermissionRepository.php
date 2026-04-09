<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonPermissionRepository
{
    private const PERMISSIONS = 'permissions';
    private const PERMISSION_ROLE = 'permission_role';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->store->findWhere(self::PERMISSIONS, ['slug' => $slug]);
    }

    public function getPermissionsForRole(int $roleId): array
    {
        $pivots = $this->store->where(self::PERMISSION_ROLE, ['role_id' => $roleId]);
        $permissions = [];

        foreach ($pivots as $pivot) {
            $perm = $this->store->find(self::PERMISSIONS, (int) $pivot['permission_id']);
            if ($perm !== null) {
                $permissions[] = $perm;
            }
        }

        return $permissions;
    }

    public function getPermissionsForUser(int $userId): array
    {
        $roleRepo = new JsonRoleRepository($this->store);
        $roles = $roleRepo->getRolesForUser($userId);
        $seen = [];
        $permissions = [];

        foreach ($roles as $role) {
            $rolePerms = $this->getPermissionsForRole((int) $role['id']);
            foreach ($rolePerms as $perm) {
                if (!isset($seen[$perm['id']])) {
                    $permissions[] = $perm;
                    $seen[$perm['id']] = true;
                }
            }
        }

        return $permissions;
    }

    public function assignToRole(int $permissionId, int $roleId): void
    {
        $existing = $this->store->findWhere(self::PERMISSION_ROLE, [
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]);

        if ($existing === null) {
            $this->store->insert(self::PERMISSION_ROLE, [
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function all(): array
    {
        return $this->store->all(self::PERMISSIONS);
    }
}
