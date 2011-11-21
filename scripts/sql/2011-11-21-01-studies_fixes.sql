SET foreign_key_checks = 0;

ALTER TABLE `study` ADD `study_id` VARCHAR( 16 ) NOT NULL AFTER `name` ;
ALTER TABLE `study` ADD `study_type` TINYINT UNSIGNED NOT NULL DEFAULT '1' AFTER `id`;

SET foreign_key_checks = 1;