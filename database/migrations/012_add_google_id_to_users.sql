-- Add Google OAuth support to users table
-- Migration: 012_add_google_id_to_users.sql

ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL AFTER email;

CREATE INDEX idx_users_google_id ON users(google_id);
