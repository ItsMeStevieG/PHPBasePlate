<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Schema;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;

class SchemaLoader
{
    public function __construct(
        private readonly string $schemaPath,
        private readonly SchemaValidator $validator,
        private readonly ContentTypeRegistry $registry,
        private readonly FieldTypeRegistry $fieldTypes,
    ) {
    }

    /**
     * Discover and load all schema JSON files.
     *
     * @return array{loaded: string[], errors: array<string, string[]>}
     */
    public function loadAll(): array
    {
        $loaded = [];
        $errors = [];

        $files = glob($this->schemaPath . '/*.json');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $schema = json_decode($content, true);

            if ($schema === null) {
                $errors[basename($file)] = ['Invalid JSON: ' . json_last_error_msg()];
                continue;
            }

            if (!$this->validator->validate($schema, $file)) {
                $errors[basename($file)] = $this->validator->getErrors();
                continue;
            }

            // Enrich with computed defaults
            $schema = $this->enrichSchema($schema, $file);

            $this->registry->register($schema);
            $loaded[] = $schema['name'];
        }

        return ['loaded' => $loaded, 'errors' => $errors];
    }

    public function loadSingle(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        $schema = json_decode($content, true);

        if ($schema === null || !$this->validator->validate($schema, $filePath)) {
            return null;
        }

        $schema = $this->enrichSchema($schema, $filePath);
        $this->registry->register($schema);

        return $schema;
    }

    private function enrichSchema(array $schema, string $filePath): array
    {
        $schema['schema_path'] = $filePath;
        $schema['mode'] = $schema['mode'] ?? 'collection';
        $schema['status_enabled'] = $schema['status_enabled'] ?? true;
        $schema['revisioning'] = $schema['revisioning'] ?? false;

        $schema['api'] = array_merge([
            'public_read' => true,
            'auth_write' => true,
            'allow_delete' => false,
        ], $schema['api'] ?? []);

        $schema['admin'] = array_merge([
            'icon' => 'bi-file-text',
            'nav_group' => 'Content',
        ], $schema['admin'] ?? []);

        // Enrich fields with sort order
        foreach ($schema['fields'] as $index => &$field) {
            $field['sort_order'] = $field['sort_order'] ?? $index;
            $field['is_required'] = $field['required'] ?? false;
            $field['is_searchable'] = $field['searchable'] ?? false;
            $field['is_filterable'] = $field['filterable'] ?? false;
            $field['is_sortable'] = $field['sortable'] ?? false;
            $field['config'] = $field['config'] ?? [];
        }

        return $schema;
    }
}
