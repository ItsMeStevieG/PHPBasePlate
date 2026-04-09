<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Config;

class Config
{
    private array $items = [];

    public function __construct(private readonly string $configPath)
    {
    }

    public function load(): void
    {
        $files = glob($this->configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $data = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }

        return $data;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $data = &$this->items;

        foreach (array_slice($segments, 0, -1) as $segment) {
            if (!isset($data[$segment]) || !is_array($data[$segment])) {
                $data[$segment] = [];
            }
            $data = &$data[$segment];
        }

        $data[end($segments)] = $value;
    }

    public function all(): array
    {
        return $this->items;
    }
}
