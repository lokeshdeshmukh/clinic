CREATE TABLE IF NOT EXISTS revenue_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    appointment_id BIGINT UNSIGNED NOT NULL UNIQUE,
    appointment_date DATE NOT NULL,
    consultation_fee DECIMAL(10, 2) NOT NULL,
    doctor_revenue DECIMAL(10, 2) NOT NULL,
    clinic_revenue DECIMAL(10, 2) NOT NULL,
    recorded_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_revenue_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id),
    CONSTRAINT fk_revenue_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    CONSTRAINT fk_revenue_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_revenue_clinic_date (clinic_id, appointment_date),
    INDEX idx_revenue_doctor_date (doctor_id, appointment_date)
);
