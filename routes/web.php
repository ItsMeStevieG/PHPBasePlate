<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Frontend\Controllers\HomeController;
use ItsMeStevieG\PHPBasePlate\Frontend\Controllers\NewsController;
use ItsMeStevieG\PHPBasePlate\Frontend\Controllers\PageController;

/** @var \ItsMeStevieG\PHPBasePlate\Core\Routing\Router $router */

// Home
$router->get('/', [HomeController::class, 'index'], 'home');

// News
$router->get('/news', [NewsController::class, 'index'], 'news.index');
$router->get('/news/{slug}', [NewsController::class, 'show'], 'news.show');

// Pages (catch-all for slugs - must be last)
$router->get('/{slug}', [PageController::class, 'show'], 'page.show');
