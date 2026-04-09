<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class BooleanField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'boolean';
    }

    public function validationRules(array $fieldConfig): array
    {
        return ['type' => 'boolean'];
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'checkbox';
    }
}
