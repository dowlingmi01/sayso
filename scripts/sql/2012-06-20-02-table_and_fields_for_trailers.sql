CREATE TABLE survey_trailer_info (
	id int(10) NOT NULL auto_increment,
	survey_id int(10) NOT NULL,
	category enum('retro', 'in release', 'test trailer') DEFAULT 'retro',
	video_key varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT sti_s_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE survey CHANGE COLUMN type type enum('survey', 'poll', 'quiz', 'trailer') NOT NULL;

