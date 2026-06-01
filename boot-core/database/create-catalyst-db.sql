-- ============================================================
-- Catalyst Framework — Schema canónico + datos iniciales
-- Última actualización: 2026-05-19
--
-- FUENTE DE VERDAD. Actualizar este archivo cada vez que
-- se agregue o modifique una tabla / constraint / seed.
-- Nota operativa 2026-05-19:
--   este bootstrap ya queda alineado con PA-02 para auth/RBAC base
--   y debe complementarse con `php public/cli.php migrate` para
--   materializar las tablas framework post-roadmap y hardenings posteriores.
--
-- Recrear desde cero (Docker):
--   docker exec -i WSDD-MySql-Server mysql -u root -p < create-catalyst-db.sql
--
-- Recrear desde cero (mysql local):
--   mysql -u root -p < create-catalyst-db.sql
--
-- Usuario demo creado: admin@catalyst.dock / password
-- ============================================================

CREATE DATABASE IF NOT EXISTS catalyst
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE catalyst;

-- ============================================================
-- Etapa 15: Migration System — tabla de tracking
-- ============================================================

CREATE TABLE IF NOT EXISTS migrations (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(14)  NOT NULL,
    name    VARCHAR(191) NOT NULL,
    batch   INT UNSIGNED NOT NULL,
    ran_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_migrations_version (version),
    INDEX idx_migrations_batch (batch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Etapa 3: Validator — tabla demo para regla 'unique'
-- ============================================================

CREATE TABLE IF NOT EXISTS validator_demo_emails (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO validator_demo_emails (email)
VALUES ('taken@example.com'), ('used@catalyst.dev');

-- ============================================================
-- Etapa 5: Auth — usuarios, tokens, cuentas sociales
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    tenant_id      INT UNSIGNED  NOT NULL DEFAULT 1,
    name           VARCHAR(255)  NOT NULL,
    email          VARCHAR(255)  NOT NULL,
    password       VARCHAR(255)  NOT NULL DEFAULT '',
    active            TINYINT(1)    NOT NULL DEFAULT 1,   -- 1=activo, 0=inactivo (sin DELETE)
    email_verified    TINYINT(1)    NOT NULL DEFAULT 0,   -- 1=verificado
    last_login        DATETIME      NULL,
    mfa_secret        VARCHAR(32)   NULL,                 -- TOTP base32 secret (Etapa 12)
    mfa_enabled       TINYINT(1)    NOT NULL DEFAULT 0,   -- 1=MFA activo (HIPAA §164.312(d))
    mfa_backup_codes  JSON          NULL,                 -- one-time backup codes array
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_tenant_email (tenant_id, email),
    INDEX idx_email  (email),
    INDEX idx_active (active),
    INDEX idx_users_tenant_active (tenant_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    token_hash  VARCHAR(255) NOT NULL UNIQUE,
    active      TINYINT(1)   NOT NULL DEFAULT 1,       -- 1=válido, 0=invalidado (sin DELETE)
    expires_at  DATETIME     NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id        (user_id),
    INDEX idx_token_hash     (token_hash),
    INDEX idx_active_expires (active, expires_at),
    CONSTRAINT fk_remember_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    active     TINYINT(1)   NOT NULL DEFAULT 1,        -- 1=pendiente, 0=usado (sin DELETE)
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id    (user_id),
    INDEX idx_token_hash (token_hash),
    CONSTRAINT fk_email_verification_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL UNIQUE,
    active     TINYINT(1)   NOT NULL DEFAULT 1,        -- 1=pendiente, 0=usado (sin DELETE)
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id    (user_id),
    INDEX idx_token_hash (token_hash),
    CONSTRAINT fk_password_reset_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_social_accounts (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    provider         VARCHAR(50)  NOT NULL,             -- 'google', 'github'
    provider_user_id VARCHAR(255) NOT NULL,
    active           TINYINT(1)   NOT NULL DEFAULT 1,  -- 1=activo, 0=desvinculado (sin DELETE)
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_provider_user (provider, provider_user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_active  (active),
    CONSTRAINT fk_user_social_accounts_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: usuario admin por defecto (contraseña: password)
INSERT IGNORE INTO users (tenant_id, name, email, password, active, email_verified)
VALUES (1, 'Admin', 'admin@catalyst.dock',
        '$2y$12$rOuc/k8mR/rMYjDOOAco1ub.a9hNdO9G/nUSX8wX6j/3f9.btKz92', 1, 1);

-- ============================================================
-- Etapa 6: RBAC — roles, permisos y asignaciones
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL DEFAULT 1,
    name        VARCHAR(50)  NOT NULL,   -- 'Administrator'
    slug        VARCHAR(50)  NOT NULL,   -- 'admin'
    description VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_roles_tenant_name (tenant_id, name),
    UNIQUE KEY uq_roles_tenant_slug (tenant_id, slug),
    INDEX idx_roles_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL DEFAULT 1,
    name        VARCHAR(100) NOT NULL,   -- 'Manage Users'
    slug        VARCHAR(100) NOT NULL,   -- 'manage-users'
    description VARCHAR(255) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_permissions_tenant_name (tenant_id, name),
    UNIQUE KEY uq_permissions_tenant_slug (tenant_id, slug),
    INDEX idx_permissions_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    tenant_id     INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (role_id, permission_id),
    INDEX idx_role_permissions_tenant_permission (tenant_id, permission_id),
    CONSTRAINT fk_role_permissions_role
        FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission
        FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, role_id),
    INDEX idx_user_roles_tenant_role (tenant_id, role_id),
    CONSTRAINT fk_user_roles_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role
        FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: roles base
INSERT IGNORE INTO roles (tenant_id, name, slug, description) VALUES
    (1, 'Administrator', 'admin', 'Full system access'),
    (1, 'User',          'user',  'Standard user access');

-- Seed: permisos base
INSERT IGNORE INTO permissions (tenant_id, name, slug, description) VALUES
    (1, 'Manage Users',   'manage-users',   'Create, edit, deactivate users'),
    (1, 'View Dashboard', 'view-dashboard', 'Access admin dashboard'),
    (1, 'Manage Roles',   'manage-roles',   'Create, edit, delete roles and permissions'),
    (1, 'Access DevTools','access-devtools','Access development-only runtime tooling');

-- Seed: rol admin tiene todos los permisos; rol user tiene view-dashboard
INSERT IGNORE INTO role_permissions (role_id, permission_id, tenant_id)
VALUES (1,1,1),(1,2,1),(1,3,1),(1,4,1),(2,2,1);

-- Seed: usuario admin (id=1) tiene rol admin
INSERT IGNORE INTO user_roles (user_id, role_id, tenant_id) VALUES (1, 1, 1);

-- ============================================================
-- Etapa 5/6: Notifications — bandeja de notificaciones
-- ============================================================

CREATE TABLE IF NOT EXISTS notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       VARCHAR(50)  NOT NULL DEFAULT 'info',  -- info|success|warning|error|system
    title      VARCHAR(255) NOT NULL,
    body       TEXT         NULL,
    read_at    DATETIME     NULL,                      -- NULL=no leída; timestamp=leída (sin DELETE)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id     (user_id),
    INDEX idx_user_unread (user_id, read_at),
    INDEX idx_created_at  (created_at),
    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
