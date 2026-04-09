<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class NumberField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'number';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'numeric'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        if (isset($fieldConfig['config']['min'])) {
            $rules['min'] = $fieldConfig['config']['min'];
        }
        if (isset($fieldConfig['config']['max'])) {
            $rules['max'] = $fieldConfig['config']['max'];
        }

        return $rules;
    }

    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        if ($value === '' || $value === null) {
            return null;
        }

        $step = $fieldConfig['config']['step'] ?? null;

        return ($step !== null && is_int($step)) ? (int) $value : (float) $value;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'number';
    }
}
