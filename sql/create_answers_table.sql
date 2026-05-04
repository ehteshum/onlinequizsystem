-- SQL: create_answers_table.sql
-- Create answers table to store selected answers for each attempt

CREATE TABLE IF NOT EXISTS `answers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attempt_id` INT UNSIGNED NOT NULL COMMENT 'References attempts.id',
  `question_id` INT UNSIGNED NOT NULL COMMENT 'References questions.id',
  `selected_option_id` INT UNSIGNED NULL COMMENT 'Selected option id, or NULL if unanswered',
  PRIMARY KEY (`id`),
  INDEX (`attempt_id`),
  INDEX (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
