SET foreign_key_checks = 0;


CREATE TABLE notification_message_group (
    id int(10) NOT NULL auto_increment,
    short_name varchar(20) DEFAULT NULL COMMENT "To reference in code if necessary",
    user_id int(10) DEFAULT NULL COMMENT "User_id of message group CREATOR",
    starbar_id int(10) DEFAULT NULL,
    repeats int(1) DEFAULT NULL,
    type enum('User Actions', 'Scheduled'),
    minimum_interval int(10) DEFAULT NULL,
    start_after int(10) DEFAULT NULL COMMENT "Minimum number of seconds after a user joins before they would see messages in thie group",
    start_at timestamp DEFAULT '0000-00-00 00:00:00',
    end_at timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT notifmg_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT notifmg_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (short_name),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE notification_message (
    id int(10) NOT NULL auto_increment,
    short_name varchar(20) DEFAULT NULL COMMENT "To reference in code if necessary",
    user_id int(10) DEFAULT NULL COMMENT "User_id of message CREATOR",
    survey_id int(10) DEFAULT NULL,
    notification_message_group_id int(10) DEFAULT NULL,
    ordinal int(4) DEFAULT NULL,
    message varchar(200) DEFAULT NULL,
    validate enum('Facebook Connect', 'Twitter Connect', 'Take Survey'),
    notification_area enum('alerts', 'promos'),
    popBoxToOpen varchar(20) COMMENT "E.g. 'polls', 'surveys', 'user-level', etc.",
    PRIMARY KEY (id),
    CONSTRAINT notifm_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT notifm_notification_message_group_id FOREIGN KEY (notification_message_group_id) REFERENCES notification_message_group (id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (short_name),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE notification_message_user_map (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL COMMENT "User to receive/who has received notification",
    notification_message_id int(10) DEFAULT NULL,
    notified timestamp DEFAULT '0000-00-00 00:00:00',
    closed timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT notifmum_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT notifmum_notification_message_id FOREIGN KEY (notification_message_id) REFERENCES notification_message (id) ON DELETE CASCADE ON UPDATE CASCADE,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


SET foreign_key_checks = 1;
