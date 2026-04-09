CREATE TABLE IF NOT EXISTS content_entry_revisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_entry_id BIGINT UNSIGNED NOT NULL,
    revision_number INT UNSIGNED NOT NULL,
    title VARCHAR(500) NULL,
    slug VARCHAR(500) NULL,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    summary TEXT NULL,
    payload_json JSON NULL,
    saved_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_entry_id) REFERENCES content_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (saved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_entry_rev (content_entry_id, revision_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
