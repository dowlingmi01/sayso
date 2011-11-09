SET foreign_key_checks = 0;

ALTER TABLE study_cell_assignment MODIFY user_id int(10) DEFAULT NULL;
ALTER TABLE study_cell_assignment DROP FOREIGN KEY study_cell_assignment_ibfk_1 ;
ALTER TABLE study_cell_assignment ADD FOREIGN KEY study_cell_assignment_user_id ( user_id ) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE ;

SET foreign_key_checks = 1;
