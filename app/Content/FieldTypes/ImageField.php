<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class ImageField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'image';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'integer'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        return ($value !== '' && $value !== null) ? (int) $value : null;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'image';
    }
}
