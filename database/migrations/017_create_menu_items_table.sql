CREATE TABLE IF NOT EXISTS menu_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    label VARCHAR(191) NOT NULL,
    item_type ENUM('internal', 'external', 'content', 'route') NOT NULL DEFAULT 'internal',
    url VARCHAR(500) NULL,
    route_name VARCHAR(191) NULL,
    content_entry_id BIGINT UNSIGNED NULL,
    target VARCHAR(20) NULL,
    css_class VARCHAR(191) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    meta_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE SET NULL,
    INDEX idx_menu_sort (menu_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
