<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

class Migrator
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function ensureMigrationsTable(): void
    {
        $this->db->execute(<<<'SQL'
            CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(191) NOT NULL,
                batch INT UNSIGNED NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function run(string $migrationsPath): array
    {
        $this->ensureMigrationsTable();

        $ran = $this->getRanMigrations();
        $batch = $this->getNextBatch();
        $executed = [];

        $files = glob($migrationsPath . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $ran, true)) {
                continue;
            }

            $sql = file_get_contents($file);

            // Split on semicolons to handle multiple statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn(string $s) => $s !== '',
            );

            foreach ($statements as $statement) {
                $this->db->execute($statement);
            }

            $this->db->execute(
                'INSERT INTO migrations (migration, batch) VALUES (?, ?)',
                [$name, $batch],
            );

            $executed[] = $name;
        }

        return $executed;
    }

    public function getRanMigrations(): array
    {
        $rows = $this->db->fetchAll('SELECT migration FROM migrations ORDER BY id');

        return array_column($rows, 'migration');
    }

    private function getNextBatch(): int
    {
        $result = $this->db->fetchOne('SELECT MAX(batch) as max_batch FROM migrations');

        return ($result['max_batch'] ?? 0) + 1;
    }
}
