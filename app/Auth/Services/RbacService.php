<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Services;

class RbacService
{
    private array $cachedRoles = [];
    private array $cachedPermissions = [];

    public function __construct(
        private readonly object $roleRepo,
        private readonly object $permissionRepo,
    ) {
    }

    public function userHasRole(int $userId, string $roleSlug): bool
    {
        $roles = $this->getUserRoles($userId);

        foreach ($roles as $role) {
            if ($role['slug'] === $roleSlug) {
                return true;
            }
        }

        return false;
    }

    public function userHasPermission(int $userId, string $permissionSlug): bool
    {
        // Super admin bypasses all permission checks
        if ($this->userHasRole($userId, 'super-admin')) {
            return true;
        }

        $permissions = $this->getUserPermissions($userId);

        foreach ($permissions as $permission) {
            if ($permission['slug'] === $permissionSlug) {
                return true;
            }
        }

        return false;
    }

    public function userHasAnyRole(int $userId, array $roleSlugs): bool
    {
        foreach ($roleSlugs as $slug) {
            if ($this->userHasRole($userId, $slug)) {
                return true;
            }
        }

        return false;
    }

    public function userHasAnyPermission(int $userId, array $permissionSlugs): bool
    {
        if ($this->userHasRole($userId, 'super-admin')) {
            return true;
        }

        foreach ($permissionSlugs as $slug) {
            if ($this->userHasPermission($userId, $slug)) {
                return true;
            }
        }

        return false;
    }

    public function getUserRoles(int $userId): array
    {
        if (!isset($this->cachedRoles[$userId])) {
            $this->cachedRoles[$userId] = $this->roleRepo->getRolesForUser($userId);
        }

        return $this->cachedRoles[$userId];
    }

    public function getUserPermissions(int $userId): array
    {
        if (!isset($this->cachedPermissions[$userId])) {
            $this->cachedPermissions[$userId] = $this->permissionRepo->getPermissionsForUser($userId);
        }

        return $this->cachedPermissions[$userId];
    }
}
