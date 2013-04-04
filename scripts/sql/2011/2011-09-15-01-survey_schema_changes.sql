SET foreign_key_checks = 0;

ALTER TABLE survey_user_map CHANGE response_id response_id varchar(32);
ALTER TABLE survey_user_map ADD COLUMN status ENUM('complete', 'archive') NOT NULL;

SET foreign_key_checks = 1;
