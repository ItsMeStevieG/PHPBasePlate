<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class SelectField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'select';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'string'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        $options = $fieldConfig['config']['options'] ?? [];
        if (!empty($options)) {
            $rules['in'] = array_column($options, 'value');
        }

        return $rules;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'select';
    }
}
