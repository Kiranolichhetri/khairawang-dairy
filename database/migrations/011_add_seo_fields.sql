-- =====================================================
-- Migration: Add SEO Fields to Tables
-- KHAIRAWANG DAIRY - Phase 8: SEO Optimization
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Add SEO settings to the settings table
INSERT INTO `settings` (`key`, `value`, `type`, `group`) VALUES
    ('seo_meta_title', 'KHAIRAWANG DAIRY - Premium Dairy Products from Nepal', 'string', 'seo'),
    ('seo_meta_description', 'Fresh, organic dairy products delivered to your doorstep. Quality milk, butter, cheese and more from Khairawang Dairy Nepal.', 'string', 'seo'),
    ('seo_meta_keywords', 'dairy products, milk, butter, cheese, organic, nepal, kathmandu', 'string', 'seo'),
    ('seo_og_image', '/assets/images/og-image.jpg', 'string', 'seo'),
    ('google_analytics_id', '', 'string', 'seo'),
    ('google_search_console_id', '', 'string', 'seo'),
    ('facebook_pixel_id', '', 'string', 'seo'),
    ('social_facebook', '', 'string', 'social'),
    ('social_instagram', '', 'string', 'social'),
    ('social_twitter', '', 'string', 'social'),
    ('social_youtube', '', 'string', 'social')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Add coupon_code and coupon_discount to orders table
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `coupon_code` VARCHAR(50) NULL AFTER `discount`,
    ADD COLUMN IF NOT EXISTS `coupon_discount` DECIMAL(10,2) DEFAULT 0 AFTER `coupon_code`;

SET FOREIGN_KEY_CHECKS = 1;
