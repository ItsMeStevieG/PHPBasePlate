<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Services;

use ItsMeStevieG\PHPBasePlate\Auth\Repositories\ApiTokenRepository;

class ApiTokenService
{
    public function __construct(private readonly ApiTokenRepository $tokenRepo)
    {
    }

    public function generate(string $name, ?int $userId = null, array $scopes = []): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $hash = hash('sha256', $plainToken);

        $this->tokenRepo->create($name, $hash, $userId, $scopes);

        return $plainToken;
    }

    public function validate(string $plainToken): ?array
    {
        $hash = hash('sha256', $plainToken);
        $token = $this->tokenRepo->findByTokenHash($hash);

        if ($token === null) {
            return null;
        }

        if ($this->tokenRepo->isExpired($token)) {
            return null;
        }

        $this->tokenRepo->updateLastUsed((int) $token['id']);

        return $token;
    }

    public function tokenHasScope(array $token, string $scope): bool
    {
        $scopes = json_decode($token['scopes_json'] ?? '[]', true);

        if (empty($scopes) || in_array('*', $scopes, true)) {
            return true;
        }

        return in_array($scope, $scopes, true);
    }
}
