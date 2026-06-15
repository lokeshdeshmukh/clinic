CREATE TABLE IF NOT EXISTS patient_identities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    provider ENUM('google') NOT NULL,
    provider_user_id VARCHAR(191) NOT NULL,
    provider_email VARCHAR(190) NULL,
    provider_name VARCHAR(190) NULL,
    avatar_url VARCHAR(255) NULL,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_patient_identities_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    UNIQUE KEY uniq_patient_identities_provider_user (provider, provider_user_id),
    INDEX idx_patient_identities_patient (patient_id),
    INDEX idx_patient_identities_email (provider, provider_email)
);
