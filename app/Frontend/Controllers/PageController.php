<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Frontend\Controllers;

use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class PageController
{
    private ViewRenderer $view;
    private EntryService $entryService;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->entryService = $container->get(EntryService::class);
    }

    public function show(Request $request, string $slug): Response
    {
        $page = $this->entryService->findBySlug('page', $slug);

        if ($page === null || $page['status'] !== 'published') {
            throw new HttpException(404, 'Page not found.');
        }

        $html = $this->view->render('frontend/page', [
            'page' => $page,
            'title' => $page['payload']['seo_title'] ?? $page['title'],
            'meta_description' => $page['payload']['seo_description'] ?? $page['summary'] ?? '',
        ]);

        return new Response($html);
    }
}
