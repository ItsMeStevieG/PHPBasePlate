<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Core;

use ItsMeStevieG\PHPBasePlate\Core\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private string $tempDir;
    private Config $config;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/phpbaseplate_test_config_' . uniqid();
        mkdir($this->tempDir);

        file_put_contents($this->tempDir . '/app.php', '<?php return [
            "name" => "TestApp",
            "debug" => true,
            "nested" => ["key" => "value", "deep" => ["leaf" => 42]],
        ];');

        file_put_contents($this->tempDir . '/database.php', '<?php return [
            "host" => "localhost",
            "port" => 3306,
        ];');

        $this->config = new Config($this->tempDir);
        $this->config->load();
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . '/*.php'));
        rmdir($this->tempDir);
    }

    public function testGetTopLevelKey(): void
    {
        $this->assertSame('TestApp', $this->config->get('app.name'));
        $this->assertTrue($this->config->get('app.debug'));
    }

    public function testGetNestedKey(): void
    {
        $this->assertSame('value', $this->config->get('app.nested.key'));
        $this->assertSame(42, $this->config->get('app.nested.deep.leaf'));
    }

    public function testGetReturnsDefaultForMissing(): void
    {
        $this->assertNull($this->config->get('app.nonexistent'));
        $this->assertSame('fallback', $this->config->get('app.nonexistent', 'fallback'));
    }

    public function testGetAcrossFiles(): void
    {
        $this->assertSame('localhost', $this->config->get('database.host'));
        $this->assertSame(3306, $this->config->get('database.port'));
    }

    public function testSetValue(): void
    {
        $this->config->set('app.name', 'Changed');
        $this->assertSame('Changed', $this->config->get('app.name'));
    }

    public function testSetCreatesNestedPath(): void
    {
        $this->config->set('new.deep.key', 'created');
        $this->assertSame('created', $this->config->get('new.deep.key'));
    }

    public function testAll(): void
    {
        $all = $this->config->all();

        $this->assertArrayHasKey('app', $all);
        $this->assertArrayHasKey('database', $all);
    }
}
