<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */
/** @var \ItsMeStevieG\PHPBasePlate\Core\App $app */

$currentApp = $app;

$router->group(['prefix' => '/api'], function ($router) use ($currentApp) {
    $router->get('/health', function (Request $request) use ($currentApp): JsonResponse {
        $registry = $currentApp->getContainer()->get(ContentTypeRegistry::class);

        return JsonResponse::success([
            'status' => 'ok',
            'php' => PHP_VERSION,
            'timestamp' => date('c'),
            'content_types' => array_keys($registry->all()),
        ], 'PHPBasePlate V3 is running.');
    }, 'api.health');
});
