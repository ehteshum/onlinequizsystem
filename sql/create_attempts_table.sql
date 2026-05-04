-- SQL: create_attempts_table.sql
-- Create attempts table to store each student's quiz attempt

CREATE TABLE IF NOT EXISTS `attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL COMMENT 'Student user id',
  `quiz_id` INT UNSIGNED NOT NULL COMMENT 'Quiz being attempted',
  `score` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Final score for the attempt',
  `start_time` DATETIME NOT NULL COMMENT 'Attempt start time',
  `end_time` DATETIME NULL COMMENT 'Attempt end time',
  PRIMARY KEY (`id`),
  INDEX (`user_id`),
  INDEX (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
