ALTER TABLE survey_trailer_info CHANGE COLUMN category category enum('retro movie', 'game', 'pre-release movie', 'pre-release game') NOT NULL;
UPDATE survey_trailer_info SET category = 'pre-release game' WHERE category = 'game';
ALTER TABLE survey_trailer_info CHANGE COLUMN category category enum('retro movie', 'pre-release movie', 'pre-release game') NOT NULL;
