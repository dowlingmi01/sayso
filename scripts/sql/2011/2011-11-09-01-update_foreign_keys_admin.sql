SET foreign_key_checks = 0;
/**
 * @see https://www.assembla.com/spaces/say-so/tickets/191
*/
UPDATE study SET user_id =1;
ALTER TABLE study DROP FOREIGN KEY study_user_id ;
ALTER TABLE study CHANGE user_id user_id INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE study ADD FOREIGN KEY ( user_id ) REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE ;

UPDATE study_cell_assignment SET user_id =1;
ALTER TABLE study_cell_assignment DROP FOREIGN KEY study_cell_assignment_user_id ;
ALTER TABLE study_cell_assignment CHANGE user_id user_id INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE study_cell_assignment ADD FOREIGN KEY ( user_id ) REFERENCES admin_user (id) ON DELETE CASCADE ON UPDATE CASCADE ;

UPDATE study_creative SET user_id =1;
ALTER TABLE study_creative DROP FOREIGN KEY creative_user_id ;
ALTER TABLE study_creative CHANGE user_id user_id INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE study_creative ADD FOREIGN KEY ( user_id ) REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE ;

UPDATE study_domain SET user_id =1;
ALTER TABLE study_domain DROP FOREIGN KEY domain_user_id ;
ALTER TABLE study_domain CHANGE user_id user_id INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE study_domain ADD FOREIGN KEY ( user_id ) REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE ;

UPDATE study_tag SET user_id =1;
ALTER TABLE study_tag DROP FOREIGN KEY tag_user_id ;
ALTER TABLE study_tag CHANGE user_id user_id INT( 10 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE study_tag ADD FOREIGN KEY ( user_id ) REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE ;

SET foreign_key_checks = 1;
