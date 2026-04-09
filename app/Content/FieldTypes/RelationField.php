<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class RelationField extends AbstractFieldType
{
    public function getType(): string
    {
        return 'relation';
    }

    public function validationRules(array $fieldConfig): array
    {
        return ['type' => 'relation'];
    }

    public function formWidget(array $fieldConfig): string
    {
        return 'relation';
    }
}
