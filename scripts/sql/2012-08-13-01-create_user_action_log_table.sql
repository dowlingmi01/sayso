CREATE TABLE user_action_log (
	id int(10) NOT NULL auto_increment,
	user_id int(10) DEFAULT NULL,
	starbar_id int(10) DEFAULT NULL,
	survey_id int(10) DEFAULT NULL,
	good_id int(10) DEFAULT NULL,
	action VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY (id),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
