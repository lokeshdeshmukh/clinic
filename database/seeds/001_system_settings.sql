INSERT INTO system_settings (clinic_id, setting_key, setting_value, setting_type, is_public, created_at, updated_at)
VALUES
    (NULL, 'appointment_reminder_hours', '24', 'integer', 0, NOW(), NOW()),
    (NULL, 'support_email', 'support@example.com', 'string', 1, NOW(), NOW()),
    (NULL, 'currency', 'INR', 'string', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    updated_at = VALUES(updated_at);
