ALTER TABLE super_admins
    ADD COLUMN username VARCHAR(80) NULL AFTER name;

UPDATE super_admins
SET username = CONCAT('platform-', id)
WHERE username IS NULL OR TRIM(username) = '';

UPDATE super_admins
SET username = CONCAT('platform-', id)
WHERE username = 'admin' AND email <> 'admin@huviena.local';

UPDATE super_admins
SET name = 'Huviena Platform Admin',
    username = 'admin',
    email = 'admin@huviena.local',
    password_hash = 'pbkdf2_sha256$210000$omSIygVwZ+h3EG/PYUnYUA==$u3sEI1Qva8Js/Py0HxQB5KRzL8WYzljRk6f0xGL3+XE=',
    status = 'active',
    deleted_at = NULL,
    updated_at = NOW()
WHERE email = 'admin@huviena.local';

INSERT INTO super_admins (name, username, email, password_hash, status, last_login_at, created_at, updated_at, deleted_at)
SELECT 'Huviena Platform Admin',
       'admin',
       'admin@huviena.local',
       'pbkdf2_sha256$210000$omSIygVwZ+h3EG/PYUnYUA==$u3sEI1Qva8Js/Py0HxQB5KRzL8WYzljRk6f0xGL3+XE=',
       'active',
       NULL,
       NOW(),
       NOW(),
       NULL
WHERE NOT EXISTS (
    SELECT 1
    FROM super_admins
    WHERE username = 'admin'
);

ALTER TABLE super_admins
    MODIFY COLUMN username VARCHAR(80) NOT NULL;

CREATE UNIQUE INDEX uniq_super_admins_username
    ON super_admins (username);
