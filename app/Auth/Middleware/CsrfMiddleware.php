<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewareInterface;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    private const PROTECTED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(private readonly Session $session)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), self::PROTECTED_METHODS, true)) {
            $token = $request->post('_token') ?? $request->header('X-CSRF-Token');

            if ($token === null || !hash_equals($this->session->token(), (string) $token)) {
                throw new HttpException(419, 'CSRF token mismatch.');
            }
        }

        return $next($request);
    }
}
