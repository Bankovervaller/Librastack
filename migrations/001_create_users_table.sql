-- Migration: create users table
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  reset_token CHAR(64) NULL DEFAULT NULL,
  reset_token_expires DATETIME NULL DEFAULT NULL,
  INDEX idx_users_email (email),
  INDEX idx_users_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

