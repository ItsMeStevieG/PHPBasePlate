<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class JsonField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'json';
    }

    public function validationRules(array $fieldConfig): array
    {
        return ['type' => 'json'];
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?? $value;
        }

        return $value;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'json';
    }
}
