<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Media\Controllers;

use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;
use ItsMeStevieG\PHPBasePlate\Media\Services\MediaService;

class MediaController
{
    private ViewRenderer $view;
    private Session $session;
    private MediaService $mediaService;
    private AuthService $auth;
    private ContentTypeRegistry $typeRegistry;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->session = $container->get(Session::class);
        $this->mediaService = $container->get(MediaService::class);
        $this->auth = $container->get(AuthService::class);
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
    }

    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query('page', 1));
        $result = $this->mediaService->list($page, 24);

        $html = $this->view->render('admin/media/index', [
            'title' => 'Media',
            'files' => $result['items'],
            'pagination' => $result,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
            'flash_success' => $this->session->getFlash('success'),
            'flash_error' => $this->session->getFlash('error'),
        ]);

        return new Response($html);
    }

    public function upload(Request $request): Response
    {
        $file = $request->file('file');

        if ($file === null) {
            $this->session->flash('error', 'No file uploaded.');
            return new RedirectResponse('/admin/media');
        }

        $result = $this->mediaService->upload($file, $this->auth->id());

        if (!$result['success']) {
            $this->session->flash('error', $result['error']);
            return new RedirectResponse('/admin/media');
        }

        $this->session->flash('success', 'File uploaded successfully.');

        return new RedirectResponse('/admin/media');
    }

    public function delete(Request $request, string $id): Response
    {
        $this->mediaService->delete((int) $id);
        $this->session->flash('success', 'File deleted.');

        return new RedirectResponse('/admin/media');
    }
}
