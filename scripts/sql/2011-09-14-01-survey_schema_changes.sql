SET foreign_key_checks = 0;

ALTER TABLE survey DROP KEY survey_unique;
ALTER TABLE survey DROP COLUMN url;
ALTER TABLE survey ADD COLUMN external_id varchar(32);
ALTER TABLE survey ADD UNIQUE KEY survey_unique (external_id);
ALTER TABLE survey ADD COLUMN external_key varchar(32);
ALTER TABLE survey ADD COLUMN type ENUM('poll', 'survey') NOT NULL AFTER user_id;
ALTER TABLE survey ADD COLUMN title varchar(255) AFTER starbar_id;
ALTER TABLE survey CHANGE origin origin ENUM('SurveyGizmo', 'Internal') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE survey_user_map ADD COLUMN response_id varchar(32) COMMENT "NULL means the user has archived the survey";

SET foreign_key_checks = 1;
