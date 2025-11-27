-- =====================================================
-- Migration: Create Coupons and Coupon Usages Tables
-- KHAIRAWANG DAIRY - Phase 8: Coupons & Discounts
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table: coupons
-- Enhanced coupon system with multiple types
-- -----------------------------------------------------
DROP TABLE IF EXISTS `coupon_usages`;

ALTER TABLE `coupons` 
    ADD COLUMN IF NOT EXISTS `name` VARCHAR(255) NOT NULL AFTER `code`,
    ADD COLUMN IF NOT EXISTS `description` TEXT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `maximum_discount` DECIMAL(10,2) NULL AFTER `min_order_amount`,
    ADD COLUMN IF NOT EXISTS `per_user_limit` INT UNSIGNED DEFAULT 1 AFTER `uses_count`,
    MODIFY COLUMN `type` ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage';

-- Update existing coupons table or create if not exists
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `type` ENUM('percentage', 'fixed', 'free_shipping') DEFAULT 'percentage',
    `value` DECIMAL(10,2) NOT NULL,
    `min_order_amount` DECIMAL(10,2) DEFAULT 0,
    `maximum_discount` DECIMAL(10,2) NULL,
    `max_uses` INT UNSIGNED NULL,
    `uses_count` INT UNSIGNED DEFAULT 0,
    `per_user_limit` INT UNSIGNED DEFAULT 1,
    `starts_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_code` (`code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_dates` (`starts_at`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: coupon_usages
-- Tracks coupon usage by users for orders
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `coupon_usages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `coupon_id` INT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `discount_amount` DECIMAL(10,2) NOT NULL,
    `used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_coupon` (`coupon_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_order` (`order_id`),
    INDEX `idx_coupon_user` (`coupon_id`, `user_id`),
    
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
