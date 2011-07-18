DROP TABLE IF EXISTS user_role;

CREATE TABLE user_role (
    id int(10) NOT NULL,
    name varchar(32),
    description varchar(255),
    parent_id int(10) DEFAULT NULL,
    ordinal int(3) NOT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * User tables
 * 
 */
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
    id int(10) NOT NULL auto_increment,
    username varchar(100),
    password varchar(32),
    password_salt varchar(32),
    first_name varchar(64),
    last_name varchar(64),
    gender enum('male', 'female', 'unspecified') DEFAULT 'unspecified',
    ethnicity enum('white', 'african-american', 'asian', 'latino', 'native-american', 'hawaiin-pacific-islander', 'unspecified') DEFAULT 'unspecified',
    birthdate date,
    url varchar(100),
    primary_email int(10),
    primary_phone int(10),
    primary_address int(10),
    user_role_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_user_role_id FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO 
    user_role (id, name, description, ordinal, parent_id, created) 
VALUES 
    (1, 'guests', 'Guest role (not actually a user)', 10, null, now()), 
    (2, 'users', 'Basic user role', 20, 1, now()), 
    (3, 'moderators', 'User role with added privilege of moderation within a site', 30, 2, now()), 
    (4, 'site_admins', 'Moderator role with added privilege of administering users/moderators within a site', 40, 3, now()), 
    (5, 'group_admins', 'Administrator role with added privilege of administering site groups', 50, 4, now()), 
    (6, 'root_admins', 'Administrator with "root" privileges across all sites', 60, 5, now());
   
DROP TABLE IF EXISTS user_social;

CREATE TABLE user_social (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    provider enum('facebook', 'twitter'),
    identifier varchar(255),
    username varchar(100),
    /* not sure about other fields on this */
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_social_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS user_email;

CREATE TABLE user_email (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    email varchar(255),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_email_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS user_address;

CREATE TABLE user_address (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    street varchar(100),
    locality varchar(64) COMMENT "city",
    region varchar(64) COMMENT "state/province",
    postalCode varchar(64) COMMENT "zip",
    country varchar(64),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_address_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS user_phone;

CREATE TABLE user_phone (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    phone varchar(100),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_phone_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS user_avatar;

CREATE TABLE user_avatar (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    data mediumblob,
    file_type enum('jpg','png','gif') DEFAULT 'jpg',
    file_size int(6) DEFAULT NULL,
    width int(4) DEFAULT NULL,
    height int(4) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_avatar_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS starbar;

/**
 * Starbar
 */
CREATE TABLE starbar (
    id int(10) NOT NULL auto_increment,
    name varchar(100) NOT NULL,
    description varchar(255),
    user_pseudonym varchar(32), /* single tense i.e. "Little Monster", "Stalker", "Follower" */
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS content;

/**
 * Dynamic textual and/or HTML content
 */
CREATE TABLE content (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    name varchar(64),
    content text,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    /* we probably do not want content to go away if a user is deleted */
    CONSTRAINT content_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT content_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS survey_type;

/**
 * Lookup tables
 */
CREATE TABLE survey_type (
    id int(10) NOT NULL,
    name varchar(100) NOT NULL,
    description varchar(255),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO survey_type (id, name) VALUES 
    (1, 'technology'),
    (2, 'food'),
    (3, 'religion'),
    (4, 'news'),
    (5, 'celebrities'),
    (6, 'politics'),
    (7, 'sports'),
    (8, 'household'),
    (9, 'television');

DROP TABLE IF EXISTS poll_frequency;

CREATE TABLE poll_frequency (
    id int(10) NOT NULL auto_increment,
    name varchar(100) NOT NULL,
    description varchar(255),
    default_frequency boolean,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO poll_frequency (id, name, default_frequency) VALUES 
    (1, 'Often - earn the most Pay.So!', false),
    (2, 'Occasionally - earn a little Pay.So', true),
    (3, 'Never - no Pay.So :(', false);

DROP TABLE IF EXISTS email_frequency;

CREATE TABLE email_frequency (
    id int(10) NOT NULL auto_increment,
    name varchar(100) NOT NULL,
    description varchar(255),
    default_frequency boolean,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO email_frequency (id, name, default_frequency) VALUES 
    (1, 'Often - earn the most Pay.So!', false),
    (2, 'Occasionally - earn a little Pay.So', true),
    (3, 'Never - no Pay.So :(', false);

DROP TABLE IF EXISTS preference_general;

/**
 * Preferences
 * 
 */
CREATE TABLE preference_general (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    poll_frequency_id int(10) DEFAULT NULL,
    email_frequency_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT preference_general_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT preference_general_poll_frequency_id FOREIGN KEY (poll_frequency_id) REFERENCES poll_frequency (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT preference_general_email_frequency_id FOREIGN KEY (email_frequency_id) REFERENCES email_frequency (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS preference_survey_type;

CREATE TABLE preference_survey_type (
    id int(10) NOT NULL auto_increment,
    survey_type_id int(10) DEFAULT NULL,
    user_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT preference_survey_type_survey_type_id FOREIGN KEY (survey_type_id) REFERENCES `survey_type` (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT preference_survey_type_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*
CREATE TABLE name (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,

    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/