<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

use PDO;
use PDOStatement;

class Connection
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $charset = 'utf8mb4',
    ) {
    }

    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";

            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $this->pdo;
    }

    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt;
    }

    public function fetchAll(string $sql, array $bindings = []): array
    {
        return $this->query($sql, $bindings)->fetchAll();
    }

    public function fetchOne(string $sql, array $bindings = []): ?array
    {
        $result = $this->query($sql, $bindings)->fetch();

        return $result !== false ? $result : null;
    }

    public function execute(string $sql, array $bindings = []): int
    {
        $stmt = $this->query($sql, $bindings);

        return $stmt->rowCount();
    }

    public function lastInsertId(): string
    {
        return $this->getPdo()->lastInsertId();
    }

    public function transaction(callable $callback): mixed
    {
        $this->getPdo()->beginTransaction();

        try {
            $result = $callback($this);
            $this->getPdo()->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->getPdo()->rollBack();
            throw $e;
        }
    }
}
