<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Auth\Services;

use ItsMeStevieG\PHPBasePlate\Auth\Repositories\UserRepository;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;

class AuthService
{
    private ?array $user = null;

    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly Session $session,
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->userRepo->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if ($user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function login(array $user): void
    {
        $this->session->regenerate();
        $this->session->set('user_id', (int) $user['id']);
        $this->user = $user;
        $this->userRepo->updateLastLogin((int) $user['id']);
    }

    public function logout(): void
    {
        $this->user = null;
        $this->session->destroy();
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?array
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $userId = $this->session->get('user_id');

        if ($userId === null) {
            return null;
        }

        $this->user = $this->userRepo->findById((int) $userId);

        if ($this->user !== null && $this->user['status'] !== 'active') {
            $this->logout();
            return null;
        }

        return $this->user;
    }

    public function id(): ?int
    {
        $user = $this->user();

        return $user !== null ? (int) $user['id'] : null;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
