<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

class Seeder
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function run(string $seedsPath): array
    {
        $executed = [];
        $files = glob($seedsPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $seed = require $file;

            if (is_callable($seed)) {
                $seed($this->db);
            }

            $executed[] = basename($file);
        }

        return $executed;
    }
}
