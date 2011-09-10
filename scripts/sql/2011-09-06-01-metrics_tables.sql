SET foreign_key_checks = 0;

CREATE TABLE metrics_page_view (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    url text,
    PRIMARY KEY (id),
    CONSTRAINT metrics_page_view_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_page_view_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE metrics_search (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    search_engine_id int(10) DEFAULT NULL,
    query text,
    PRIMARY KEY (id),
    CONSTRAINT metrics_search_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_search_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_search_search_engine_id FOREIGN KEY (search_engine_id) REFERENCES lookup_search_engines (id) ON DELETE CASCADE ON UPDATE CASCADE,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE metrics_social_activity (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    social_activity_type_id int(10) DEFAULT NULL,
    url text,
    content text,
    PRIMARY KEY (id),
    CONSTRAINT metrics_social_activity_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_social_activity_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_social_activity_social_activity_type_id FOREIGN KEY (social_activity_type_id) REFERENCES lookup_social_activity_type (id) ON DELETE CASCADE ON UPDATE CASCADE,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;
