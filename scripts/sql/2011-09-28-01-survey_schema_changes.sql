SET foreign_key_checks = 0;

ALTER TABLE survey ADD number_of_answers INT(4) NULL DEFAULT NULL;

SET foreign_key_checks = 1;
