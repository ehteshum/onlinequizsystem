-- SQL: create_quizzes_table.sql
-- Create quizzes table to store quiz metadata

CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL COMMENT 'Quiz title',
  `created_by` INT UNSIGNED NOT NULL COMMENT 'User id of teacher who created this quiz',
  `duration` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Duration in minutes',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
