SET foreign_key_checks = 0;

/**
 * Each user can be assigned to a study cell
 * This table handles mapping that relationship and 
 * indicating if the current assignment is active
 */
CREATE TABLE study_cell_assignment (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    study_cell_id int(10) DEFAULT NULL,
    active boolean,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY study_cell_assignment_unique (user_id, study_cell_id),
    CONSTRAINT study_cell_assignment_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_cell_assignment_study_cell_id FOREIGN KEY (study_cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;
