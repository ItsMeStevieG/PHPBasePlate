<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Routing;

use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;

class Router
{
    /** @var Route[] */
    private array $routes = [];
    private array $namedRoutes = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];

    public function get(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('GET', $uri, $handler, $name);
    }

    public function post(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('POST', $uri, $handler, $name);
    }

    public function put(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('PUT', $uri, $handler, $name);
    }

    public function patch(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('PATCH', $uri, $handler, $name);
    }

    public function delete(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('DELETE', $uri, $handler, $name);
    }

    public function any(string $uri, mixed $handler, ?string $name = null): Route
    {
        return $this->addRoute('ANY', $uri, $handler, $name);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $previousPrefix . ($attributes['prefix'] ?? '');
        $this->groupMiddleware = array_merge(
            $previousMiddleware,
            (array) ($attributes['middleware'] ?? []),
        );

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }

    public function resolve(Request $request): ?Route
    {
        $method = $request->method();
        $uri = $request->path();

        // Normalise trailing slash
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return $route;
            }
        }

        return null;
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \RuntimeException("Route [{$name}] not defined.");
        }

        $pattern = $this->namedRoutes[$name]->getPattern();

        foreach ($params as $key => $value) {
            $pattern = str_replace('{' . $key . '}', (string) $value, $pattern);
        }

        return $pattern;
    }

    /** @return Route[] */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function addRoute(string $method, string $uri, mixed $handler, ?string $name): Route
    {
        $fullUri = $this->groupPrefix . $uri;

        // Normalise trailing slash
        if ($fullUri !== '/' && str_ends_with($fullUri, '/')) {
            $fullUri = rtrim($fullUri, '/');
        }

        $route = new Route($method, $fullUri, $handler, $name);

        if (!empty($this->groupMiddleware)) {
            $route->middleware($this->groupMiddleware);
        }

        $this->routes[] = $route;

        if ($name !== null) {
            $this->namedRoutes[$name] = $route;
        }

        return $route;
    }
}
