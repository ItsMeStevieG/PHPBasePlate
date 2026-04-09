<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Config\Env;

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        global $app;
        return $app->getConfig()->get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        global $app;
        return $app->getBasePath() . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        global $app;
        $renderer = $app->getContainer()->get(
            \ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer::class
        );
        return $renderer->render($template, $data);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        global $app;
        return $app->getRouter()->url($name, $params);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}
