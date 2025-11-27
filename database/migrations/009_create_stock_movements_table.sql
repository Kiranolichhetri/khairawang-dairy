-- =====================================================
-- Migration: Create Stock Movements Table
-- KHAIRAWANG DAIRY - Phase 8: Inventory Management
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table: stock_movements
-- Tracks all inventory changes for audit trail
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NULL,
    `type` ENUM('in', 'out', 'adjustment', 'reserved', 'released') NOT NULL,
    `quantity` INT NOT NULL,
    `reference_type` VARCHAR(50) NULL COMMENT 'order, adjustment, return, etc.',
    `reference_id` BIGINT UNSIGNED NULL COMMENT 'ID of the related record',
    `notes` TEXT NULL,
    `stock_before` INT NOT NULL DEFAULT 0,
    `stock_after` INT NOT NULL DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_product` (`product_id`),
    INDEX `idx_variant` (`variant_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_reference` (`reference_type`, `reference_id`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_created_by` (`created_by`),
    
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add notify_low_stock column to products if it doesn't exist
ALTER TABLE `products` 
    ADD COLUMN IF NOT EXISTS `notify_low_stock` BOOLEAN DEFAULT TRUE AFTER `low_stock_threshold`;

SET FOREIGN_KEY_CHECKS = 1;
