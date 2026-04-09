<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;

class JsonContentTypeRepository
{
    private const TYPES = 'content_types';
    private const FIELDS = 'content_fields';

    public function __construct(private readonly JsonStore $store)
    {
    }

    public function findByMachineName(string $machineName): ?array
    {
        return $this->store->findWhere(self::TYPES, ['machine_name' => $machineName]);
    }

    public function upsert(array $data): int
    {
        $existing = $this->findByMachineName($data['machine_name']);

        if ($existing) {
            $this->store->update(self::TYPES, (int) $existing['id'], $data);
            return (int) $existing['id'];
        }

        return $this->store->insert(self::TYPES, $data);
    }

    public function syncFields(int $contentTypeId, array $fields): void
    {
        // Remove old fields for this type
        $existing = $this->store->where(self::FIELDS, ['content_type_id' => $contentTypeId]);
        foreach ($existing as $f) {
            $this->store->delete(self::FIELDS, (int) $f['id']);
        }

        // Insert fresh
        foreach ($fields as $field) {
            $this->store->insert(self::FIELDS, [
                'content_type_id' => $contentTypeId,
                'machine_name' => $field['name'],
                'label' => $field['label'],
                'field_type' => $field['type'],
                'sort_order' => $field['sort_order'] ?? 0,
                'is_required' => $field['is_required'] ?? false,
                'is_searchable' => $field['is_searchable'] ?? false,
                'is_filterable' => $field['is_filterable'] ?? false,
                'is_sortable' => $field['is_sortable'] ?? false,
                'config_json' => json_encode($field['config'] ?? []),
            ]);
        }
    }

    public function all(): array
    {
        return $this->store->all(self::TYPES);
    }
}
