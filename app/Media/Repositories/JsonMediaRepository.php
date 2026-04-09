<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Media\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonMediaRepository
{
    private const COLLECTION = 'media_files';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->store->find(self::COLLECTION, $id);
    }

    public function list(int $page = 1, int $perPage = 24, ?string $mimeFilter = null): array
    {
        $conditions = [];

        // JsonStore doesn't support LIKE, so we filter manually for mime
        if ($mimeFilter !== null) {
            $all = $this->store->all(self::COLLECTION);
            $filtered = array_values(array_filter($all, function ($r) use ($mimeFilter) {
                return str_starts_with($r['mime_type'] ?? '', $mimeFilter);
            }));

            usort($filtered, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

            $total = count($filtered);
            $offset = ($page - 1) * $perPage;

            return [
                'items' => array_slice($filtered, $offset, $perPage),
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / max($perPage, 1)),
            ];
        }

        return $this->store->paginate(self::COLLECTION, $page, $perPage);
    }

    public function create(array $data): int
    {
        return $this->store->insert(self::COLLECTION, [
            'disk' => $data['disk'] ?? 'local',
            'path' => $data['path'],
            'file_name' => $data['file_name'],
            'original_name' => $data['original_name'],
            'extension' => $data['extension'],
            'mime_type' => $data['mime_type'],
            'size_bytes' => $data['size_bytes'],
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'title' => $data['title'] ?? null,
            'caption' => $data['caption'] ?? null,
            'uploaded_by' => $data['uploaded_by'] ?? null,
            'meta_json' => json_encode($data['meta'] ?? []),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'alt_text' => $data['alt_text'] ?? null,
            'title' => $data['title'] ?? null,
            'caption' => $data['caption'] ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $this->store->delete(self::COLLECTION, $id);
    }
}
