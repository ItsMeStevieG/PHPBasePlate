<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Schema;

class ContentTypeRegistry
{
    /** @var array<string, array> Keyed by machine_name */
    private array $types = [];

    public function register(array $schema): void
    {
        $this->types[$schema['name']] = $schema;
    }

    public function get(string $machineName): ?array
    {
        return $this->types[$machineName] ?? null;
    }

    public function has(string $machineName): bool
    {
        return isset($this->types[$machineName]);
    }

    /** @return array<string, array> */
    public function all(): array
    {
        return $this->types;
    }

    public function getField(string $typeName, string $fieldName): ?array
    {
        $type = $this->get($typeName);
        if ($type === null) {
            return null;
        }

        foreach ($type['fields'] as $field) {
            if ($field['name'] === $fieldName) {
                return $field;
            }
        }

        return null;
    }

    public function getFields(string $typeName): array
    {
        $type = $this->get($typeName);

        return $type['fields'] ?? [];
    }
}
