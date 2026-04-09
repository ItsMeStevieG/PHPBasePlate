<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Tests\Unit\Content;

use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\SlugField;
use ItsMeStevieG\PHPBasePlate\Core\Support\Str;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function testBasicSlug(): void
    {
        $this->assertSame('hello-world', Str::slug('Hello World'));
    }

    public function testSlugWithSpecialChars(): void
    {
        $this->assertSame('my-page-title', Str::slug('My Page & Title!'));
    }

    public function testSlugWithExtraSpaces(): void
    {
        $this->assertSame('spaced-out', Str::slug('  Spaced   Out  '));
    }

    public function testSlugFromUppercase(): void
    {
        $this->assertSame('uppercase', Str::slug('UPPERCASE'));
    }

    public function testSlugWithNumbers(): void
    {
        $this->assertSame('page-42', Str::slug('Page 42'));
    }

    public function testSlugFieldNormalises(): void
    {
        $field = new SlugField();

        $this->assertSame('my-title', $field->normalise('My Title', []));
        $this->assertSame('already-slug', $field->normalise('already-slug', []));
    }

    public function testStrTruncate(): void
    {
        $this->assertSame('Hello...', Str::truncate('Hello World', 5));
        $this->assertSame('Short', Str::truncate('Short', 10));
    }

    public function testStrStudly(): void
    {
        $this->assertSame('HelloWorld', Str::studly('hello-world'));
        $this->assertSame('FooBar', Str::studly('foo_bar'));
    }

    public function testStrCamel(): void
    {
        $this->assertSame('helloWorld', Str::camel('hello-world'));
    }

    public function testStrSnake(): void
    {
        $this->assertSame('hello_world', Str::snake('HelloWorld'));
    }
}
