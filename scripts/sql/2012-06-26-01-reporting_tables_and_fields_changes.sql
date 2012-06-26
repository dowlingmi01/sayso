ALTER TABLE survey_question DROP FOREIGN KEY sq_sqg_id;
ALTER TABLE survey_question DROP COLUMN survey_question_group_id;

ALTER TABLE survey_question_choice DROP FOREIGN KEY sqc_sqcg_id;
ALTER TABLE survey_question_choice DROP COLUMN survey_question_choice_group_id;

ALTER TABLE report_cell_survey_calculation DROP FOREIGN KEY rcsq_sqg_id;
ALTER TABLE report_cell_survey_calculation DROP COLUMN survey_question_group_id;

ALTER TABLE report_cell_survey_calculation DROP FOREIGN KEY rcsq_sqcg_id;
ALTER TABLE report_cell_survey_calculation DROP COLUMN survey_question_choice_group_id;

DROP TABLE report_cell_or_condition;
DROP TABLE report_cell_and_condition;

DROP TABLE survey_question_choice_group;
DROP TABLE survey_question_group;

ALTER TABLE report_cell DROP FOREIGN KEY rc_sb_id;
ALTER TABLE report_cell DROP COLUMN starbar_id;

ALTER TABLE report_cell ADD COLUMN conditions_processed tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE report_cell ADD COLUMN condition_type enum('and', 'or') NOT NULL;
UPDATE report_cell SET condition_type = 'and';
ALTER TABLE report_cell ADD COLUMN category enum('Internal', 'Custom', 'Panel', 'Gender', 'Age Range', 'Marital Status', 'Education', 'Ethnicity', 'Income', 'Parental Status') NOT NULL DEFAULT 'Internal';

CREATE TABLE report_cell_user_condition (
	id int(10) NOT NULL auto_increment,
	report_cell_id int(10) NOT NULL,
	condition_type enum('choice', 'string', 'integer', 'decimal', 'monetary', 'starbar', 'report_cell') NOT NULL,
	comparison_type enum('<', '>', '=', '<=', '>=', '!=', 'contains', 'does not contain') NOT NULL,
	compare_report_cell_id int(10) DEFAULT NULL,
	compare_starbar_id int(10) DEFAULT NULL,
	compare_survey_question_id int(10) DEFAULT NULL,
	compare_survey_question_choice_id int(10) DEFAULT NULL,
	compare_string VARCHAR(2000) DEFAULT NULL,
	compare_integer INT(10) DEFAULT NULL,
	compare_decimal NUMERIC(18, 6) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT rcuc_rc_id FOREIGN KEY (report_cell_id) REFERENCES report_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcuc_crc_id FOREIGN KEY (compare_report_cell_id) REFERENCES report_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcuc_cs_id FOREIGN KEY (compare_starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcuc_csq_id FOREIGN KEY (compare_survey_question_id) REFERENCES survey_question (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcuc_csqcg_id FOREIGN KEY (compare_survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE survey ADD COLUMN last_response timestamp DEFAULT CURRENT_TIMESTAMP;
UPDATE survey set last_response = now();
ALTER TABLE report_cell_survey ADD COLUMN last_processed timestamp DEFAULT '0000-00-00 00:00:00';
