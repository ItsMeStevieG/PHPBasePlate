<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Services;

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Schema\SchemaLoader;

class SchemaService
{
    public function __construct(
        private readonly SchemaLoader $loader,
        private readonly ContentTypeRegistry $registry,
        private readonly object $typeRepo,
    ) {
    }

    /**
     * Load all schemas and optionally sync to database.
     */
    public function loadAndSync(bool $syncToDb = false): array
    {
        $result = $this->loader->loadAll();

        if ($syncToDb && !empty($result['loaded'])) {
            foreach ($result['loaded'] as $name) {
                $this->syncToDatabase($name);
            }
        }

        return $result;
    }

    /**
     * Sync a single content type's schema to the database.
     */
    public function syncToDatabase(string $typeName): void
    {
        $schema = $this->registry->get($typeName);
        if ($schema === null) {
            return;
        }

        $titleField = null;
        $slugField = null;

        foreach ($schema['fields'] as $field) {
            if ($field['type'] === 'slug') {
                $slugField = $field['name'];
            }
        }

        // Find the first text field as title candidate
        if (!isset($schema['title_field'])) {
            foreach ($schema['fields'] as $field) {
                if ($field['type'] === 'text') {
                    $titleField = $field['name'];
                    break;
                }
            }
        } else {
            $titleField = $schema['title_field'];
        }

        $typeId = $this->typeRepo->upsert([
            'machine_name' => $schema['name'],
            'label' => $schema['label'],
            'mode' => $schema['mode'],
            'schema_path' => $schema['schema_path'] ?? null,
            'slug_field' => $slugField,
            'title_field' => $titleField,
            'status_field_enabled' => $schema['status_enabled'] ?? true,
            'revisioning_enabled' => $schema['revisioning'] ?? false,
            'api_public_read' => $schema['api']['public_read'] ?? true,
            'api_auth_write' => $schema['api']['auth_write'] ?? true,
            'config' => $schema['admin'] ?? [],
        ]);

        $this->typeRepo->syncFields($typeId, $schema['fields']);
    }

    public function getRegistry(): ContentTypeRegistry
    {
        return $this->registry;
    }
}
