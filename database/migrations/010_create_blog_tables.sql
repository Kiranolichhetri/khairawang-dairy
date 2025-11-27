-- =====================================================
-- Migration: Create Blog Tables
-- KHAIRAWANG DAIRY - Phase 8: Blog/News System
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table: blog_categories
-- Blog post categories
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: blog_tags
-- Blog post tags
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Modify existing blog_posts table to add more fields
-- or create it if doesn't exist
-- -----------------------------------------------------
-- Drop and recreate to ensure proper structure
DROP TABLE IF EXISTS `blog_post_tags`;

ALTER TABLE `blog_posts`
    ADD COLUMN IF NOT EXISTS `category_id` INT UNSIGNED NULL AFTER `featured_image`,
    ADD COLUMN IF NOT EXISTS `meta_title` VARCHAR(255) NULL AFTER `published_at`,
    ADD COLUMN IF NOT EXISTS `meta_description` TEXT NULL AFTER `meta_title`,
    ADD COLUMN IF NOT EXISTS `meta_keywords` VARCHAR(255) NULL AFTER `meta_description`,
    ADD COLUMN IF NOT EXISTS `views_count` INT UNSIGNED DEFAULT 0 AFTER `meta_keywords`;

-- Add foreign key constraint for category if not exists
-- Note: This may fail if constraint already exists
ALTER TABLE `blog_posts`
    ADD CONSTRAINT `fk_blog_posts_category` 
    FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table: blog_post_tags
-- Many-to-many relationship between posts and tags
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `blog_post_tags` (
    `post_id` BIGINT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    
    PRIMARY KEY (`post_id`, `tag_id`),
    
    INDEX `idx_post` (`post_id`),
    INDEX `idx_tag` (`tag_id`),
    
    FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `blog_tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insert default blog categories
-- -----------------------------------------------------
INSERT IGNORE INTO `blog_categories` (`name`, `slug`, `description`) VALUES
    ('News', 'news', 'Latest news and announcements'),
    ('Recipes', 'recipes', 'Dairy recipes and cooking tips'),
    ('Health & Nutrition', 'health-nutrition', 'Health benefits of dairy products'),
    ('Farm Updates', 'farm-updates', 'Updates from our dairy farm');

SET FOREIGN_KEY_CHECKS = 1;
