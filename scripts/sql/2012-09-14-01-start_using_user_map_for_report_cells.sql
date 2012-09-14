CREATE TABLE report_cell_user_map (
	report_cell_id int(10) NOT NULL,
	user_id int(10) NOT NULL,
	PRIMARY KEY (report_cell_id, user_id),
	CONSTRAINT rcum_rc_id FOREIGN KEY (report_cell_id) REFERENCES report_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT rcum_u_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX report_cell_user_map_report_cell_id ON report_cell_user_map (report_cell_id);

INSERT INTO report_cell_user_map (report_cell_id, user_id) VALUES (1, 1);

ALTER TABLE report_cell DROP COLUMN comma_delimited_list_of_users;
ALTER TABLE report_cell_survey DROP COLUMN comma_delimited_list_of_users;
ALTER TABLE report_cell_survey_calculation DROP COLUMN comma_delimited_list_of_users;
