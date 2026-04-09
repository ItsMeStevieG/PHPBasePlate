<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

abstract class AbstractFieldType implements FieldTypeInterface
{
    public function normalise(mixed $value, array $fieldConfig): mixed
    {
        return $value;
    }

    public function serialise(mixed $value, array $fieldConfig): mixed
    {
        return $value;
    }
}
