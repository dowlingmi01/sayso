DROP TABLE IF EXISTS metrics_tag_click_thru;
DROP TABLE IF EXISTS metrics_tag_view;
DROP TABLE IF EXISTS metrics_creative_click_thru;
DROP TABLE IF EXISTS metrics_creative_view;

CREATE TABLE metrics_tag_view ( 
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    cell_id int(10) DEFAULT NULL COMMENT "There may be multiple views (user/ad) recorded for each 'active' cell (study) for which the tag is referenced.",
    tag_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT metrics_tag_view_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE, /* cascade delete because deleting a user would probably not occur except for development and testing where removing their associated data is desired */
    CONSTRAINT metrics_tag_view_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_tag_view_cell_id FOREIGN KEY (cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_tag_view_tag_id FOREIGN KEY (tag_id) REFERENCES study_tag (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE metrics_tag_click_thru ( 
    id int(10) NOT NULL auto_increment,
    metrics_tag_view_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT metrics_tag_click_thru_metrics_tag_view_id FOREIGN KEY (metrics_tag_view_id) REFERENCES metrics_tag_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE metrics_creative_view ( 
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    cell_id int(10) DEFAULT NULL COMMENT "There may be multiple creatives (user/ad) recorded for each 'active' cell (study) for which the creative is referenced.",
    creative_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT metrics_creative_view_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE, 
    CONSTRAINT metrics_creative_view_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_creative_view_cell_id FOREIGN KEY (cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT metrics_creative_view_creative_id FOREIGN KEY (creative_id) REFERENCES study_creative (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE metrics_creative_click_thru ( 
    id int(10) NOT NULL auto_increment,
    metrics_creative_view_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT metrics_creative_click_thru_metrics_creative_view_id FOREIGN KEY (metrics_creative_view_id) REFERENCES metrics_creative_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE study_tag ADD target_url varchar(255) DEFAULT NULL AFTER tag;