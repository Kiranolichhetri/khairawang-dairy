-- Migration: Create notification_preferences table
-- KHAIRAWANG DAIRY - User Notification Preferences

CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `email_orders` BOOLEAN DEFAULT TRUE,
    `email_promotions` BOOLEAN DEFAULT TRUE,
    `email_newsletter` BOOLEAN DEFAULT TRUE,
    `sms_orders` BOOLEAN DEFAULT TRUE,
    `sms_promotions` BOOLEAN DEFAULT FALSE,
    `push_enabled` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_user` (`user_id`),
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
