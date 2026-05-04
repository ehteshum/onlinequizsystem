-- SQL: create_options_table.sql
-- Create options table to store MCQ options for questions

CREATE TABLE IF NOT EXISTS `options` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` INT UNSIGNED NOT NULL COMMENT 'References questions.id',
  `option_text` VARCHAR(500) NOT NULL COMMENT 'Option text',
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 if this option is correct, else 0',
  PRIMARY KEY (`id`),
  INDEX (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
