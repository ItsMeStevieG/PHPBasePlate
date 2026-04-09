<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = require_once dirname(__DIR__) . '/bootstrap/app.php';

$request = \ItsMeStevieG\PHPBasePlate\Core\Http\Request::capture();

$response = $app->run($request);

$response->send();
