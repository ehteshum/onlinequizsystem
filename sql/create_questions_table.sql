-- SQL: create_questions_table.sql
-- Create questions table to store questions for quizzes

CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` INT UNSIGNED NOT NULL COMMENT 'References quizzes.id',
  `question_text` TEXT NOT NULL COMMENT 'The question content',
  `question_type` ENUM('mcq','true_false') NOT NULL DEFAULT 'mcq' COMMENT 'Type of question',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`quiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
