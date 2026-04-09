<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Api\Controllers\ContentApiController;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\ApiTokenMiddleware;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */
/** @var \ItsMeStevieG\PHPBasePlate\Core\App $app */

$currentApp = $app;

$router->group(['prefix' => '/api'], function ($router) use ($currentApp) {
    // Health check
    $router->get('/health', function (Request $request) use ($currentApp): JsonResponse {
        $registry = $currentApp->getContainer()->get(ContentTypeRegistry::class);

        return JsonResponse::success([
            'status' => 'ok',
            'php' => PHP_VERSION,
            'timestamp' => date('c'),
            'content_types' => array_keys($registry->all()),
        ], 'PHPBasePlate V3 is running.');
    }, 'api.health');

    // Content API - public read endpoints
    $router->get('/{type}', [ContentApiController::class, 'index'], 'api.content.index');
    $router->get('/{type}/{identifier}', [ContentApiController::class, 'show'], 'api.content.show');

    // Content API - authenticated write endpoints
    $router->post('/{type}', [ContentApiController::class, 'store'], 'api.content.store')
        ->middleware([ApiTokenMiddleware::class]);
    $router->put('/{type}/{id}', [ContentApiController::class, 'update'], 'api.content.update')
        ->middleware([ApiTokenMiddleware::class]);
    $router->patch('/{type}/{id}', [ContentApiController::class, 'update'], 'api.content.update.patch')
        ->middleware([ApiTokenMiddleware::class]);
    $router->delete('/{type}/{id}', [ContentApiController::class, 'destroy'], 'api.content.destroy')
        ->middleware([ApiTokenMiddleware::class]);
    $router->post('/{type}/{id}/publish', [ContentApiController::class, 'publish'], 'api.content.publish')
        ->middleware([ApiTokenMiddleware::class]);
    $router->post('/{type}/{id}/unpublish', [ContentApiController::class, 'unpublish'], 'api.content.unpublish')
        ->middleware([ApiTokenMiddleware::class]);
});
