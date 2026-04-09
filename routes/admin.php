<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */

$router->group(['prefix' => '/admin'], function ($router) {
    $router->get('', function (Request $request): Response {
        return new Response('<h1>Admin Dashboard</h1><p>Coming in Phase 2+</p>');
    }, 'admin.dashboard');
});
