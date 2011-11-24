SET foreign_key_checks = 0;

ALTER TABLE study_tag ADD study_id INT( 10 ) NOT NULL AFTER user_id , ADD INDEX ( study_id ) ;
ALTER TABLE study_tag ADD FOREIGN KEY ( study_id ) REFERENCES sayso.study ( id ) ON DELETE CASCADE ON UPDATE CASCADE ;

SET foreign_key_checks = 1;
