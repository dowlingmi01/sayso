ALTER TABLE survey CHANGE COLUMN type type enum('survey', 'poll', 'quiz') NOT NULL;
ALTER TABLE survey_question ADD COLUMN image_url VARCHAR(2000) DEFAULT NULL;
ALTER TABLE survey_question_choice ADD COLUMN correct BOOLEAN DEFAULT NULL;
