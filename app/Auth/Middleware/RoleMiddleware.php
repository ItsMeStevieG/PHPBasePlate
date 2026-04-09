<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Auth\Services\RbacService;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewareInterface;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

class RoleMiddleware implements MiddlewareInterface
{
    private array $roles = [];

    public function __construct(
        private readonly AuthService $auth,
        private readonly RbacService $rbac,
    ) {
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->auth->user();

        if ($user === null) {
            throw new HttpException(401, 'Unauthorized.');
        }

        if (!empty($this->roles) && !$this->rbac->userHasAnyRole((int) $user['id'], $this->roles)) {
            throw new HttpException(403, 'Forbidden. Required role not found.');
        }

        return $next($request);
    }
}
