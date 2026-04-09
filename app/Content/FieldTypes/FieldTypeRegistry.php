<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\FieldTypes;

class FieldTypeRegistry
{
    /** @var array<string, FieldTypeInterface> */
    private array $types = [];

    public function register(FieldTypeInterface $fieldType): void
    {
        $this->types[$fieldType->getType()] = $fieldType;
    }

    public function get(string $type): FieldTypeInterface
    {
        if (!isset($this->types[$type])) {
            throw new \RuntimeException("Unknown field type: {$type}");
        }

        return $this->types[$type];
    }

    public function has(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /** @return array<string, FieldTypeInterface> */
    public function all(): array
    {
        return $this->types;
    }

    public static function createDefault(): self
    {
        $registry = new self();

        $registry->register(new TextField());
        $registry->register(new TextareaField());
        $registry->register(new RichtextField());
        $registry->register(new SlugField());
        $registry->register(new NumberField());
        $registry->register(new BooleanField());
        $registry->register(new DateField());
        $registry->register(new DatetimeField());
        $registry->register(new SelectField());
        $registry->register(new ImageField());
        $registry->register(new FileField());
        $registry->register(new RelationField());
        $registry->register(new RepeaterField());
        $registry->register(new JsonField());

        return $registry;
    }
}
