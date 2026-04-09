<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Config\Env;

return [
    'cache' => Env::get('APP_ENV', 'production') === 'production',
];
