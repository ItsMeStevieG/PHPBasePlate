<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class RichtextField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'richtext';
    }

    public function validationRules(array $fieldConfig): array
    {
        $rules = ['type' => 'string'];

        if ($fieldConfig['is_required'] ?? false) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'richtext';
    }
}
