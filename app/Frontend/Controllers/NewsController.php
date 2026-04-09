<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Frontend\Controllers;

use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class NewsController
{
    private ViewRenderer $view;
    private EntryService $entryService;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->entryService = $container->get(EntryService::class);
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));

        $result = $this->entryService->list(
            'news', $page, 12, 'published', null, 'published_at', 'desc',
        );

        $html = $this->view->render('frontend/news/index', [
            'title' => 'News',
            'entries' => $result['items'],
            'pagination' => [
                'page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total'],
            ],
        ]);

        return new Response($html);
    }

    public function show(Request $request, string $slug): Response
    {
        $entry = $this->entryService->findBySlug('news', $slug);

        if ($entry === null || $entry['status'] !== 'published') {
            throw new HttpException(404, 'Article not found.');
        }

        $html = $this->view->render('frontend/news/show', [
            'entry' => $entry,
            'title' => $entry['title'],
        ]);

        return new Response($html);
    }
}
