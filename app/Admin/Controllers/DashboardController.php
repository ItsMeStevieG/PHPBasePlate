<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Admin\Controllers;

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class DashboardController
{
    private ViewRenderer $view;
    private Session $session;
    private ContentTypeRegistry $typeRegistry;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->session = $container->get(Session::class);
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
    }

    public function index(Request $request): Response
    {
        $html = $this->view->render('admin/dashboard', [
            'title' => 'Dashboard',
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
        ]);

        return new Response($html);
    }
}
