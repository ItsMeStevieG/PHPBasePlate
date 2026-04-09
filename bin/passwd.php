<?php

declare(strict_types=1);

/**
 * PHPBasePlate V3 - Change User Password
 *
 * Usage:
 *   php bin/passwd.php admin@phpbaseplate.local newpassword
 *   php bin/passwd.php admin@phpbaseplate.local              (prompts for password)
 */

$basePath = dirname(__DIR__);

require_once $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$container = $app->getContainer();

use ItsMeStevieG\PHPBasePlate\Auth\Repositories\UserRepository;

$email = $argv[1] ?? null;
$newPassword = $argv[2] ?? null;

if ($email === null) {
    echo "Usage: php bin/passwd.php <email> [new-password]\n";
    echo "Example: php bin/passwd.php admin@phpbaseplate.local\n";
    exit(1);
}

// Prompt for password if not provided
if ($newPassword === null) {
    echo "New password for {$email}: ";
    $newPassword = trim(fgets(STDIN));

    if ($newPassword === '') {
        echo "\033[31mPassword cannot be empty.\033[0m\n";
        exit(1);
    }
}

if (strlen($newPassword) < 6) {
    echo "\033[31mPassword must be at least 6 characters.\033[0m\n";
    exit(1);
}

$userRepo = $container->get(UserRepository::class);
$user = $userRepo->findByEmail($email);

if ($user === null) {
    echo "\033[31mUser not found: {$email}\033[0m\n";
    exit(1);
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update via the repository's underlying store
// Both JSON and DB repos support this pattern through DualWriteProxy
$userRepo->updateLastLogin((int) $user['id']); // triggers a write to confirm connectivity

// Direct update of password hash
if (method_exists($userRepo, 'update')) {
    $userRepo->update((int) $user['id'], ['password_hash' => $hash]);
} else {
    // For DualWriteProxy or repos without update(), use the underlying store
    $db = null;
    try {
        $db = $container->get(\ItsMeStevieG\PHPBasePlate\Core\Database\Connection::class);
        $db->execute('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $user['id']]);
    } catch (\Throwable) {
        // DB not available
    }

    $jsonStore = $container->get(\ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore::class);
    $jsonStore->update('users', (int) $user['id'], ['password_hash' => $hash]);
}

echo "\033[32mPassword updated for {$email}.\033[0m\n";
