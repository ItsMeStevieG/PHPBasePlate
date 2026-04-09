CREATE TABLE IF NOT EXISTS settings_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    settings_group_id BIGINT UNSIGNED NOT NULL,
    machine_name VARCHAR(191) NOT NULL,
    label VARCHAR(191) NOT NULL,
    field_type VARCHAR(100) NOT NULL DEFAULT 'text',
    value_json JSON NULL,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    config_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (settings_group_id) REFERENCES settings_groups(id) ON DELETE CASCADE,
    UNIQUE KEY uk_group_item (settings_group_id, machine_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
