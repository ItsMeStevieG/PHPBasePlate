<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewareInterface;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->guest()) {
            return new RedirectResponse('/admin/login');
        }

        $request->setAttribute('user', $this->auth->user());

        return $next($request);
    }
}
