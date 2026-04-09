<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Config\Env;

return [
    'host' => Env::get('DB_HOST', 'localhost'),
    'port' => (int) Env::get('DB_PORT', 3306),
    'database' => Env::get('DB_DATABASE', 'phpbaseplate'),
    'username' => Env::get('DB_USERNAME', 'root'),
    'password' => Env::get('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
