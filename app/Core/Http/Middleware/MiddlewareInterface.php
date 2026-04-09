<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Http\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response;
}
