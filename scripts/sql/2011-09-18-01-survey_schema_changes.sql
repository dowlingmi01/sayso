SET foreign_key_checks = 0;

ALTER TABLE survey ADD premium TINYINT(1) NULL DEFAULT NULL;

SET foreign_key_checks = 1;
