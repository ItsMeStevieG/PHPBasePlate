<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Services;

class SettingsService
{
    private array $cache = [];

    public function __construct(private readonly object $repo)
    {
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $values = $this->getGroup($group);

        return $values[$key] ?? $default;
    }

    public function getGroup(string $group): array
    {
        if (!isset($this->cache[$group])) {
            $this->cache[$group] = $this->repo->getGroupValues($group);
        }

        return $this->cache[$group];
    }

    public function allGroups(): array
    {
        return $this->repo->allGroups();
    }

    public function getGroupWithItems(string $machineName): ?array
    {
        $group = $this->repo->findGroup($machineName);
        if ($group === null) {
            return null;
        }

        $group['items'] = $this->repo->getItems((int) $group['id']);

        // Decode values
        foreach ($group['items'] as &$item) {
            $item['value'] = json_decode($item['value_json'] ?? 'null', true);
        }

        return $group;
    }

    public function updateGroup(string $machineName, array $values): void
    {
        $group = $this->repo->findGroup($machineName);
        if ($group === null) {
            return;
        }

        $items = $this->repo->getItems((int) $group['id']);

        foreach ($items as $item) {
            if (array_key_exists($item['machine_name'], $values)) {
                $this->repo->updateItem((int) $item['id'], $values[$item['machine_name']]);
            }
        }

        unset($this->cache[$machineName]);
    }
}
