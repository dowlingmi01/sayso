SET foreign_key_checks = 0;

ALTER TABLE study_survey ADD survey_type TINYINT UNSIGNED NOT NULL AFTER id ;
ALTER TABLE study_survey ADD INDEX ( survey_type ) ;

SET foreign_key_checks = 1;
