ALTER TABLE patients
    MODIFY email VARCHAR(190) NULL,
    MODIFY phone VARCHAR(30) NULL,
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN email_verified_at DATETIME NULL AFTER email,
    ADD COLUMN phone_verified_at DATETIME NULL AFTER phone,
    ADD COLUMN last_login_at DATETIME NULL AFTER password_hash;
