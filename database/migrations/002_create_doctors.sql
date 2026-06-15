CREATE TABLE IF NOT EXISTS doctors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    specialization VARCHAR(120) NOT NULL,
    consultation_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    slot_duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    bio TEXT NULL,
    profile_photo_path VARCHAR(255) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    CONSTRAINT fk_doctors_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id),
    INDEX idx_doctors_clinic (clinic_id),
    INDEX idx_doctors_specialization (specialization),
    INDEX idx_doctors_status (status)
);
