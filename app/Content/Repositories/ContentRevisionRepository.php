<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class ContentRevisionRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function create(int $entryId, array $data): int
    {
        $nextRevision = $this->getNextRevisionNumber($entryId);

        $this->db->execute(
            'INSERT INTO content_entry_revisions (content_entry_id, revision_number, title,
             slug, status, summary, payload_json, saved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $entryId,
                $nextRevision,
                $data['title'] ?? null,
                $data['slug'] ?? null,
                $data['status'] ?? 'draft',
                $data['summary'] ?? null,
                json_encode($data['payload'] ?? []),
                $data['saved_by'] ?? null,
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function listForEntry(int $entryId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM content_entry_revisions WHERE content_entry_id = ? ORDER BY revision_number DESC',
            [$entryId],
        );
    }

    public function findRevision(int $entryId, int $revisionNumber): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM content_entry_revisions WHERE content_entry_id = ? AND revision_number = ?',
            [$entryId, $revisionNumber],
        );
    }

    private function getNextRevisionNumber(int $entryId): int
    {
        $row = $this->db->fetchOne(
            'SELECT MAX(revision_number) as max_rev FROM content_entry_revisions WHERE content_entry_id = ?',
            [$entryId],
        );

        return ($row['max_rev'] ?? 0) + 1;
    }
}
