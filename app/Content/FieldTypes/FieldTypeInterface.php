<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

interface FieldTypeInterface
{
    /**
     * Return the machine name of this field type (e.g. 'text', 'richtext').
     */
    public function getType(): string;

    /**
     * Return validation rules for this field given its schema config.
     *
     * @return array<string, mixed> Key-value validation constraints.
     */
    public function validationRules(array $fieldConfig): array;

    /**
     * Normalise a value before persistence.
     */
    public function normalise(mixed $value, array $fieldConfig): mixed;

    /**
     * Serialise a value for API output.
     */
    public function serialise(mixed $value, array $fieldConfig): mixed;

    /**
     * Return the Twig form widget type hint for admin form rendering.
     */
    public function formWidget(array $fieldConfig): string;
}
