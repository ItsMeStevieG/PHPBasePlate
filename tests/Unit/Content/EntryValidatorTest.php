<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Content;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Validators\EntryValidator;
use PHPUnit\Framework\TestCase;

class EntryValidatorTest extends TestCase
{
    private EntryValidator $validator;
    private ContentTypeRegistry $typeRegistry;

    protected function setUp(): void
    {
        $fieldTypes = FieldTypeRegistry::createDefault();
        $this->typeRegistry = new ContentTypeRegistry();
        $this->validator = new EntryValidator($this->typeRegistry, $fieldTypes);

        $this->typeRegistry->register([
            'name' => 'page',
            'label' => 'Pages',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'is_required' => true, 'config' => ['max_length' => 100]],
                ['name' => 'slug', 'label' => 'Slug', 'type' => 'slug', 'is_required' => true, 'config' => []],
                ['name' => 'body', 'label' => 'Body', 'type' => 'richtext', 'is_required' => false, 'config' => []],
                ['name' => 'featured', 'label' => 'Featured', 'type' => 'boolean', 'is_required' => false, 'config' => []],
            ],
        ]);
    }

    public function testValidPayload(): void
    {
        $payload = ['title' => 'My Page', 'slug' => 'my-page', 'body' => '<p>Hello</p>'];

        $this->assertTrue($this->validator->validate('page', $payload));
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testMissingRequiredField(): void
    {
        $payload = ['slug' => 'no-title'];

        $this->assertFalse($this->validator->validate('page', $payload));
        $this->assertArrayHasKey('title', $this->validator->getErrors());
    }

    public function testMaxLengthExceeded(): void
    {
        $payload = ['title' => str_repeat('a', 101), 'slug' => 'long'];

        $this->assertFalse($this->validator->validate('page', $payload));
        $this->assertArrayHasKey('title', $this->validator->getErrors());
    }

    public function testOptionalFieldCanBeEmpty(): void
    {
        $payload = ['title' => 'Test', 'slug' => 'test'];

        $this->assertTrue($this->validator->validate('page', $payload));
    }

    public function testUpdateModeSkipsRequired(): void
    {
        $payload = ['body' => '<p>Updated body only</p>'];

        $this->assertTrue($this->validator->validate('page', $payload, isUpdate: true));
    }
}
