CREATE TABLE IF NOT EXISTS super_admins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    deleted_at DATETIME NULL,
    INDEX idx_super_admins_status (status)
);
