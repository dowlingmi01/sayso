SET foreign_key_checks = 0;

ALTER TABLE `study` ADD `study_id` VARCHAR( 16 ) NOT NULL AFTER `name` ;

SET foreign_key_checks = 1;