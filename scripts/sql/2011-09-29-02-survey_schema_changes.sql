SET foreign_key_checks = 0;

ALTER TABLE survey ADD COLUMN number_of_questions int(4) NULL DEFAULT NULL;
ALTER TABLE survey ADD COLUMN ordinal int(4) NULL DEFAULT NULL;

SET foreign_key_checks = 1;
