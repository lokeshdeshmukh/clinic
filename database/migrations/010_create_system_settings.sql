CREATE TABLE IF NOT EXISTS system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NULL,
    clinic_scope BIGINT UNSIGNED GENERATED ALWAYS AS (IFNULL(clinic_id, 0)) STORED,
    setting_key VARCHAR(120) NOT NULL,
    setting_value TEXT NOT NULL,
    setting_type VARCHAR(30) NOT NULL DEFAULT 'string',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_settings_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id),
    UNIQUE KEY uniq_system_settings (clinic_scope, setting_key),
    INDEX idx_settings_public (is_public)
);
