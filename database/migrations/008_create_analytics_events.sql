CREATE TABLE IF NOT EXISTS analytics_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clinic_id BIGINT UNSIGNED NULL,
    doctor_id BIGINT UNSIGNED NULL,
    patient_id BIGINT UNSIGNED NULL,
    appointment_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(80) NOT NULL,
    event_date DATE NOT NULL,
    metadata_json JSON NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_analytics_clinic FOREIGN KEY (clinic_id) REFERENCES clinics(id),
    CONSTRAINT fk_analytics_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    CONSTRAINT fk_analytics_patient FOREIGN KEY (patient_id) REFERENCES patients(id),
    CONSTRAINT fk_analytics_appointment FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_analytics_event_type (event_type),
    INDEX idx_analytics_event_date (event_date),
    INDEX idx_analytics_clinic_date (clinic_id, event_date)
);
