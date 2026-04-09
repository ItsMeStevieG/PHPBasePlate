<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class UserRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO users (name, email, password_hash, status) VALUES (?, ?, ?, ?)',
            [
                $data['name'],
                $data['email'],
                $data['password_hash'],
                $data['status'] ?? 'active',
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->execute(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [$id],
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM users ORDER BY name');
    }
}
