-- Migration: Create addresses table
-- KHAIRAWANG DAIRY - User Address Book

CREATE TABLE IF NOT EXISTS `addresses` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `label` VARCHAR(50) DEFAULT 'Home',
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `address_line_1` VARCHAR(255) NOT NULL,
    `address_line_2` VARCHAR(255),
    `city` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20),
    `address_type` ENUM('shipping', 'billing', 'both') DEFAULT 'both',
    `is_default` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user` (`user_id`),
    INDEX `idx_default` (`user_id`, `is_default`),
    INDEX `idx_type` (`address_type`),
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
