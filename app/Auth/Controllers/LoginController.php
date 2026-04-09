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
        // Rate limiting: max 5 attempts per 15 minutes
        $attempts = (int) $this->session->get('login_attempts', 0);
        $lockUntil = (int) $this->session->get('login_lock_until', 0);

        if ($lockUntil > time()) {
            $remaining = (int) ceil(($lockUntil - time()) / 60);
            $this->session->flash('login_error', "Too many login attempts. Try again in {$remaining} minute(s).");
            return new RedirectResponse('/admin/login');
        }

        $email = trim((string) $request->post('email', ''));
        $password = (string) $request->post('password', '');

        if ($email === '' || $password === '') {
            $this->session->flash('login_error', 'Email and password are required.');
            return new RedirectResponse('/admin/login');
        }

        if (!$this->auth->attempt($email, $password)) {
            $attempts++;
            $this->session->set('login_attempts', $attempts);

            if ($attempts >= 5) {
                $this->session->set('login_lock_until', time() + 900); // 15 minutes
                $this->session->set('login_attempts', 0);
                $this->session->flash('login_error', 'Too many failed attempts. Account locked for 15 minutes.');
            } else {
                $this->session->flash('login_error', 'Invalid email or password.');
            }

            return new RedirectResponse('/admin/login');
        }

        // Reset on success
        $this->session->remove('login_attempts');
        $this->session->remove('login_lock_until');

        return new RedirectResponse('/admin');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();

        return new RedirectResponse('/admin/login');
    }
}
