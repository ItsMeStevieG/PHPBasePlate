CREATE TABLE IF NOT EXISTS media_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    media_file_id BIGINT UNSIGNED NOT NULL,
    content_entry_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(191) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (media_file_id) REFERENCES media_files(id) ON DELETE CASCADE,
    FOREIGN KEY (content_entry_id) REFERENCES content_entries(id) ON DELETE CASCADE,
    INDEX idx_entry_field (content_entry_id, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
