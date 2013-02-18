CREATE TABLE starbar_content_key (
	id int(10) NOT NULL auto_increment,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT unique_starbar_content_key_title UNIQUE (title),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS starbar_content;

CREATE TABLE starbar_content (
	id int(10) NOT NULL auto_increment,
	starbar_id int(10) DEFAULT NULL,
	starbar_content_key_id int(10) NOT NULL,
	content text DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT unique_starbar_content_starbar_and_key_title UNIQUE (starbar_id, starbar_content_key_id),
	CONSTRAINT sc_s_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT sc_sck_id FOREIGN KEY (starbar_content_key_id) REFERENCES starbar_content_key (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
