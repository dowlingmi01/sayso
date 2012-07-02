CREATE TABLE user_gaming_order_history (
	id int(10) NOT NULL auto_increment,
	user_gaming_id int(10) NOT NULL,
	status enum('successful', 'failed') NOT NULL DEFAULT 'successful',
	first_name VARCHAR(255) DEFAULT NULL,
	last_name VARCHAR(255) DEFAULT NULL,
	street1 VARCHAR(255) DEFAULT NULL,
	street2 VARCHAR(255) DEFAULT NULL,
	locality VARCHAR(255) DEFAULT NULL,
	region VARCHAR(255) DEFAULT NULL,
	postalCode VARCHAR(255) DEFAULT NULL,
	country VARCHAR(255) DEFAULT NULL,
	phone VARCHAR(255) DEFAULT NULL,
	good_id int(10) NOT NULL,
	quantity int(4) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT ugoh_ug_id FOREIGN KEY (user_gaming_id) REFERENCES user_gaming (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;