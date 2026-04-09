<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class ApiTokenRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByTokenHash(string $hash): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM api_tokens WHERE token_hash = ?',
            [$hash],
        );
    }

    public function create(string $name, string $tokenHash, ?int $userId = null, array $scopes = []): int
    {
        $this->db->execute(
            'INSERT INTO api_tokens (name, token_hash, user_id, scopes_json) VALUES (?, ?, ?, ?)',
            [$name, $tokenHash, $userId, json_encode($scopes)],
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateLastUsed(int $id): void
    {
        $this->db->execute(
            'UPDATE api_tokens SET last_used_at = NOW() WHERE id = ?',
            [$id],
        );
    }

    public function isExpired(array $token): bool
    {
        if ($token['expires_at'] === null) {
            return false;
        }

        return strtotime($token['expires_at']) < time();
    }
}
