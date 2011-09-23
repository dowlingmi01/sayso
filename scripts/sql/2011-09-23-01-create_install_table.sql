SET foreign_key_checks = 0;

CREATE TABLE external_user_install (
    id int(10) NOT NULL auto_increment,
    external_user_id int(10) DEFAULT NULL,
    token varchar(64) NOT NULL,
    ip_address varchar(255) NOT NULL,
    user_agent varchar(255) NOT NULL,
    begin_time datetime NOT NULL,
    completed_time datetime NOT NULL COMMENT "This is not exact",
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT external_user_install_external_user_id FOREIGN KEY (external_user_id) REFERENCES external_user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;
