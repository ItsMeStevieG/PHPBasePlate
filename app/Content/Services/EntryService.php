<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Services;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentEntryRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentTypeRepository;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Validators\EntryValidator;
use ItsMeStevieG\PHPBasePlate\Core\Support\Str;

class EntryService
{
    public function __construct(
        private readonly ContentEntryRepository $entryRepo,
        private readonly ContentTypeRepository $typeRepo,
        private readonly ContentTypeRegistry $typeRegistry,
        private readonly FieldTypeRegistry $fieldTypes,
        private readonly EntryValidator $validator,
        private readonly RevisionService $revisionService,
    ) {
    }

    public function list(
        string $typeName,
        int $page = 1,
        int $perPage = 20,
        ?string $status = null,
        ?string $search = null,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
    ): array {
        $dbType = $this->typeRepo->findByMachineName($typeName);
        if ($dbType === null) {
            throw new \RuntimeException("Content type '{$typeName}' not found in database.");
        }

        $result = $this->entryRepo->list(
            (int) $dbType['id'], $page, $perPage, $status, $search, $sortBy, $sortDir,
        );

        // Decode payload JSON for each item
        foreach ($result['items'] as &$item) {
            $item['payload'] = json_decode($item['payload_json'] ?? '{}', true) ?? [];
        }

        return $result;
    }

    public function find(int $id): ?array
    {
        $entry = $this->entryRepo->findById($id);
        if ($entry === null) {
            return null;
        }

        $entry['payload'] = json_decode($entry['payload_json'] ?? '{}', true) ?? [];

        return $entry;
    }

    public function findBySlug(string $typeName, string $slug): ?array
    {
        $dbType = $this->typeRepo->findByMachineName($typeName);
        if ($dbType === null) {
            return null;
        }

        $entry = $this->entryRepo->findBySlug((int) $dbType['id'], $slug);
        if ($entry === null) {
            return null;
        }

        $entry['payload'] = json_decode($entry['payload_json'] ?? '{}', true) ?? [];

        return $entry;
    }

    /**
     * @return array{success: bool, id?: int, errors?: array}
     */
    public function create(string $typeName, array $input, ?int $userId = null): array
    {
        $schema = $this->typeRegistry->get($typeName);
        if ($schema === null) {
            return ['success' => false, 'errors' => ['_global' => ['Unknown content type.']]];
        }

        $dbType = $this->typeRepo->findByMachineName($typeName);
        if ($dbType === null) {
            return ['success' => false, 'errors' => ['_global' => ['Content type not synced.']]];
        }

        // Extract and normalise payload fields
        $payload = $this->extractPayload($schema, $input);

        // Validate
        if (!$this->validator->validate($typeName, $payload)) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        // Build slug
        $title = $input['title'] ?? '';
        $slug = $this->generateUniqueSlug((int) $dbType['id'], $input['slug'] ?? $title);

        $id = $this->entryRepo->create([
            'content_type_id' => (int) $dbType['id'],
            'title' => $title,
            'slug' => $slug,
            'status' => $input['status'] ?? 'draft',
            'summary' => $input['summary'] ?? null,
            'payload' => $payload,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Create revision if enabled
        if ($schema['revisioning'] ?? false) {
            $this->revisionService->snapshot($id, [
                'title' => $title,
                'slug' => $slug,
                'status' => $input['status'] ?? 'draft',
                'summary' => $input['summary'] ?? null,
                'payload' => $payload,
                'saved_by' => $userId,
            ]);
        }

        return ['success' => true, 'id' => $id];
    }

    /**
     * @return array{success: bool, errors?: array}
     */
    public function update(int $id, string $typeName, array $input, ?int $userId = null): array
    {
        $schema = $this->typeRegistry->get($typeName);
        if ($schema === null) {
            return ['success' => false, 'errors' => ['_global' => ['Unknown content type.']]];
        }

        $existing = $this->entryRepo->findById($id);
        if ($existing === null) {
            return ['success' => false, 'errors' => ['_global' => ['Entry not found.']]];
        }

        $payload = $this->extractPayload($schema, $input);

        if (!$this->validator->validate($typeName, $payload, isUpdate: true)) {
            return ['success' => false, 'errors' => $this->validator->getErrors()];
        }

        $title = $input['title'] ?? $existing['title'];
        $slug = isset($input['slug']) && $input['slug'] !== ''
            ? $this->generateUniqueSlug((int) $existing['content_type_id'], $input['slug'], $id)
            : $existing['slug'];

        $this->entryRepo->update($id, [
            'title' => $title,
            'slug' => $slug,
            'status' => $input['status'] ?? $existing['status'],
            'summary' => $input['summary'] ?? $existing['summary'],
            'payload' => $payload,
            'updated_by' => $userId,
        ]);

        if ($schema['revisioning'] ?? false) {
            $this->revisionService->snapshot($id, [
                'title' => $title,
                'slug' => $slug,
                'status' => $input['status'] ?? $existing['status'],
                'summary' => $input['summary'] ?? $existing['summary'],
                'payload' => $payload,
                'saved_by' => $userId,
            ]);
        }

        return ['success' => true];
    }

    public function delete(int $id): void
    {
        $this->entryRepo->delete($id);
    }

    public function publish(int $id, int $userId): void
    {
        $this->entryRepo->publish($id, $userId);
    }

    public function unpublish(int $id, int $userId): void
    {
        $this->entryRepo->unpublish($id, $userId);
    }

    private function extractPayload(array $schema, array $input): array
    {
        $payload = [];

        foreach ($schema['fields'] as $field) {
            $name = $field['name'];
            if (!array_key_exists($name, $input)) {
                continue;
            }

            $fieldType = $this->fieldTypes->get($field['type']);
            $payload[$name] = $fieldType->normalise($input[$name], $field);
        }

        return $payload;
    }

    private function generateUniqueSlug(int $contentTypeId, string $source, ?int $excludeId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'entry';
        }

        $slug = $base;
        $counter = 1;

        while ($this->entryRepo->slugExists($contentTypeId, $slug, $excludeId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
