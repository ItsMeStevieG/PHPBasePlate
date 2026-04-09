<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Admin\Controllers\ContentController;
use ItsMeStevieG\PHPBasePlate\Admin\Controllers\DashboardController;
use ItsMeStevieG\PHPBasePlate\Auth\Controllers\LoginController;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\AuthMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\CsrfMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\GuestMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\StartSessionMiddleware;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */

// Guest-only routes (login page)
$router->group(['prefix' => '/admin'], function ($router) {
    $router->get('/login', [LoginController::class, 'showLogin'], 'admin.login')
        ->middleware([StartSessionMiddleware::class, GuestMiddleware::class]);

    $router->post('/login', [LoginController::class, 'login'], 'admin.login.submit')
        ->middleware([StartSessionMiddleware::class, CsrfMiddleware::class, GuestMiddleware::class]);
});

// Authenticated admin routes
$router->group(['prefix' => '/admin', 'middleware' => [StartSessionMiddleware::class, AuthMiddleware::class]], function ($router) {
    // Dashboard
    $router->get('', [DashboardController::class, 'index'], 'admin.dashboard');

    // Logout
    $router->post('/logout', [LoginController::class, 'logout'], 'admin.logout')
        ->middleware([CsrfMiddleware::class]);

    // Content CRUD
    $router->get('/content/{type}', [ContentController::class, 'index'], 'admin.content.index');
    $router->get('/content/{type}/create', [ContentController::class, 'create'], 'admin.content.create');
    $router->post('/content/{type}', [ContentController::class, 'store'], 'admin.content.store')
        ->middleware([CsrfMiddleware::class]);
    $router->get('/content/{type}/{id}/edit', [ContentController::class, 'edit'], 'admin.content.edit');
    $router->post('/content/{type}/{id}', [ContentController::class, 'update'], 'admin.content.update')
        ->middleware([CsrfMiddleware::class]);
    $router->post('/content/{type}/{id}/delete', [ContentController::class, 'delete'], 'admin.content.delete')
        ->middleware([CsrfMiddleware::class]);
    $router->post('/content/{type}/{id}/publish', [ContentController::class, 'publish'], 'admin.content.publish')
        ->middleware([CsrfMiddleware::class]);
    $router->post('/content/{type}/{id}/unpublish', [ContentController::class, 'unpublish'], 'admin.content.unpublish')
        ->middleware([CsrfMiddleware::class]);
});
