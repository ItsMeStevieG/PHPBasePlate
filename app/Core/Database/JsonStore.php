<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

/**
 * Generic JSON flat-file storage engine.
 *
 * Stores collections as JSON files in a given directory.
 * Each collection is a single JSON file containing an array of records.
 * Provides CRUD, filtering, pagination, and auto-incrementing IDs.
 */
class JsonStore
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');

        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0775, true);
        }
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Get all records from a collection.
     */
    public function all(string $collection): array
    {
        return $this->read($collection);
    }

    /**
     * Find a record by ID.
     */
    public function find(string $collection, int $id): ?array
    {
        $records = $this->read($collection);

        foreach ($records as $record) {
            if ((int) ($record['id'] ?? 0) === $id) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Find first record matching conditions.
     */
    public function findWhere(string $collection, array $conditions): ?array
    {
        $records = $this->read($collection);

        foreach ($records as $record) {
            if ($this->matchesConditions($record, $conditions)) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Find all records matching conditions.
     */
    public function where(string $collection, array $conditions): array
    {
        $records = $this->read($collection);

        return array_values(array_filter(
            $records,
            fn(array $r) => $this->matchesConditions($r, $conditions),
        ));
    }

    /**
     * Insert a record with auto-incrementing ID.
     */
    public function insert(string $collection, array $data): int
    {
        $records = $this->read($collection);
        $meta = $this->readMeta($collection);

        $nextId = ($meta['next_id'] ?? 1);
        $data['id'] = $nextId;
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');

        $records[] = $data;
        $meta['next_id'] = $nextId + 1;

        $this->write($collection, $records);
        $this->writeMeta($collection, $meta);

        return $nextId;
    }

    /**
     * Update a record by ID.
     */
    public function update(string $collection, int $id, array $data): bool
    {
        $records = $this->read($collection);
        $found = false;

        foreach ($records as &$record) {
            if ((int) ($record['id'] ?? 0) === $id) {
                $record = array_merge($record, $data);
                $record['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->write($collection, $records);
        }

        return $found;
    }

    /**
     * Delete a record by ID.
     */
    public function delete(string $collection, int $id): bool
    {
        $records = $this->read($collection);
        $count = count($records);

        $records = array_values(array_filter(
            $records,
            fn(array $r) => (int) ($r['id'] ?? 0) !== $id,
        ));

        if (count($records) < $count) {
            $this->write($collection, $records);
            return true;
        }

        return false;
    }

    /**
     * Paginated list with optional filtering, search, and sort.
     */
    public function paginate(
        string $collection,
        int $page = 1,
        int $perPage = 20,
        array $conditions = [],
        ?string $search = null,
        array $searchFields = [],
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
    ): array {
        $records = $this->read($collection);

        // Filter
        if (!empty($conditions)) {
            $records = array_filter(
                $records,
                fn(array $r) => $this->matchesConditions($r, $conditions),
            );
        }

        // Search
        if ($search !== null && $search !== '' && !empty($searchFields)) {
            $needle = mb_strtolower($search);
            $records = array_filter($records, function (array $r) use ($needle, $searchFields) {
                foreach ($searchFields as $field) {
                    $value = $r[$field] ?? '';
                    if (is_string($value) && str_contains(mb_strtolower($value), $needle)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $records = array_values($records);

        // Sort
        usort($records, function (array $a, array $b) use ($sortBy, $sortDir) {
            $aVal = $a[$sortBy] ?? '';
            $bVal = $b[$sortBy] ?? '';

            $cmp = is_numeric($aVal) && is_numeric($bVal)
                ? $aVal <=> $bVal
                : strcmp((string) $aVal, (string) $bVal);

            return $sortDir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($records);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($records, $offset, $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / max($perPage, 1)),
        ];
    }

    /**
     * Count records matching conditions.
     */
    public function count(string $collection, array $conditions = []): int
    {
        if (empty($conditions)) {
            return count($this->read($collection));
        }

        return count($this->where($collection, $conditions));
    }

    private function matchesConditions(array $record, array $conditions): bool
    {
        foreach ($conditions as $key => $value) {
            if (($record[$key] ?? null) != $value) {
                return false;
            }
        }

        return true;
    }

    private function filePath(string $collection): string
    {
        return $this->basePath . '/' . $collection . '.json';
    }

    private function metaPath(string $collection): string
    {
        return $this->basePath . '/' . $collection . '_meta.json';
    }

    private function read(string $collection): array
    {
        $path = $this->filePath($collection);

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    private function write(string $collection, array $records): void
    {
        $path = $this->filePath($collection);
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $path,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX,
        );
    }

    private function readMeta(string $collection): array
    {
        $path = $this->metaPath($collection);

        if (!file_exists($path)) {
            return ['next_id' => 1];
        }

        $data = json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : ['next_id' => 1];
    }

    private function writeMeta(string $collection, array $meta): void
    {
        file_put_contents(
            $this->metaPath($collection),
            json_encode($meta, JSON_PRETTY_PRINT),
            LOCK_EX,
        );
    }
}
