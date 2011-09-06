SET foreign_key_checks = 0;

INSERT INTO starbar VALUES (1, 'hellomusic', 'Hello Music', null, 'Rocker', now(), now());

CREATE TABLE external_user (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL COMMENT "This may be null if the 'internal' user has not been created yet",
    external_id varchar(255) NOT NULL,
    starbar_id int(10) DEFAULT NULL,
    email varchar(255),
    username varchar(255),
    first_name varchar(64),
    last_name varchar(64),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY external_user_unique (external_id, starbar_id),
    CONSTRAINT external_user_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT external_user_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE survey (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL COMMENT "User who created the survey (not the respondent)",
    url varchar(255),
    origin enum('SurveyGizmo', 'Internal'),
    starbar_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY survey_unique (url),
    CONSTRAINT survey_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE survey_user_map (
    survey_id int(10) NOT NULL,
    user_id int(10) NOT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    UNIQUE KEY survey_user_map_unique (survey_id, user_id),
    CONSTRAINT survey_user_map_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT survey_user_map_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;