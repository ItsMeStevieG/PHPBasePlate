<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Admin\Controllers;

use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class ContentController
{
    private ViewRenderer $view;
    private Session $session;
    private ContentTypeRegistry $typeRegistry;
    private EntryService $entryService;
    private AuthService $auth;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->session = $container->get(Session::class);
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
        $this->entryService = $container->get(EntryService::class);
        $this->auth = $container->get(AuthService::class);
    }

    public function index(Request $request, string $type): Response
    {
        $schema = $this->resolveSchema($type);

        $page = max(1, (int) $request->query('page', 1));
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort', 'created_at');
        $sortDir = $request->query('dir', 'desc');

        $result = $this->entryService->list(
            $type, $page, 20, $status, $search, (string) $sortBy, (string) $sortDir,
        );

        $html = $this->view->render('admin/content/index', [
            'title' => $schema['label'],
            'schema' => $schema,
            'type' => $type,
            'entries' => $result['items'],
            'pagination' => [
                'page' => $result['page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
                'total_pages' => $result['total_pages'],
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sortBy,
                'dir' => $sortDir,
            ],
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
            'flash_success' => $this->session->getFlash('success'),
            'flash_error' => $this->session->getFlash('error'),
        ]);

        return new Response($html);
    }

    public function create(Request $request, string $type): Response
    {
        $schema = $this->resolveSchema($type);

        $html = $this->view->render('admin/content/create', [
            'title' => 'Create ' . $schema['label'],
            'schema' => $schema,
            'type' => $type,
            'fields' => $schema['fields'],
            'old' => $this->session->getFlash('old_input') ?? [],
            'errors' => $this->session->getFlash('validation_errors') ?? [],
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
        ]);

        return new Response($html);
    }

    public function store(Request $request, string $type): Response
    {
        $schema = $this->resolveSchema($type);
        $input = $request->all();
        $userId = $this->auth->id();

        $result = $this->entryService->create($type, $input, $userId);

        if (!$result['success']) {
            $this->session->flash('validation_errors', $result['errors']);
            $this->session->flash('old_input', $input);
            return new RedirectResponse("/admin/content/{$type}/create");
        }

        $this->session->flash('success', $schema['label'] . ' entry created successfully.');

        return new RedirectResponse("/admin/content/{$type}");
    }

    public function edit(Request $request, string $type, string $id): Response
    {
        $schema = $this->resolveSchema($type);
        $entry = $this->entryService->find((int) $id);

        if ($entry === null) {
            throw new HttpException(404, 'Entry not found.');
        }

        $html = $this->view->render('admin/content/edit', [
            'title' => 'Edit ' . $schema['label'],
            'schema' => $schema,
            'type' => $type,
            'entry' => $entry,
            'fields' => $schema['fields'],
            'old' => $this->session->getFlash('old_input') ?? [],
            'errors' => $this->session->getFlash('validation_errors') ?? [],
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
        ]);

        return new Response($html);
    }

    public function update(Request $request, string $type, string $id): Response
    {
        $schema = $this->resolveSchema($type);
        $input = $request->all();
        $userId = $this->auth->id();

        $result = $this->entryService->update((int) $id, $type, $input, $userId);

        if (!$result['success']) {
            $this->session->flash('validation_errors', $result['errors']);
            $this->session->flash('old_input', $input);
            return new RedirectResponse("/admin/content/{$type}/{$id}/edit");
        }

        $this->session->flash('success', $schema['label'] . ' entry updated successfully.');

        return new RedirectResponse("/admin/content/{$type}");
    }

    public function delete(Request $request, string $type, string $id): Response
    {
        $schema = $this->resolveSchema($type);

        $this->entryService->delete((int) $id);
        $this->session->flash('success', $schema['label'] . ' entry deleted.');

        return new RedirectResponse("/admin/content/{$type}");
    }

    public function publish(Request $request, string $type, string $id): Response
    {
        $schema = $this->resolveSchema($type);
        $userId = $this->auth->id();

        $this->entryService->publish((int) $id, (int) $userId);
        $this->session->flash('success', $schema['label'] . ' entry published.');

        return new RedirectResponse("/admin/content/{$type}");
    }

    public function unpublish(Request $request, string $type, string $id): Response
    {
        $schema = $this->resolveSchema($type);
        $userId = $this->auth->id();

        $this->entryService->unpublish((int) $id, (int) $userId);
        $this->session->flash('success', $schema['label'] . ' entry unpublished.');

        return new RedirectResponse("/admin/content/{$type}");
    }

    private function resolveSchema(string $type): array
    {
        $schema = $this->typeRegistry->get($type);
        if ($schema === null) {
            throw new HttpException(404, "Content type '{$type}' not found.");
        }

        return $schema;
    }
}
