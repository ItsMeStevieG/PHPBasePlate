<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */

$router->group(['prefix' => '/api'], function ($router) {
    $router->get('/health', function (Request $request): JsonResponse {
        return JsonResponse::success([
            'status' => 'ok',
            'php' => PHP_VERSION,
            'timestamp' => date('c'),
        ], 'PHPBasePlate V3 is running.');
    }, 'api.health');
});
