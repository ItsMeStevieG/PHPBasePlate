<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Support;

class Str
{
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}\s]/u', '', $value);
        $value = preg_replace('/[\s]+/', $separator, trim($value));

        return $value;
    }

    public static function truncate(string $value, int $length = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $length) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $length)) . $end;
    }

    public static function contains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    public static function camel(string $value): string
    {
        return lcfirst(self::studly($value));
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        $value = preg_replace('/([a-z])([A-Z])/', '$1' . $delimiter . '$2', $value);

        return mb_strtolower($value);
    }
}
