CREATE TABLE user_gaming_transaction_history (
	id int(10) NOT NULL auto_increment,
	user_gaming_id int(10) NOT NULL,
	status enum('successful', 'failed') NOT NULL DEFAULT 'successful',
	action VARCHAR(255),
	action_on_id int(10) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT ugth_ug_id FOREIGN KEY (user_gaming_id) REFERENCES user_gaming (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
