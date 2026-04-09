<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Routing;

class Route
{
    private array $middleware = [];
    private array $params = [];

    public function __construct(
        private readonly string $method,
        private readonly string $pattern,
        private readonly mixed $handler,
        private readonly ?string $name = null,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge(
            $this->middleware,
            is_array($middleware) ? $middleware : [$middleware],
        );
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function matches(string $method, string $uri): bool
    {
        if ($this->method !== $method && $this->method !== 'ANY') {
            return false;
        }

        $regex = $this->compilePattern();

        if (preg_match($regex, $uri, $matches)) {
            $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    private function compilePattern(): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $this->pattern);
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]*)', $pattern);

        return '#^' . $pattern . '$#';
    }
}
