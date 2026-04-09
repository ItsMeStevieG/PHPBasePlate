<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Settings\Controllers;

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;
use ItsMeStevieG\PHPBasePlate\Settings\Services\SettingsService;

class SettingsController
{
    private ViewRenderer $view;
    private Session $session;
    private SettingsService $settingsService;
    private ContentTypeRegistry $typeRegistry;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->session = $container->get(Session::class);
        $this->settingsService = $container->get(SettingsService::class);
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
    }

    public function index(Request $request): Response
    {
        $groups = $this->settingsService->allGroups();

        $html = $this->view->render('admin/settings/index', [
            'title' => 'Settings',
            'groups' => $groups,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
            'flash_success' => $this->session->getFlash('success'),
        ]);

        return new Response($html);
    }

    public function edit(Request $request, string $group): Response
    {
        $data = $this->settingsService->getGroupWithItems($group);
        if ($data === null) {
            throw new HttpException(404, 'Settings group not found.');
        }

        $html = $this->view->render('admin/settings/edit', [
            'title' => 'Settings: ' . $data['label'],
            'group' => $data,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
        ]);

        return new Response($html);
    }

    public function update(Request $request, string $group): Response
    {
        $input = $request->all();
        unset($input['_token'], $input['_method']);

        $this->settingsService->updateGroup($group, $input);
        $this->session->flash('success', 'Settings updated.');

        return new RedirectResponse("/admin/settings/{$group}");
    }
}
