ALTER TABLE survey_trailer_info CHANGE COLUMN category category enum('retro movie', 'game') NOT NULL;
ALTER TABLE survey_trailer_info ADD COLUMN entertainment_title varchar(2000) NOT NULL;
