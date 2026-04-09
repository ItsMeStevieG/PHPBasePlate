<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

use ItsMeStevieG\PHPBasePlate\Core\Support\Str;

class SlugField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'slug';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'string', 'max_length' => 500];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        if (is_string($value) && $value !== '') {
            return Str::slug($value);
        }

        return $value;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'slug';
    }
}
