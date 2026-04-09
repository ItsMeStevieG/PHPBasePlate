<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Schema;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;

class SchemaValidator
{
    private array $errors = [];

    public function __construct(private readonly FieldTypeRegistry $fieldTypes)
    {
    }

    public function validate(array $schema, string $filePath): bool
    {
        $this->errors = [];
        $prefix = basename($filePath);

        // Required top-level keys
        foreach (['name', 'label', 'fields'] as $key) {
            if (!isset($schema[$key])) {
                $this->errors[] = "[{$prefix}] Missing required key: {$key}";
            }
        }

        if (!empty($this->errors)) {
            return false;
        }

        // Name format
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $schema['name'])) {
            $this->errors[] = "[{$prefix}] 'name' must be lowercase alphanumeric with underscores.";
        }

        // Mode
        $mode = $schema['mode'] ?? 'collection';
        if (!in_array($mode, ['single', 'collection'], true)) {
            $this->errors[] = "[{$prefix}] 'mode' must be 'single' or 'collection'.";
        }

        // Fields
        if (!is_array($schema['fields']) || empty($schema['fields'])) {
            $this->errors[] = "[{$prefix}] 'fields' must be a non-empty array.";
            return false;
        }

        $fieldNames = [];
        foreach ($schema['fields'] as $index => $field) {
            $this->validateField($field, $index, $prefix, $fieldNames);
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateField(array $field, int $index, string $prefix, array &$fieldNames): void
    {
        foreach (['name', 'label', 'type'] as $key) {
            if (!isset($field[$key]) || $field[$key] === '') {
                $this->errors[] = "[{$prefix}] Field #{$index}: missing '{$key}'.";
            }
        }

        if (isset($field['name'])) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $field['name'])) {
                $this->errors[] = "[{$prefix}] Field '{$field['name']}': name must be lowercase alphanumeric with underscores.";
            }

            if (in_array($field['name'], $fieldNames, true)) {
                $this->errors[] = "[{$prefix}] Duplicate field name: '{$field['name']}'.";
            }

            $fieldNames[] = $field['name'];
        }

        if (isset($field['type']) && !$this->fieldTypes->has($field['type'])) {
            $this->errors[] = "[{$prefix}] Field '{$field['name']}': unknown type '{$field['type']}'.";
        }
    }
}
