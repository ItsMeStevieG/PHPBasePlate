<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class RepeaterField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'repeater';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'array'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'repeater';
    }
}
