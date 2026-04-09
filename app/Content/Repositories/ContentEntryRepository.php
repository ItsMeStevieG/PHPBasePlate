<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class ContentEntryRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM content_entries WHERE id = ?', [$id]);
    }

    public function findBySlug(int $contentTypeId, string $slug): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM content_entries WHERE content_type_id = ? AND slug = ?',
            [$contentTypeId, $slug],
        );
    }

    public function list(
        int $contentTypeId,
        int $page = 1,
        int $perPage = 20,
        ?string $status = null,
        ?string $search = null,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
    ): array {
        $where = ['content_type_id = ?'];
        $bindings = [$contentTypeId];

        if ($status !== null) {
            $where[] = 'status = ?';
            $bindings[] = $status;
        }

        if ($search !== null && $search !== '') {
            $where[] = '(title LIKE ? OR summary LIKE ?)';
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        $whereClause = implode(' AND ', $where);

        // Whitelist sort columns
        $allowedSorts = ['id', 'title', 'slug', 'status', 'created_at', 'updated_at', 'published_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        // Count
        $countRow = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM content_entries WHERE {$whereClause}",
            $bindings,
        );
        $total = (int) ($countRow['total'] ?? 0);

        // Fetch
        $offset = ($page - 1) * $perPage;
        $rows = $this->db->fetchAll(
            "SELECT * FROM content_entries WHERE {$whereClause} ORDER BY {$sortBy} {$sortDir} LIMIT ? OFFSET ?",
            [...$bindings, $perPage, $offset],
        );

        return [
            'items' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO content_entries (content_type_id, title, slug, status, summary,
             payload_json, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['content_type_id'],
                $data['title'] ?? null,
                $data['slug'] ?? null,
                $data['status'] ?? 'draft',
                $data['summary'] ?? null,
                json_encode($data['payload'] ?? []),
                $data['created_by'] ?? null,
                $data['updated_by'] ?? null,
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->execute(
            'UPDATE content_entries SET title = ?, slug = ?, status = ?, summary = ?,
             payload_json = ?, updated_by = ? WHERE id = ?',
            [
                $data['title'] ?? null,
                $data['slug'] ?? null,
                $data['status'] ?? 'draft',
                $data['summary'] ?? null,
                json_encode($data['payload'] ?? []),
                $data['updated_by'] ?? null,
                $id,
            ],
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM content_entries WHERE id = ?', [$id]);
    }

    public function publish(int $id, int $userId): void
    {
        $this->db->execute(
            'UPDATE content_entries SET status = ?, published_by = ?, published_at = NOW(), updated_by = ? WHERE id = ?',
            ['published', $userId, $userId, $id],
        );
    }

    public function unpublish(int $id, int $userId): void
    {
        $this->db->execute(
            'UPDATE content_entries SET status = ?, updated_by = ? WHERE id = ?',
            ['draft', $userId, $id],
        );
    }

    public function slugExists(int $contentTypeId, string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) as cnt FROM content_entries WHERE content_type_id = ? AND slug = ?';
        $bindings = [$contentTypeId, $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $bindings[] = $excludeId;
        }

        $row = $this->db->fetchOne($sql, $bindings);

        return ((int) ($row['cnt'] ?? 0)) > 0;
    }
}
