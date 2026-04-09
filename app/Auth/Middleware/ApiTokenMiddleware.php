<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Auth\Services\ApiTokenService;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewareInterface;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

class ApiTokenMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ApiTokenService $tokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('authorization', '');

        if (!str_starts_with((string) $header, 'Bearer ')) {
            throw new HttpException(401, 'Missing or invalid Authorization header.');
        }

        $plainToken = substr((string) $header, 7);
        $token = $this->tokenService->validate($plainToken);

        if ($token === null) {
            throw new HttpException(401, 'Invalid or expired API token.');
        }

        $request->setAttribute('api_token', $token);
        $request->setAttribute('api_user_id', $token['user_id']);

        return $next($request);
    }
}
