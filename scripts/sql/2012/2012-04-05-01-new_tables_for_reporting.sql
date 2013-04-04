ALTER TABLE survey_question DROP FOREIGN KEY sq_sqt_id;
ALTER TABLE survey_question DROP COLUMN survey_question_type_id;
DROP TABLE survey_question_type;

CREATE TABLE survey_question_group (
	id int(10) NOT NULL auto_increment,
	starbar_id int(10) DEFAULT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sqg_sb_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE survey_question ADD COLUMN survey_question_group_id int(10) DEFAULT NULL;
ALTER TABLE survey_question ADD CONSTRAINT sq_sqg_id FOREIGN KEY (survey_question_group_id) REFERENCES survey_question_group (id) ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE survey_question_choice_group (
	id int(10) NOT NULL auto_increment,
	survey_question_group_id int(10) NOT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sqcg_sqg_id FOREIGN KEY (survey_question_group_id) REFERENCES survey_question_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE survey_question_choice ADD COLUMN survey_question_choice_group_id int(10) DEFAULT NULL;
ALTER TABLE survey_question_choice ADD CONSTRAINT sqc_sqcg_id FOREIGN KEY (survey_question_choice_group_id) REFERENCES survey_question_choice_group (id) ON DELETE SET NULL ON UPDATE CASCADE;


CREATE TABLE report_cell (
	id int(10) NOT NULL auto_increment,
	starbar_id int(10) DEFAULT NULL,
	title varchar(255) NOT NULL,
	number_of_users int(4) DEFAULT NULL,
	comma_delimited_list_of_users text DEFAULT NULL COMMENT "NULL means all users",
	PRIMARY KEY (id),
	CONSTRAINT rc_sb_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO report_cell (title, number_of_users) SELECT 'All Users' AS title, count(id) FROM user;


CREATE TABLE report_cell_and_condition (
	id int(10) NOT NULL auto_increment,
	report_cell_id int(10) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT rcac_rc_id FOREIGN KEY (report_cell_id) REFERENCES report_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE report_cell_or_condition (
	id int(10) NOT NULL auto_increment,
	report_cell_and_condition_id int(10) NOT NULL,
	survey_question_group_id int(10) DEFAULT NULL,
	survey_question_id int(10) DEFAULT NULL,
	data_type enum('choice', 'string', 'integer', 'decimal', 'monetary') NOT NULL,
	comparison_type enum('<', '>', '=', '<=', '>=', '!=', 'contains', 'does not contain') NOT NULL,
	survey_question_choice_group_id int(10) DEFAULT NULL,
	survey_question_choice_id int(10) DEFAULT NULL,
	compare_string VARCHAR(2000) DEFAULT NULL,
	compare_integer INT(10) DEFAULT NULL,
	compare_decimal NUMERIC(18, 6) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT rcoc_ac_id FOREIGN KEY (report_cell_and_condition_id) REFERENCES report_cell_and_condition (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcoc_sqg_id FOREIGN KEY (survey_question_group_id) REFERENCES survey_question_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcoc_sq_id FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcoc_sqcg_id FOREIGN KEY (survey_question_choice_group_id) REFERENCES survey_question_choice_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcoc_sqc_id FOREIGN KEY (survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE report_cell_survey (
	id int(10) NOT NULL auto_increment,
	report_cell_id int(10) NOT NULL,
	survey_id int(10) NOT NULL,
	number_of_responses int(4) DEFAULT NULL,
	comma_delimited_list_of_users text DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT rcs_rc_id FOREIGN KEY (report_cell_id) REFERENCES report_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcs_s_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE report_cell_survey_calculation (
	id int(10) NOT NULL auto_increment,
	report_cell_survey_id int(10) NOT NULL,
	survey_question_group_id int(10) DEFAULT NULL,
	survey_question_id int(10) DEFAULT NULL,
	survey_question_choice_group_id int(10) DEFAULT NULL,
	survey_question_choice_id int(10) DEFAULT NULL,
	parent_type enum('survey_question_group', 'survey_question', 'survey_question_choice_group', 'survey_question_choice') NOT NULL,
	number_of_responses int(4) DEFAULT NULL,
	comma_delimited_list_of_users text DEFAULT NULL,
	average NUMERIC(18, 6) DEFAULT NULL,
	median NUMERIC(18, 6) DEFAULT NULL,
	stardard_deviation NUMERIC(18, 6) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT rcsq_rcs_id FOREIGN KEY (report_cell_survey_id) REFERENCES report_cell_survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcsq_sqg_id FOREIGN KEY (survey_question_group_id) REFERENCES survey_question_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcsq_sq_id FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcsq_sqcg_id FOREIGN KEY (survey_question_choice_group_id) REFERENCES survey_question_choice_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcsq_sqc_id FOREIGN KEY (survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


