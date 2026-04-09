<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonContentRevisionRepository
{
    private const COLLECTION = 'content_revisions';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function create(int $entryId, array $data): int
    {
        $existing = $this->store->where(self::COLLECTION, ['content_entry_id' => $entryId]);
        $maxRev = 0;
        foreach ($existing as $rev) {
            $maxRev = max($maxRev, (int) ($rev['revision_number'] ?? 0));
        }

        return $this->store->insert(self::COLLECTION, [
            'content_entry_id' => $entryId,
            'revision_number' => $maxRev + 1,
            'title' => $data['title'] ?? null,
            'slug' => $data['slug'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'summary' => $data['summary'] ?? null,
            'payload_json' => json_encode($data['payload'] ?? []),
            'saved_by' => $data['saved_by'] ?? null,
        ]);
    }

    public function listForEntry(int $entryId): array
    {
        $revisions = $this->store->where(self::COLLECTION, ['content_entry_id' => $entryId]);

        usort($revisions, fn($a, $b) => ($b['revision_number'] ?? 0) <=> ($a['revision_number'] ?? 0));

        return $revisions;
    }

    public function findRevision(int $entryId, int $revisionNumber): ?array
    {
        return $this->store->findWhere(self::COLLECTION, [
            'content_entry_id' => $entryId,
            'revision_number' => $revisionNumber,
        ]);
    }
}
