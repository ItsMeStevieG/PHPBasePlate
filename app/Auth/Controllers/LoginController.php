<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Controllers;

use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class LoginController
{
    private AuthService $auth;
    private Session $session;
    private ViewRenderer $view;

    public function __construct(Container $container)
    {
        $this->auth = $container->get(AuthService::class);
        $this->session = $container->get(Session::class);
        $this->view = $container->get(ViewRenderer::class);
    }

    public function showLogin(Request $request): Response
    {
        $html = $this->view->render('auth/login', [
            'title' => 'Login',
            'csrf_token' => $this->session->token(),
            'error' => $this->session->getFlash('login_error'),
        ]);

        return new Response($html);
    }

    public function login(Request $request): Response
    {
        $email = trim((string) $request->post('email', ''));
        $password = (string) $request->post('password', '');

        if ($email === '' || $password === '') {
            $this->session->flash('login_error', 'Email and password are required.');
            return new RedirectResponse('/admin/login');
        }

        if (!$this->auth->attempt($email, $password)) {
            $this->session->flash('login_error', 'Invalid email or password.');
            return new RedirectResponse('/admin/login');
        }

        return new RedirectResponse('/admin');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();

        return new RedirectResponse('/admin/login');
    }
}
