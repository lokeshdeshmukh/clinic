CREATE TABLE IF NOT EXISTS appointment_status_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    changed_by_type ENUM('clinic', 'patient', 'system') NOT NULL,
    changed_by_id BIGINT UNSIGNED NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_status_logs_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_status_logs_appointment (appointment_id),
    INDEX idx_status_logs_created_at (created_at)
);
