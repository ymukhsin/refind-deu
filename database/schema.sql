CREATE DATABASE IF NOT EXISTS `refind`;

USE `refind`;

DROP TABLE IF EXISTS `returns`;
DROP TABLE IF EXISTS `claims`;
DROP TABLE IF EXISTS `listings`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `user_id`       INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`     VARCHAR(100) NOT NULL,
    `email`         VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone`         VARCHAR(20)  NOT NULL,
    `student_id`    VARCHAR(30)  NOT NULL,
    `role`          ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_users_email`      (`email`),
    UNIQUE KEY `uq_users_student_id` (`student_id`)
);

CREATE TABLE `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50) NOT NULL,
    UNIQUE KEY `uq_categories_name` (`name`)
);

CREATE TABLE `locations` (
    `location_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `building`    VARCHAR(100) NOT NULL
);

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
);

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
);

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
);

INSERT INTO users
(full_name, email, password_hash, phone, student_id)
VALUES
(
    'John Smith',
    'john@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '01012345678',
    '20250001'
),
(
    'Sarah Kim',
    'sarah@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '01023456789',
    '20250002'
),
(
    'David Lee',
    'david@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '01034567890',
    '20250003'
),
(
    'Emma Wilson',
    'emma@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '01045678901',
    '20250004'
);

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

INSERT INTO listings
(reporter_id, type, category_id, location_id,
 title, description, item_date, status, image_path)
VALUES

(1, 'lost', 1, 1,
 'Lost Apple AirPods Pro',
 'White AirPods Pro lost in the library study room.',
 '2026-11-24', 'open',
 'assets/uploads/airpods.jpg'),

(1, 'found', 4, 2,
 'Found Black Wallet',
 'Black leather wallet found near the cafeteria.',
 '2026-11-23', 'open',
 'assets/uploads/wallet.jpg'),

(1, 'lost', 3, 3,
 'Lost House Keys',
 'Three silver keys with a blue keychain.',
 '2026-11-22', 'open',
 'assets/uploads/house-keys.jpg'),

(1, 'found', 1, 3,
 'Found iPhone 15',
 'Black iPhone with a transparent phone case.',
 '2026-11-24', 'open',
 'assets/uploads/iphone.jpg'),

(1, 'lost', 2, 2,
 'Lost Student ID Card',
 'Student ID card lost near the cafeteria.',
 '2026-11-21', 'open',
 'assets/uploads/student-id.jpg'),

(1, 'found', 5, 4,
 'Found White Hoodie',
 'White hoodie left in the gym locker room.',
 '2026-11-20', 'open',
 'assets/uploads/hoodie.jpg'),

(1, 'lost', 6, 1,
 'Lost Linear Algebra Textbook',
 'Mathematics textbook with handwritten notes inside.',
 '2026-11-19', 'open',
 'assets/uploads/textbook.jpg'),

(1, 'found', 4, 1,
 'Found Blue Backpack',
 'Navy blue backpack found under a library desk.',
 '2026-11-24', 'open',
 'assets/uploads/backpack.jpg'),

(1, 'lost', 1, 3,
 'Lost MacBook Charger',
 'White USB-C Apple charger and charging cable.',
 '2026-11-23', 'open',
 'assets/uploads/charger.jpg'),

(1, 'found', 7, 2,
 'Found Pink Umbrella',
 'Pink folding umbrella near the cafeteria entrance.',
 '2026-11-22', 'open',
 'assets/uploads/umbrella.jpg'),

(1, 'lost', 5, 4,
 'Lost Black Jacket',
 'Black winter jacket lost after gym practice.',
 '2026-11-18', 'open',
 'assets/uploads/jacket.jpg'),

(1, 'found', 3, 1,
 'Found Car Keys',
 'Car keys with a black key holder.',
 '2026-11-17', 'open',
 'assets/uploads/car-keys.jpg'),

(1, 'lost', 2, 2,
 'Lost Credit Card',
 'Bank card lost inside the student union building.',
 '2026-11-16', 'open',
 'assets/uploads/credit-card.jpg'),

(1, 'found', 7, 4,
 'Found Water Bottle',
 'Green stainless steel water bottle.',
 '2026-11-14', 'open',
 'assets/uploads/water-bottle.jpg');

INSERT INTO users (
    full_name,
    email,
    password_hash,
    phone,
    student_id,
    role
)
VALUES (
    'ReFind Administrator',
    'admin@refind.com',
    '$2y$10$a0b6DcPO/Tifr2EtAkfZxeS5l/p6Ypiuh3pdxdJWYPYuxequ/b1PW',
    '01012345678',
    '99999999',
    'admin'
);