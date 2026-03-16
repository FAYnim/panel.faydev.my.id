-- ============================================================
-- Faydev Dashboard — Database Migration (Phase 1 MVP)
-- Run against existing `fayd7716_project` database
-- ============================================================

USE `fayd7716_project`;

-- ------------------------------------------------------------
-- 1. admins table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(50)     NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. site_settings table (key-value store for content sections)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `section`    VARCHAR(50)     NOT NULL,
    `key`        VARCHAR(100)    NOT NULL,
    `value`      TEXT            NULL,
    `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_section_key` (`section`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Add display_order to existing tables
-- ------------------------------------------------------------
-- Using stored procedure to make ALTER idempotent (safe to re-run)

DELIMITER //

DROP PROCEDURE IF EXISTS `_dashboard_migrate`//

CREATE PROCEDURE `_dashboard_migrate`()
BEGIN
    -- Add display_order to projects if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'projects'
          AND COLUMN_NAME  = 'display_order'
    ) THEN
        ALTER TABLE `projects`
            ADD COLUMN `display_order` INT NOT NULL DEFAULT 0;
    END IF;

    -- Add display_order to social_links if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'social_links'
          AND COLUMN_NAME  = 'display_order'
    ) THEN
        ALTER TABLE `social_links`
            ADD COLUMN `display_order` INT NOT NULL DEFAULT 0;
    END IF;
END//

DELIMITER ;

CALL `_dashboard_migrate`();
DROP PROCEDURE IF EXISTS `_dashboard_migrate`;

-- ------------------------------------------------------------
-- 4. Default admin user
--    Username: admin
--    Password: admin123 (CHANGE ON FIRST LOGIN!)
--    Hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO `admins` (`username`, `password_hash`)
SELECT 'admin', '$2y$10$UHuDHXnZyIu558pKG1gqdOw9eMqEEhau3o36lRNolPWyoMYn6fokm'
WHERE NOT EXISTS (
    SELECT 1 FROM `admins` WHERE `username` = 'admin'
);
