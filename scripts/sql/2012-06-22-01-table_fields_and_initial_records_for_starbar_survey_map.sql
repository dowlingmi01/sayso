CREATE TABLE starbar_survey_map (
	id int(10) NOT NULL auto_increment,
	survey_id int(10) NOT NULL,
	starbar_id int(10) NOT NULL,
	start_after int(10) DEFAULT NULL COMMENT "Minimum number of seconds after a user joins before they could see this survey/poll on this starbar",
	start_at timestamp DEFAULT '0000-00-00 00:00:00',
	end_at timestamp DEFAULT '0000-00-00 00:00:00',
	start_day int(4) DEFAULT NULL,
	ordinal int(4) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT ssm_su_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT ssm_st_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO starbar_survey_map (survey_id, starbar_id, start_after, start_at, end_at, start_day, ordinal)
SELECT id, starbar_id, start_after, start_at, end_at, start_day, ordinal FROM survey;
