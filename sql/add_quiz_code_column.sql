-- SQL: add_quiz_code_column.sql
-- Migration for existing databases to add the unique quiz code columns.
-- `code` is kept for legacy compatibility with older data/schema versions.

ALTER TABLE `quizzes`
  ADD COLUMN `quiz_code` VARCHAR(32) NOT NULL COMMENT 'Unique code used to identify the quiz' AFTER `id`,
  ADD COLUMN `code` VARCHAR(10) NOT NULL COMMENT 'Legacy quiz code kept for backward compatibility' AFTER `quiz_code`,
  ADD UNIQUE KEY `uniq_quiz_code` (`quiz_code`),
  ADD UNIQUE KEY `uniq_code` (`code`);
