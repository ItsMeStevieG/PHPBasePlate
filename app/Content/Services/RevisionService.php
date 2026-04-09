<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Services;

use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentRevisionRepository;

class RevisionService
{
    public function __construct(private readonly ContentRevisionRepository $revisionRepo)
    {
    }

    public function snapshot(int $entryId, array $data): int
    {
        return $this->revisionRepo->create($entryId, $data);
    }

    public function listForEntry(int $entryId): array
    {
        return $this->revisionRepo->listForEntry($entryId);
    }

    public function getRevision(int $entryId, int $revisionNumber): ?array
    {
        return $this->revisionRepo->findRevision($entryId, $revisionNumber);
    }
}
