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
use ItsMeStevieG\PHPBasePlate\Settings\Services\MenuService;

class MenuController
{
    private ViewRenderer $view;
    private Session $session;
    private MenuService $menuService;
    private ContentTypeRegistry $typeRegistry;

    public function __construct(Container $container)
    {
        $this->view = $container->get(ViewRenderer::class);
        $this->session = $container->get(Session::class);
        $this->menuService = $container->get(MenuService::class);
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
    }

    public function index(Request $request): Response
    {
        $menus = $this->menuService->allMenus();

        $html = $this->view->render('admin/menus/index', [
            'title' => 'Menus',
            'menus' => $menus,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
            'flash_success' => $this->session->getFlash('success'),
        ]);

        return new Response($html);
    }

    public function edit(Request $request, string $menu): Response
    {
        $data = $this->menuService->getMenu($menu);
        if ($data === null) {
            throw new HttpException(404, 'Menu not found.');
        }

        $flatItems = $this->menuService->getFlatItems((int) $data['id']);

        $html = $this->view->render('admin/menus/edit', [
            'title' => 'Edit Menu: ' . $data['label'],
            'menu' => $data,
            'items' => $flatItems,
            'user' => $request->getAttribute('user'),
            'csrf_token' => $this->session->token(),
            'content_types' => $this->typeRegistry->all(),
            'flash_success' => $this->session->getFlash('success'),
        ]);

        return new Response($html);
    }

    public function update(Request $request, string $menu): Response
    {
        $data = $this->menuService->getMenu($menu);
        if ($data === null) {
            throw new HttpException(404, 'Menu not found.');
        }

        $items = [];
        $labels = $request->post('label');
        $types = $request->post('item_type');
        $urls = $request->post('url');

        if (is_array($labels)) {
            foreach ($labels as $i => $label) {
                if (trim($label) === '') {
                    continue;
                }
                $items[] = [
                    'label' => $label,
                    'item_type' => $types[$i] ?? 'internal',
                    'url' => $urls[$i] ?? null,
                    'sort_order' => $i,
                ];
            }
        }

        $this->menuService->saveItems((int) $data['id'], $items);
        $this->session->flash('success', 'Menu updated.');

        return new RedirectResponse("/admin/menus/{$menu}");
    }
}
