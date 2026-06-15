CREATE TABLE IF NOT EXISTS email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NULL,
    patient_id BIGINT UNSIGNED NULL,
    appointment_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(190) NOT NULL,
    subject VARCHAR(190) NOT NULL,
    template_key VARCHAR(80) NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    error_message TEXT NULL,
    sent_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_email_logs_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id),
    CONSTRAINT fk_email_logs_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_email_logs_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_email_logs_template (template_key),
    INDEX idx_email_logs_status (status)
);
