CREATE TABLE IF NOT EXISTS content_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_type_id BIGINT UNSIGNED NOT NULL,
    machine_name VARCHAR(191) NOT NULL,
    label VARCHAR(191) NOT NULL,
    field_type VARCHAR(100) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    is_required TINYINT(1) NOT NULL DEFAULT 0,
    is_searchable TINYINT(1) NOT NULL DEFAULT 0,
    is_filterable TINYINT(1) NOT NULL DEFAULT 0,
    is_sortable TINYINT(1) NOT NULL DEFAULT 0,
    config_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (content_type_id) REFERENCES content_types(id) ON DELETE CASCADE,
    UNIQUE KEY uk_type_field (content_type_id, machine_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
