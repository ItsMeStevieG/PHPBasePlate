<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Auth\Controllers\LoginController;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\AuthMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\CsrfMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\GuestMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\StartSessionMiddleware;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */
/** @var \ItsMeStevieG\PHPBasePlate\Core\App $app */

// Guest-only routes (login page)
$router->group(['prefix' => '/admin'], function ($router) {
    $router->get('/login', [LoginController::class, 'showLogin'], 'admin.login')
        ->middleware([StartSessionMiddleware::class, GuestMiddleware::class]);

    $router->post('/login', [LoginController::class, 'login'], 'admin.login.submit')
        ->middleware([StartSessionMiddleware::class, CsrfMiddleware::class, GuestMiddleware::class]);
});

// Authenticated admin routes
$currentApp = $app;
$router->group(['prefix' => '/admin', 'middleware' => [StartSessionMiddleware::class, AuthMiddleware::class]], function ($router) use ($currentApp) {
    $router->get('', function (Request $request) use ($currentApp): Response {
        $session = $currentApp->getContainer()->get(Session::class);
        $html = view('admin/dashboard', [
            'title' => 'Dashboard',
            'user' => $request->getAttribute('user'),
            'csrf_token' => $session->token(),
        ]);
        return new Response($html);
    }, 'admin.dashboard');

    $router->post('/logout', [LoginController::class, 'logout'], 'admin.logout')
        ->middleware([CsrfMiddleware::class]);
});
