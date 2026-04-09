<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Http;

class JsonResponse extends Response
{
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';

        parent::__construct(
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $statusCode,
            $headers,
        );
    }

    public static function success(
        mixed $data = null,
        string $message = 'Success.',
        array $meta = [],
        int $statusCode = 200,
    ): self {
        return new self([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => [],
        ], $statusCode);
    }

    public static function error(
        string $message = 'Error.',
        array|object $errors = [],
        int $statusCode = 400,
    ): self {
        return new self([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => (object) [],
            'errors' => $errors,
        ], $statusCode);
    }
}
