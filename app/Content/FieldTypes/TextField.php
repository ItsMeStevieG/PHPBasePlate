<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class TextField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'text';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'string'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        $max = $fieldConfig['config']['max_length'] ?? 255;
        $rules['max_length'] = $max;

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'text';
    }
}
