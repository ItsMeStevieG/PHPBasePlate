<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */

$router->get('/', function (Request $request): Response {
    return new Response(view('frontend/home', [
        'title' => config('app.name', 'PHPBasePlate'),
    ]));
}, 'home');
