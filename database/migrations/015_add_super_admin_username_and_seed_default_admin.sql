SET @has_username_column := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'super_admins'
      AND column_name = 'username'
);
SET @add_username_column_sql := IF(
    @has_username_column = 0,
    'ALTER TABLE super_admins ADD COLUMN username VARCHAR(80) NULL AFTER name',
    'SELECT 1'
);
PREPARE add_username_column_stmt FROM @add_username_column_sql;
EXECUTE add_username_column_stmt;
DEALLOCATE PREPARE add_username_column_stmt;

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

SET @has_username_index := (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'super_admins'
      AND index_name = 'uniq_super_admins_username'
);
SET @add_username_index_sql := IF(
    @has_username_index = 0,
    'CREATE UNIQUE INDEX uniq_super_admins_username ON super_admins (username)',
    'SELECT 1'
);
PREPARE add_username_index_stmt FROM @add_username_index_sql;
EXECUTE add_username_index_stmt;
DEALLOCATE PREPARE add_username_index_stmt;
