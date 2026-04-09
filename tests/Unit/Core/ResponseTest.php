<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Core;

use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\RedirectResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testBasicResponse(): void
    {
        $response = new Response('Hello', 200);

        $this->assertSame('Hello', $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testSetStatusCode(): void
    {
        $response = new Response();
        $response->setStatusCode(404);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testSetHeader(): void
    {
        $response = new Response();
        $response->setHeader('X-Custom', 'value');

        $this->assertSame(['X-Custom' => 'value'], $response->getHeaders());
    }

    public function testJsonResponseSuccess(): void
    {
        $response = JsonResponse::success(['key' => 'val'], 'OK');
        $body = json_decode($response->getBody(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($body['success']);
        $this->assertSame('OK', $body['message']);
        $this->assertSame(['key' => 'val'], $body['data']);
    }

    public function testJsonResponseError(): void
    {
        $response = JsonResponse::error('Failed', ['field' => ['Error msg']], 422);
        $body = json_decode($response->getBody(), true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertFalse($body['success']);
        $this->assertSame('Failed', $body['message']);
        $this->assertNull($body['data']);
    }

    public function testJsonResponseContentType(): void
    {
        $response = new JsonResponse(['test' => true]);

        $this->assertSame('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testRedirectResponse(): void
    {
        $response = new RedirectResponse('/admin');

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/admin', $response->getHeaders()['Location']);
    }
}
