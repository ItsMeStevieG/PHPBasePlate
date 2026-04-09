<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class TextareaField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'textarea';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'string'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'textarea';
    }
}
