<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonApiTokenRepository
{
    private const COLLECTION = 'api_tokens';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findByTokenHash(string $hash): ?array
    {
        return $this->store->findWhere(self::COLLECTION, ['token_hash' => $hash]);
    }

    public function create(string $name, string $tokenHash, ?int $userId = null, array $scopes = []): int
    {
        return $this->store->insert(self::COLLECTION, [
            'name' => $name,
            'token_hash' => $tokenHash,
            'user_id' => $userId,
            'scopes_json' => json_encode($scopes),
            'last_used_at' => null,
            'expires_at' => null,
        ]);
    }

    public function updateLastUsed(int $id): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'last_used_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function isExpired(array $token): bool
    {
        if (($token['expires_at'] ?? null) === null) {
            return false;
        }

        return strtotime($token['expires_at']) < time();
    }
}
