<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Core;

use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testBindAndGet(): void
    {
        $this->container->bind('greeting', fn() => 'hello');

        $this->assertSame('hello', $this->container->get('greeting'));
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $this->container->singleton('counter', fn() => new \stdClass());

        $a = $this->container->get('counter');
        $b = $this->container->get('counter');

        $this->assertSame($a, $b);
    }

    public function testInstanceRegistration(): void
    {
        $obj = new \stdClass();
        $obj->value = 42;

        $this->container->instance('myobj', $obj);

        $this->assertSame($obj, $this->container->get('myobj'));
        $this->assertSame(42, $this->container->get('myobj')->value);
    }

    public function testHasReturnsTrueForBound(): void
    {
        $this->container->bind('exists', fn() => true);

        $this->assertTrue($this->container->has('exists'));
        $this->assertFalse($this->container->has('missing'));
    }

    public function testGetThrowsForUnbound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No binding found for [missing]');

        $this->container->get('missing');
    }

    public function testContainerPassedToClosure(): void
    {
        $this->container->bind('dep', fn() => 'dependency');
        $this->container->bind('service', fn(Container $c) => 'uses-' . $c->get('dep'));

        $this->assertSame('uses-dependency', $this->container->get('service'));
    }
}
