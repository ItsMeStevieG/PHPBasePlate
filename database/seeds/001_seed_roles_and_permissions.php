<?php

declare(strict_types=1);

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

return function (Connection $db): void {
    // Seed roles
    $roles = [
        ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access'],
        ['name' => 'Content Admin', 'slug' => 'content-admin', 'description' => 'Manage content, publish, menus, media'],
        ['name' => 'Editor', 'slug' => 'editor', 'description' => 'Create and edit draft content'],
    ];

    foreach ($roles as $role) {
        $existing = $db->fetchOne('SELECT id FROM roles WHERE slug = ?', [$role['slug']]);
        if ($existing === null) {
            $db->execute(
                'INSERT INTO roles (name, slug, description) VALUES (?, ?, ?)',
                [$role['name'], $role['slug'], $role['description']],
            );
        }
    }

    // Seed permissions
    $permissions = [
        // Content
        ['name' => 'View Content', 'slug' => 'content.view', 'group_name' => 'content'],
        ['name' => 'Create Content', 'slug' => 'content.create', 'group_name' => 'content'],
        ['name' => 'Edit Content', 'slug' => 'content.edit', 'group_name' => 'content'],
        ['name' => 'Delete Content', 'slug' => 'content.delete', 'group_name' => 'content'],
        ['name' => 'Publish Content', 'slug' => 'content.publish', 'group_name' => 'content'],
        // Media
        ['name' => 'Upload Media', 'slug' => 'media.upload', 'group_name' => 'media'],
        ['name' => 'Delete Media', 'slug' => 'media.delete', 'group_name' => 'media'],
        // Menus
        ['name' => 'Manage Menus', 'slug' => 'menus.manage', 'group_name' => 'menus'],
        // Settings
        ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group_name' => 'settings'],
        // Users
        ['name' => 'Manage Users', 'slug' => 'users.manage', 'group_name' => 'users'],
        ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'group_name' => 'users'],
        // API
        ['name' => 'Manage API Tokens', 'slug' => 'api.tokens.manage', 'group_name' => 'api'],
    ];

    foreach ($permissions as $perm) {
        $existing = $db->fetchOne('SELECT id FROM permissions WHERE slug = ?', [$perm['slug']]);
        if ($existing === null) {
            $db->execute(
                'INSERT INTO permissions (name, slug, group_name) VALUES (?, ?, ?)',
                [$perm['name'], $perm['slug'], $perm['group_name']],
            );
        }
    }

    // Assign all permissions to content-admin role
    $contentAdmin = $db->fetchOne("SELECT id FROM roles WHERE slug = 'content-admin'");
    if ($contentAdmin) {
        $contentPerms = $db->fetchAll(
            "SELECT id FROM permissions WHERE group_name IN ('content', 'media', 'menus')"
        );
        foreach ($contentPerms as $perm) {
            $db->execute(
                'INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (?, ?)',
                [$perm['id'], $contentAdmin['id']],
            );
        }
    }

    // Assign content view/create/edit to editor role
    $editor = $db->fetchOne("SELECT id FROM roles WHERE slug = 'editor'");
    if ($editor) {
        $editorPerms = $db->fetchAll(
            "SELECT id FROM permissions WHERE slug IN ('content.view', 'content.create', 'content.edit', 'media.upload')"
        );
        foreach ($editorPerms as $perm) {
            $db->execute(
                'INSERT IGNORE INTO permission_role (permission_id, role_id) VALUES (?, ?)',
                [$perm['id'], $editor['id']],
            );
        }
    }
};
