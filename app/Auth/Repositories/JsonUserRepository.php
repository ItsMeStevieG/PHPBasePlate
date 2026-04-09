<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonUserRepository
{
    private const COLLECTION = 'users';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->store->find(self::COLLECTION, $id);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->store->findWhere(self::COLLECTION, ['email' => $email]);
    }

    public function create(array $data): int
    {
        return $this->store->insert(self::COLLECTION, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'status' => $data['status'] ?? 'active',
            'last_login_at' => null,
        ]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function all(): array
    {
        return $this->store->all(self::COLLECTION);
    }
}
