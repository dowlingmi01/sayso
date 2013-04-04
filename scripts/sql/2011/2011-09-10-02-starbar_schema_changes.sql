SET foreign_key_checks = 0;

CREATE TABLE starbar_user_map (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    active boolean COMMENT "Is this Starbar 'instance' activated for this user",
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY starbar_user_map_unique (user_id, starbar_id),
    CONSTRAINT starbar_user_map_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT starbar_user_map_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE external_user ADD domain varchar(64) AFTER last_name;

ALTER TABLE starbar ADD UNIQUE KEY starbar_unique (short_name);

SET foreign_key_checks = 1;
