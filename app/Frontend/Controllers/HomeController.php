<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Frontend\Controllers;

use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class HomeController
{
    private ViewRenderer $view;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
    }

    public function index(Request $request): Response
    {
        $html = $this->view->render('frontend/home', [
            'title' => config('app.name', 'PHPBasePlate'),
        ]);

        return new Response($html);
    }
}
