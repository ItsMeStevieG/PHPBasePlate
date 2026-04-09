<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Content;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Schema\SchemaValidator;
use PHPUnit\Framework\TestCase;

class SchemaValidatorTest extends TestCase
{
    private SchemaValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SchemaValidator(FieldTypeRegistry::createDefault());
    }

    public function testValidSchema(): void
    {
        $schema = [
            'name' => 'page',
            'label' => 'Pages',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['name' => 'body', 'label' => 'Body', 'type' => 'richtext'],
            ],
        ];

        $this->assertTrue($this->validator->validate($schema, 'page.json'));
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testMissingNameFails(): void
    {
        $schema = ['label' => 'Pages', 'fields' => [['name' => 'x', 'label' => 'X', 'type' => 'text']]];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
        $this->assertNotEmpty($this->validator->getErrors());
    }

    public function testMissingFieldsFails(): void
    {
        $schema = ['name' => 'page', 'label' => 'Pages'];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testEmptyFieldsFails(): void
    {
        $schema = ['name' => 'page', 'label' => 'Pages', 'fields' => []];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testInvalidNameFormat(): void
    {
        $schema = [
            'name' => 'My Page',
            'label' => 'Pages',
            'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text']],
        ];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testInvalidMode(): void
    {
        $schema = [
            'name' => 'page',
            'label' => 'Pages',
            'mode' => 'invalid',
            'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'text']],
        ];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testUnknownFieldType(): void
    {
        $schema = [
            'name' => 'page',
            'label' => 'Pages',
            'fields' => [['name' => 'title', 'label' => 'Title', 'type' => 'unknown_type']],
        ];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testDuplicateFieldNames(): void
    {
        $schema = [
            'name' => 'page',
            'label' => 'Pages',
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['name' => 'title', 'label' => 'Title Again', 'type' => 'text'],
            ],
        ];

        $this->assertFalse($this->validator->validate($schema, 'bad.json'));
    }

    public function testValidModes(): void
    {
        $base = ['name' => 'page', 'label' => 'P', 'fields' => [['name' => 'x', 'label' => 'X', 'type' => 'text']]];

        $single = array_merge($base, ['mode' => 'single']);
        $collection = array_merge($base, ['mode' => 'collection']);

        $this->assertTrue($this->validator->validate($single, 's.json'));
        $this->assertTrue($this->validator->validate($collection, 'c.json'));
    }
}
