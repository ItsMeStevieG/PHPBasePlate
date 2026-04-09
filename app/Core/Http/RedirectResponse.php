<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Http;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $statusCode = 302)
    {
        parent::__construct('', $statusCode, ['Location' => $url]);
    }
}
