-- SQL: create_quizzes_table.sql
-- Create quizzes table to store quiz metadata

CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_code` VARCHAR(32) NOT NULL COMMENT 'Unique code used to identify the quiz',
  `code` VARCHAR(10) NOT NULL COMMENT 'Legacy quiz code kept for backward compatibility',
  `title` VARCHAR(255) NOT NULL COMMENT 'Quiz title',
  `created_by` INT UNSIGNED NOT NULL COMMENT 'User id of teacher who created this quiz',
  `duration` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Duration in minutes',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_quiz_code` (`quiz_code`),
  UNIQUE KEY `uniq_code` (`code`),
  INDEX (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
