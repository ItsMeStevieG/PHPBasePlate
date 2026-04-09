<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Content;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use PHPUnit\Framework\TestCase;

class FieldTypeRegistryTest extends TestCase
{
    private FieldTypeRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = FieldTypeRegistry::createDefault();
    }

    public function testAllDefaultTypesRegistered(): void
    {
        $expected = [
            'text', 'textarea', 'richtext', 'slug', 'number', 'boolean',
            'date', 'datetime', 'select', 'image', 'file', 'relation',
            'repeater', 'json',
        ];

        foreach ($expected as $type) {
            $this->assertTrue($this->registry->has($type), "Missing field type: {$type}");
        }
    }

    public function testGetReturnsCorrectType(): void
    {
        $text = $this->registry->get('text');
        $this->assertSame('text', $text->getType());

        $slug = $this->registry->get('slug');
        $this->assertSame('slug', $slug->getType());
    }

    public function testGetThrowsForUnknown(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->registry->get('nonexistent');
    }

    public function testAllReturnsAllTypes(): void
    {
        $all = $this->registry->all();

        $this->assertCount(14, $all);
        $this->assertArrayHasKey('text', $all);
        $this->assertArrayHasKey('richtext', $all);
    }
}
