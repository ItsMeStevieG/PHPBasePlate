<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Http\Middleware;

use Closure;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

class MiddlewarePipeline
{
    /** @var list<string|MiddlewareInterface> */
    private array $middleware = [];

    public function __construct(private readonly Container $container)
    {
    }

    public function pipe(string|MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function through(array $middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }

    public function run(Request $request, Closure $destination): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function (Closure $next, string|MiddlewareInterface $middleware) {
                return function (Request $request) use ($next, $middleware): Response {
                    $instance = is_string($middleware)
                        ? $this->container->get($middleware)
                        : $middleware;

                    return $instance->handle($request, $next);
                };
            },
            $destination,
        );

        return $pipeline($request);
    }
}
