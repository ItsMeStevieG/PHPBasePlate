<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\App;

$basePath = dirname(__DIR__);

$app = new App($basePath);
$app->bootstrap();

return $app;
