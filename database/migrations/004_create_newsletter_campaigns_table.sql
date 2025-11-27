-- Migration: Create newsletter_campaigns table
-- KHAIRAWANG DAIRY - Newsletter Campaign Management

CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `sent_count` INT UNSIGNED DEFAULT 0,
    `opened_count` INT UNSIGNED DEFAULT 0,
    `clicked_count` INT UNSIGNED DEFAULT 0,
    `status` ENUM('draft', 'scheduled', 'sending', 'sent') DEFAULT 'draft',
    `scheduled_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_status` (`status`),
    INDEX `idx_scheduled` (`scheduled_at`),
    INDEX `idx_sent` (`sent_at`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add unsubscribe_token to existing newsletter_subscribers table if not exists
-- Note: This is safe to run multiple times due to IF NOT EXISTS
ALTER TABLE `newsletter_subscribers` 
ADD COLUMN IF NOT EXISTS `unsubscribe_token` VARCHAR(64) NULL UNIQUE AFTER `name`;

-- Update existing subscribers with tokens (only where token is NULL or empty)
-- This is idempotent - it will only update records that don't have a token
UPDATE `newsletter_subscribers` 
SET `unsubscribe_token` = SHA2(CONCAT(email, RAND(), NOW()), 256)
WHERE `unsubscribe_token` IS NULL OR `unsubscribe_token` = '';
