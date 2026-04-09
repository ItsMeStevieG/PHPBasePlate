CREATE TABLE IF NOT EXISTS content_relations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_entry_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(191) NOT NULL,
    target_entry_id BIGINT UNSIGNED NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_entry_id) REFERENCES content_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (target_entry_id) REFERENCES content_entries(id) ON DELETE CASCADE,
    INDEX idx_source_field (source_entry_id, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
