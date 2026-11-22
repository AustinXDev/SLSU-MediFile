CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value TEXT NULL,
    updated_by INT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_system_settings_updated_by
        FOREIGN KEY (updated_by) REFERENCES admin (admin_id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_key, setting_value)
VALUES
    ('application_name', 'SLSU-Health Record'),
    ('institution_name', 'Southern Luzon State University'),
    ('school_logo_path', 'assets/images/SLSU-LOGO.png'),
    ('developer_contact_email', '')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

CREATE TABLE IF NOT EXISTS backup_archives (
    backup_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL UNIQUE,
    size_bytes BIGINT UNSIGNED NOT NULL,
    backup_type VARCHAR(32) NOT NULL DEFAULT 'Full',
    status VARCHAR(32) NOT NULL DEFAULT 'Completed',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_backup_archives_created_by
        FOREIGN KEY (created_by) REFERENCES admin (admin_id)
        ON DELETE SET NULL,
    INDEX idx_backup_archives_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
