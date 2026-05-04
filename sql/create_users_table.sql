-- SQL: create_users_table.sql
-- Run this in phpMyAdmin or via mysql client to create the `users` table

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL COMMENT 'Full name of the user',
  `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'User email, must be unique',
  `password` VARCHAR(255) NOT NULL COMMENT 'Password hash stored using password_hash()',
  `role` ENUM('teacher','student') NOT NULL DEFAULT 'student' COMMENT 'Role: teacher or student',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments: each column has a short explanation above as SQL comments.
