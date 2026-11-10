-- =============================================================
--  ReFind — Lost & Found System
--  MySQL database schema (utf8mb4)
--
--  Run with:  mysql -u root -p < database/schema.sql
-- =============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `refind`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `refind`;

-- -------------------------------------------------------------
-- Drop tables in reverse-dependency order for clean re-runs
-- -------------------------------------------------------------
DROP TABLE IF EXISTS `returns`;
DROP TABLE IF EXISTS `claims`;
DROP TABLE IF EXISTS `listings`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- -------------------------------------------------------------
-- users
-- -------------------------------------------------------------
CREATE TABLE `users` (
    `user_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`     VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone`         VARCHAR(20)  NULL,
    `student_id`    VARCHAR(30)  NOT NULL,
    `role`          ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_users_email`      (`email`),
    UNIQUE KEY `uq_users_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- categories
-- -------------------------------------------------------------
CREATE TABLE `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50) NOT NULL,
    UNIQUE KEY `uq_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- locations
-- -------------------------------------------------------------
CREATE TABLE `locations` (
    `location_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `building`    VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- listings
-- -------------------------------------------------------------
CREATE TABLE `listings` (
    `listing_id`  INT AUTO_INCREMENT PRIMARY KEY,
    `reporter_id` INT NOT NULL,
    `type`        ENUM('lost','found') NOT NULL,
    `category_id` INT NOT NULL,
    `location_id` INT NOT NULL,
    `title`       VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `item_date`   DATE NOT NULL,
    `status`      ENUM('open','claimed','resolved','closed') NOT NULL DEFAULT 'open',
    `image_path`  VARCHAR(255) NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_listings_reporter` FOREIGN KEY (`reporter_id`)
        REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_listings_category` FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`category_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_listings_location` FOREIGN KEY (`location_id`)
        REFERENCES `locations` (`location_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- claims
-- -------------------------------------------------------------
CREATE TABLE `claims` (
    `claim_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `listing_id`   INT NOT NULL,
    `claimant_id`  INT NOT NULL,
    `explanation`  TEXT NOT NULL,
    `status`       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `reviewed_by`  INT NULL,
    `reviewed_at`  DATETIME NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_claims_listing_claimant` (`listing_id`, `claimant_id`),
    CONSTRAINT `fk_claims_listing` FOREIGN KEY (`listing_id`)
        REFERENCES `listings` (`listing_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_claims_claimant` FOREIGN KEY (`claimant_id`)
        REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_claims_reviewer` FOREIGN KEY (`reviewed_by`)
        REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- returns
-- -------------------------------------------------------------
CREATE TABLE `returns` (
    `return_id`   INT AUTO_INCREMENT PRIMARY KEY,
    `claim_id`    INT NOT NULL,
    `returned_by` INT NOT NULL,
    `received_by` INT NOT NULL,
    `return_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notes`       TEXT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_returns_claim_id` (`claim_id`),
    CONSTRAINT `fk_returns_claim` FOREIGN KEY (`claim_id`)
        REFERENCES `claims` (`claim_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_returns_returned_by` FOREIGN KEY (`returned_by`)
        REFERENCES `users` (`user_id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_returns_received_by` FOREIGN KEY (`received_by`)
        REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Seed data
-- =============================================================

INSERT INTO `categories` (`name`) VALUES
    ('Electronics'),
    ('ID & Credit Cards'),
    ('Keys'),
    ('Bags & Wallets'),
    ('Clothing'),
    ('Books & Study Materials'),
    ('Other');

INSERT INTO `locations` (`name`, `building`) VALUES
    ('Main Library 2F Study Room', 'Main Library'),
    ('Student Cafeteria', 'Student Union Building'),
    ('IT Lab 402', 'Engineering Building'),
    ('Gymnasium Locker Room', 'Gymnasium');
