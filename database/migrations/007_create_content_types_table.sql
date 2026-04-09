CREATE TABLE IF NOT EXISTS content_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    machine_name VARCHAR(191) NOT NULL UNIQUE,
    label VARCHAR(191) NOT NULL,
    mode ENUM('single', 'collection') NOT NULL DEFAULT 'collection',
    schema_path VARCHAR(500) NULL,
    slug_field VARCHAR(191) NULL,
    title_field VARCHAR(191) NULL,
    status_field_enabled TINYINT(1) NOT NULL DEFAULT 1,
    revisioning_enabled TINYINT(1) NOT NULL DEFAULT 0,
    api_public_read TINYINT(1) NOT NULL DEFAULT 1,
    api_auth_write TINYINT(1) NOT NULL DEFAULT 1,
    config_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
