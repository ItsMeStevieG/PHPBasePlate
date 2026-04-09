<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Core;

use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function testBasicGetRoute(): void
    {
        $this->router->get('/hello', fn() => 'world', 'hello');

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/hello']);
        $route = $this->router->resolve($request);

        $this->assertNotNull($route);
        $this->assertSame('hello', $route->getName());
    }

    public function testRouteWithParameter(): void
    {
        $this->router->get('/users/{id}', fn() => 'user', 'user.show');

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/users/42']);
        $route = $this->router->resolve($request);

        $this->assertNotNull($route);
        $this->assertSame(['id' => '42'], $route->getParams());
    }

    public function testMultipleParameters(): void
    {
        $this->router->get('/content/{type}/{id}/edit', fn() => 'edit');

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/content/page/5/edit']);
        $route = $this->router->resolve($request);

        $this->assertNotNull($route);
        $this->assertSame('page', $route->getParams()['type']);
        $this->assertSame('5', $route->getParams()['id']);
    }

    public function testRouteGroupWithPrefix(): void
    {
        $this->router->group(['prefix' => '/api'], function ($router) {
            $router->get('/health', fn() => 'ok', 'api.health');
            $router->get('/users', fn() => 'users', 'api.users');
        });

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/health']);
        $route = $this->router->resolve($request);

        $this->assertNotNull($route);
        $this->assertSame('api.health', $route->getName());
    }

    public function testNestedGroups(): void
    {
        $this->router->group(['prefix' => '/api'], function ($router) {
            $router->group(['prefix' => '/v1'], function ($router) {
                $router->get('/items', fn() => 'items', 'api.v1.items');
            });
        });

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/api/v1/items']);
        $this->assertNotNull($this->router->resolve($request));
    }

    public function testGroupMiddlewareApplied(): void
    {
        $this->router->group(['prefix' => '/admin', 'middleware' => ['auth']], function ($router) {
            $router->get('/dashboard', fn() => 'dash', 'admin.dash');
        });

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/dashboard']);
        $route = $this->router->resolve($request);

        $this->assertContains('auth', $route->getMiddleware());
    }

    public function testReturnsNullForUnmatched(): void
    {
        $this->router->get('/exists', fn() => 'yes');

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/missing']);
        $this->assertNull($this->router->resolve($request));
    }

    public function testMethodMismatchReturnsNull(): void
    {
        $this->router->get('/only-get', fn() => 'get');

        $request = new Request(server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/only-get']);
        $this->assertNull($this->router->resolve($request));
    }

    public function testNamedRouteUrlGeneration(): void
    {
        $this->router->get('/users/{id}', fn() => '', 'user.show');

        $url = $this->router->url('user.show', ['id' => 99]);
        $this->assertSame('/users/99', $url);
    }

    public function testUrlGenerationThrowsForUnknownRoute(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->router->url('nonexistent');
    }

    public function testPostRoute(): void
    {
        $this->router->post('/submit', fn() => 'ok');

        $request = new Request(server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/submit']);
        $this->assertNotNull($this->router->resolve($request));
    }

    public function testTrailingSlashNormalised(): void
    {
        $this->router->get('/clean', fn() => 'ok');

        $request = new Request(server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/clean/']);
        $this->assertNotNull($this->router->resolve($request));
    }
}
