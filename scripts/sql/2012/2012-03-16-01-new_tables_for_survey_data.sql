ALTER TABLE survey ADD COLUMN display_number_of_questions varchar(100) DEFAULT NULL COMMENT "Number of questions to display -- can be a range, e.g. '45-50'" AFTER number_of_questions;
UPDATE survey SET display_number_of_questions = number_of_questions;

CREATE TABLE survey_question_type (
	id int(10) NOT NULL auto_increment,
	starbar_id int(10) DEFAULT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sqt_sb_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE survey_question (
	id int(10) NOT NULL auto_increment,
	survey_id int(10) NOT NULL,
	survey_question_type_id int(10) DEFAULT NULL,
	data_type enum('none', 'string', 'integer', 'decimal', 'monetary') NOT NULL DEFAULT 'none',
	choice_type enum('none', 'single', 'multiple') NOT NULL DEFAULT 'none',
	title varchar(2000) NOT NULL,
	number_of_choices int(4) DEFAULT NULL,
	ordinal int(4) DEFAULT NULL,
	external_question_id int(10) DEFAULT NULL,
	external_pipe_choice_id int(10) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sq_s_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT sq_sqt_id FOREIGN KEY (survey_question_type_id) REFERENCES survey_question_type (id) ON DELETE SET NULL ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE survey_question_choice (
	id int(10) NOT NULL auto_increment,
	survey_question_id int(10) NOT NULL,
	title varchar(2000) NOT NULL,
	value varchar(2000) NOT NULL,
	other tinyint(1) DEFAULT NULL,
	ordinal int(4) DEFAULT NULL,
	external_choice_id int(10) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sqc_sq_id FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE survey_question ADD COLUMN piped_from_survey_question_id int(10) DEFAULT NULL COMMENT "Used only if question is piped from another multiple choice question" AFTER survey_question_type_id;
ALTER TABLE survey_question ADD CONSTRAINT sq_pfsq_id FOREIGN KEY (piped_from_survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE survey_question ADD COLUMN piped_from_survey_question_choice_id int(10) DEFAULT NULL COMMENT "Used only if question is piped from another multiple choice question"  AFTER piped_from_survey_question_id;
ALTER TABLE survey_question ADD CONSTRAINT sq_pfsqc_id FOREIGN KEY (piped_from_survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE CASCADE ON UPDATE CASCADE;

/* This table is now re-dedicated to Sergio! */
CREATE TABLE survey_response (
	id int(10) NOT NULL auto_increment,
	survey_id int(10) NOT NULL,
	user_id int(10) NOT NULL,
	external_response_id int(10) DEFAULT NULL COMMENT "Response ID on SurveyGizmo, for example",
	status enum('completed','archived','new','disqualified') NOT NULL DEFAULT 'new',
	processing_status enum ('completed', 'pending') NOT NULL DEFAULT 'pending',
	PRIMARY KEY (id),
	completed_disqualified timestamp DEFAULT '0000-00-00 00:00:00',
	data_download timestamp DEFAULT '0000-00-00 00:00:00',
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO survey_response (survey_id, user_id, status, created)
	SELECT survey_id, user_id, status, created FROM survey_user_map;

CREATE TABLE survey_question_response (
	id int(10) NOT NULL auto_increment,
	survey_response_id int(10) NOT NULL,
	survey_question_id int(10) NOT NULL,
	data_type enum('choice', 'string', 'integer', 'decimal', 'monetary') NOT NULL DEFAULT 'choice',
	survey_question_choice_id int(10) DEFAULT NULL,
	response_string VARCHAR(2000) DEFAULT NULL,
	response_integer INT(10) DEFAULT NULL,
	response_decimal NUMERIC(18, 6) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT src_sr_id FOREIGN KEY (survey_response_id) REFERENCES survey_response (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT src_sq_id FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT src_sqc_id FOREIGN KEY (survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
