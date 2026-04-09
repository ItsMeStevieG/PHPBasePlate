<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class DatetimeField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'datetime';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'datetime'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'datetime';
    }
}
