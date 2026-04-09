<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewareInterface;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;

class StartSessionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->session->start();

        return $next($request);
    }
}
