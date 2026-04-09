<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Media\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class MediaRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne('SELECT * FROM media_files WHERE id = ?', [$id]);
    }

    public function list(int $page = 1, int $perPage = 24, ?string $mimeFilter = null): array
    {
        $where = '1=1';
        $bindings = [];

        if ($mimeFilter !== null) {
            $where .= ' AND mime_type LIKE ?';
            $bindings[] = $mimeFilter . '%';
        }

        $countRow = $this->db->fetchOne("SELECT COUNT(*) as total FROM media_files WHERE {$where}", $bindings);
        $total = (int) ($countRow['total'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $rows = $this->db->fetchAll(
            "SELECT * FROM media_files WHERE {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [...$bindings, $perPage, $offset],
        );

        return [
            'items' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / max($perPage, 1)),
        ];
    }

    public function create(array $data): int
    {
        $this->db->execute(
            'INSERT INTO media_files (disk, path, file_name, original_name, extension, mime_type,
             size_bytes, width, height, alt_text, title, caption, uploaded_by, meta_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['disk'] ?? 'local',
                $data['path'],
                $data['file_name'],
                $data['original_name'],
                $data['extension'],
                $data['mime_type'],
                $data['size_bytes'],
                $data['width'] ?? null,
                $data['height'] ?? null,
                $data['alt_text'] ?? null,
                $data['title'] ?? null,
                $data['caption'] ?? null,
                $data['uploaded_by'] ?? null,
                json_encode($data['meta'] ?? []),
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->db->execute(
            'UPDATE media_files SET alt_text = ?, title = ?, caption = ? WHERE id = ?',
            [$data['alt_text'] ?? null, $data['title'] ?? null, $data['caption'] ?? null, $id],
        );
    }

    public function delete(int $id): void
    {
        $this->db->execute('DELETE FROM media_files WHERE id = ?', [$id]);
    }
}
