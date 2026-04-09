<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

return function (Connection $db): void {
    $email = 'admin@phpbaseplate.local';

    $existing = $db->fetchOne('SELECT id FROM users WHERE email = ?', [$email]);
    if ($existing !== null) {
        return;
    }

    $db->execute(
        'INSERT INTO users (name, email, password_hash, status) VALUES (?, ?, ?, ?)',
        [
            'Admin',
            $email,
            password_hash('admin', PASSWORD_DEFAULT),
            'active',
        ],
    );

    $userId = (int) $db->lastInsertId();
    $superAdmin = $db->fetchOne("SELECT id FROM roles WHERE slug = 'super-admin'");

    if ($superAdmin) {
        $db->execute(
            'INSERT IGNORE INTO role_user (user_id, role_id) VALUES (?, ?)',
            [$userId, $superAdmin['id']],
        );
    }
};
