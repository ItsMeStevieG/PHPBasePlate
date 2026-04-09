<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonContentEntryRepository
{
    private const COLLECTION = 'content_entries';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findById(int $id): ?array
    {
        return $this->store->find(self::COLLECTION, $id);
    }

    public function findBySlug(int $contentTypeId, string $slug): ?array
    {
        return $this->store->findWhere(self::COLLECTION, [
            'content_type_id' => $contentTypeId,
            'slug' => $slug,
        ]);
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
        $conditions = ['content_type_id' => $contentTypeId];

        if ($status !== null) {
            $conditions['status'] = $status;
        }

        $allowedSorts = ['id', 'title', 'slug', 'status', 'created_at', 'updated_at', 'published_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        return $this->store->paginate(
            self::COLLECTION,
            $page,
            $perPage,
            $conditions,
            $search,
            ['title', 'summary'],
            $sortBy,
            strtolower($sortDir) === 'asc' ? 'asc' : 'desc',
        );
    }

    public function create(array $data): int
    {
        return $this->store->insert(self::COLLECTION, [
            'content_type_id' => $data['content_type_id'],
            'title' => $data['title'] ?? null,
            'slug' => $data['slug'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'summary' => $data['summary'] ?? null,
            'payload_json' => json_encode($data['payload'] ?? []),
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
            'published_by' => null,
            'published_at' => null,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'title' => $data['title'] ?? null,
            'slug' => $data['slug'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'summary' => $data['summary'] ?? null,
            'payload_json' => json_encode($data['payload'] ?? []),
            'updated_by' => $data['updated_by'] ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $this->store->delete(self::COLLECTION, $id);
    }

    public function publish(int $id, int $userId): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'status' => 'published',
            'published_by' => $userId,
            'published_at' => date('Y-m-d H:i:s'),
            'updated_by' => $userId,
        ]);
    }

    public function unpublish(int $id, int $userId): void
    {
        $this->store->update(self::COLLECTION, $id, [
            'status' => 'draft',
            'updated_by' => $userId,
        ]);
    }

    public function slugExists(int $contentTypeId, string $slug, ?int $excludeId = null): bool
    {
        $entries = $this->store->where(self::COLLECTION, [
            'content_type_id' => $contentTypeId,
            'slug' => $slug,
        ]);

        if ($excludeId !== null) {
            $entries = array_filter($entries, fn($e) => (int) $e['id'] !== $excludeId);
        }

        return count($entries) > 0;
    }
}
