<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Core;

use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testMethodDetection(): void
    {
        $request = new Request(server: ['REQUEST_METHOD' => 'GET']);
        $this->assertSame('GET', $request->method());

        $request = new Request(server: ['REQUEST_METHOD' => 'POST']);
        $this->assertSame('POST', $request->method());
    }

    public function testMethodOverride(): void
    {
        $request = new Request(
            post: ['_method' => 'PUT'],
            server: ['REQUEST_METHOD' => 'POST'],
        );

        $this->assertSame('PUT', $request->method());
    }

    public function testUri(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/hello?foo=bar']);
        $this->assertSame('/hello', $request->uri());
    }

    public function testPath(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/test/path']);
        $this->assertSame('/test/path', $request->path());
    }

    public function testQueryParam(): void
    {
        $request = new Request(query: ['page' => '2', 'search' => 'hello']);

        $this->assertSame('2', $request->query('page'));
        $this->assertSame('hello', $request->query('search'));
        $this->assertNull($request->query('missing'));
        $this->assertSame('default', $request->query('missing', 'default'));
    }

    public function testPostParam(): void
    {
        $request = new Request(post: ['name' => 'John']);

        $this->assertSame('John', $request->post('name'));
        $this->assertNull($request->post('missing'));
    }

    public function testInput(): void
    {
        $request = new Request(query: ['a' => '1'], post: ['b' => '2']);

        $this->assertSame('1', $request->input('a'));
        $this->assertSame('2', $request->input('b'));
    }

    public function testAttributes(): void
    {
        $request = new Request();
        $request->setAttribute('user', ['id' => 1]);

        $this->assertSame(['id' => 1], $request->getAttribute('user'));
        $this->assertNull($request->getAttribute('missing'));
    }

    public function testHeaders(): void
    {
        $request = new Request(server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CUSTOM' => 'value',
        ]);

        $this->assertSame('application/json', $request->header('accept'));
        $this->assertSame('value', $request->header('x-custom'));
    }

    public function testWantsJson(): void
    {
        $request = new Request(server: ['HTTP_ACCEPT' => 'application/json']);
        $this->assertTrue($request->wantsJson());

        $request = new Request(server: ['HTTP_ACCEPT' => 'text/html']);
        $this->assertFalse($request->wantsJson());
    }

    public function testIp(): void
    {
        $request = new Request(server: ['REMOTE_ADDR' => '192.168.1.1']);
        $this->assertSame('192.168.1.1', $request->ip());
    }
}
