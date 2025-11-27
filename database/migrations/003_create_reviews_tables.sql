-- Migration: Create reviews and related tables
-- KHAIRAWANG DAIRY - Product Reviews System

-- Update existing reviews table if it exists or create new one
-- Note: The schema.sql already has a basic reviews table, we'll add missing columns

-- Add missing columns to reviews table if they don't exist
ALTER TABLE `reviews` 
    ADD COLUMN IF NOT EXISTS `order_id` BIGINT UNSIGNED NULL AFTER `user_id`,
    ADD COLUMN IF NOT EXISTS `is_verified_purchase` BOOLEAN DEFAULT FALSE AFTER `comment`,
    ADD COLUMN IF NOT EXISTS `helpful_count` INT UNSIGNED DEFAULT 0 AFTER `is_verified_purchase`,
    ADD INDEX IF NOT EXISTS `idx_order` (`order_id`),
    ADD FOREIGN KEY IF NOT EXISTS `fk_reviews_order` (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Create review_images table
CREATE TABLE IF NOT EXISTS `review_images` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `review_id` BIGINT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_review` (`review_id`),
    
    FOREIGN KEY (`review_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create review_helpfuls table for tracking helpful votes
CREATE TABLE IF NOT EXISTS `review_helpfuls` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `review_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_review_user` (`review_id`, `user_id`),
    INDEX `idx_review` (`review_id`),
    INDEX `idx_user` (`user_id`),
    
    FOREIGN KEY (`review_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
