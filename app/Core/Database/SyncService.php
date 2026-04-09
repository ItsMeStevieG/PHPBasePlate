<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

/**
 * Synchronises data between JSON flat-file storage and MySQL database.
 *
 * Used to keep a "last known good" snapshot in the backup storage,
 * so failover has recent data to work with.
 */
class SyncService
{
    /** Tables/collections to sync, in dependency order */
    private const COLLECTIONS = [
        'users',
        'roles',
        'permissions',
        'role_user',
        'permission_role',
        'api_tokens',
        'content_types',
        'content_fields',
        'content_entries',
        'content_entry_revisions',
        'content_relations',
        'media_files',
        'media_links',
        'settings_groups',
        'settings_items',
        'menus',
        'menu_items',
    ];

    public function __construct(
        private readonly JsonStore $jsonStore,
        private readonly Connection $db,
    ) {
    }

    /**
     * Export database tables to JSON flat files (DB -> JSON).
     *
     * @return array{synced: string[], errors: string[]}
     */
    public function databaseToJson(): array
    {
        $synced = [];
        $errors = [];

        foreach (self::COLLECTIONS as $table) {
            try {
                $rows = $this->db->fetchAll("SELECT * FROM {$table} ORDER BY id");
                $this->writeJsonCollection($table, $rows);
                $synced[] = $table;
            } catch (\Throwable $e) {
                // Table might not exist yet (no migrations run)
                $errors[] = "{$table}: {$e->getMessage()}";
            }
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Import JSON flat files into database tables (JSON -> DB).
     * Clears existing data and re-inserts from JSON.
     *
     * @return array{synced: string[], errors: string[]}
     */
    public function jsonToDatabase(): array
    {
        $synced = [];
        $errors = [];

        // Disable FK checks for clean import
        $this->db->execute('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach (self::COLLECTIONS as $table) {
                try {
                    $rows = $this->jsonStore->all($table);

                    if (empty($rows)) {
                        continue;
                    }

                    // Truncate and re-insert
                    $this->db->execute("TRUNCATE TABLE {$table}");

                    foreach ($rows as $row) {
                        // Remove JsonStore metadata fields
                        unset($row['created_at_orig'], $row['updated_at_orig']);

                        $columns = array_keys($row);
                        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                        $columnList = implode(', ', $columns);

                        $this->db->execute(
                            "INSERT INTO {$table} ({$columnList}) VALUES ({$placeholders})",
                            array_values($row),
                        );
                    }

                    $synced[] = $table . ' (' . count($rows) . ' rows)';
                } catch (\Throwable $e) {
                    $errors[] = "{$table}: {$e->getMessage()}";
                }
            }
        } finally {
            $this->db->execute('SET FOREIGN_KEY_CHECKS = 1');
        }

        return ['synced' => $synced, 'errors' => $errors];
    }

    /**
     * Write a complete collection to JSON, setting the next_id meta correctly.
     */
    private function writeJsonCollection(string $collection, array $rows): void
    {
        $filePath = $this->jsonStore->getBasePath() . '/' . $collection . '.json';
        $metaPath = $this->jsonStore->getBasePath() . '/' . $collection . '_meta.json';

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $filePath,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX,
        );

        // Calculate next ID
        $maxId = 0;
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > $maxId) {
                $maxId = $id;
            }
        }

        file_put_contents(
            $metaPath,
            json_encode(['next_id' => $maxId + 1], JSON_PRETTY_PRINT),
            LOCK_EX,
        );
    }
}
