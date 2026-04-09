<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Validators;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;

class EntryValidator
{
    private array $errors = [];

    public function __construct(
        private readonly ContentTypeRegistry $typeRegistry,
        private readonly FieldTypeRegistry $fieldTypes,
    ) {
    }

    public function validate(string $typeName, array $payload, bool $isUpdate = false): bool
    {
        $this->errors = [];

        $fields = $this->typeRegistry->getFields($typeName);

        foreach ($fields as $field) {
            $value = $payload[$field['name']] ?? null;
            $fieldType = $this->fieldTypes->get($field['type']);
            $rules = $fieldType->validationRules($field);

            $this->validateField($field['name'], $field['label'], $value, $rules, $isUpdate);
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateField(string $name, string $label, mixed $value, array $rules, bool $isUpdate): void
    {
        $isRequired = $rules['required'] ?? false;
        $isEmpty = ($value === null || $value === '' || $value === []);

        if ($isRequired && $isEmpty && !$isUpdate) {
            $this->errors[$name][] = "The {$label} field is required.";
            return;
        }

        if ($isEmpty) {
            return;
        }

        $type = $rules['type'] ?? null;

        if ($type === 'string' && !is_string($value)) {
            $this->errors[$name][] = "The {$label} field must be a string.";
        }

        if ($type === 'numeric' && !is_numeric($value)) {
            $this->errors[$name][] = "The {$label} field must be a number.";
        }

        if ($type === 'integer' && !is_numeric($value)) {
            $this->errors[$name][] = "The {$label} field must be an integer.";
        }

        if ($type === 'boolean' && !is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
            $this->errors[$name][] = "The {$label} field must be a boolean.";
        }

        if ($type === 'date' && is_string($value) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $this->errors[$name][] = "The {$label} field must be a valid date (YYYY-MM-DD).";
        }

        if ($type === 'datetime' && is_string($value) && !strtotime($value)) {
            $this->errors[$name][] = "The {$label} field must be a valid datetime.";
        }

        if (isset($rules['max_length']) && is_string($value) && mb_strlen($value) > $rules['max_length']) {
            $this->errors[$name][] = "The {$label} field must not exceed {$rules['max_length']} characters.";
        }

        if (isset($rules['min']) && is_numeric($value) && $value < $rules['min']) {
            $this->errors[$name][] = "The {$label} field must be at least {$rules['min']}.";
        }

        if (isset($rules['max']) && is_numeric($value) && $value > $rules['max']) {
            $this->errors[$name][] = "The {$label} field must not exceed {$rules['max']}.";
        }

        if (isset($rules['in']) && !in_array($value, $rules['in'], true)) {
            $allowed = implode(', ', $rules['in']);
            $this->errors[$name][] = "The {$label} field must be one of: {$allowed}.";
        }
    }
}
