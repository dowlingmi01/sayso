SET foreign_key_checks = 0;

/**
 * User tables
 * 
 */
DROP TABLE IF EXISTS `user`; /* NOTE: "DROP TABLE IF EXISTS" on an empty DB generates warnings */

CREATE TABLE `user` (
    id int(10) NOT NULL auto_increment,
    username varchar(100),
    password varchar(32),
    password_salt varchar(32),
    first_name varchar(64),
    last_name varchar(64),
    gender_id int(10),
    ethnicity_id int(10) DEFAULT NULL,
    income_range_id int(10) DEFAULT NULL,
    birthdate date COMMENT "Use this in relation to lookup_age_range",
    url varchar(100),
    timezone varchar(16),
    primary_email_id int(10),
    primary_phone_id int(10),
    primary_address_id int(10),
    user_role_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_user_role_id FOREIGN KEY (user_role_id) REFERENCES user_role (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_gender_id FOREIGN KEY (gender_id) REFERENCES lookup_gender (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_ethnicity_id FOREIGN KEY (ethnicity_id) REFERENCES lookup_ethnicity (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_income_range_id FOREIGN KEY (income_range_id) REFERENCES lookup_income_range (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_primary_email_id FOREIGN KEY (primary_email_id) REFERENCES user_email (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_primary_phone_id FOREIGN KEY (primary_phone_id) REFERENCES user_phone (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT user_primary_address_id FOREIGN KEY (primary_address_id) REFERENCES user_address (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  
DROP TABLE IF EXISTS user_child;
DROP TABLE IF EXISTS user_social;
DROP TABLE IF EXISTS user_email;
DROP TABLE IF EXISTS user_address;
DROP TABLE IF EXISTS user_phone;
DROP TABLE IF EXISTS user_avatar;
DROP TABLE IF EXISTS user_role; 
DROP TABLE IF EXISTS starbar;
DROP TABLE IF EXISTS starbar_content;
DROP TABLE IF EXISTS lookup_gender;
DROP TABLE IF EXISTS lookup_ethnicity;
DROP TABLE IF EXISTS lookup_income_range;
DROP TABLE IF EXISTS lookup_age_range;
DROP TABLE IF EXISTS lookup_quota_percentile;
DROP TABLE IF EXISTS lookup_survey_type;
DROP TABLE IF EXISTS lookup_poll_frequency;
DROP TABLE IF EXISTS lookup_email_frequency;
DROP TABLE IF EXISTS lookup_search_engines;
DROP TABLE IF EXISTS lookup_social_activity_type;
DROP TABLE IF EXISTS lookup_timeframe;
DROP TABLE IF EXISTS preference_general;
DROP TABLE IF EXISTS preference_survey_type;
DROP TABLE IF EXISTS study_domain;
DROP TABLE IF EXISTS study_tag;
DROP TABLE IF EXISTS study_tag_domain_map;
DROP TABLE IF EXISTS study;
DROP TABLE IF EXISTS study_search_engines_map;
DROP TABLE IF EXISTS study_social_activity_type_map; 
DROP TABLE IF EXISTS study_quota;
DROP TABLE IF EXISTS study_survey;
DROP TABLE IF EXISTS study_survey_criterion;
DROP TABLE IF EXISTS study_cell;
DROP TABLE IF EXISTS study_cell_tag_map;
DROP TABLE IF EXISTS study_cell_qualifier_browsing;
DROP TABLE IF EXISTS study_cell_qualifier_search;

SET foreign_key_checks = 1;

CREATE TABLE user_child (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    name varchar(32) DEFAULT NULL,
    birthdate date DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_child_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE user_email (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    email varchar(255),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_email_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE user_phone (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    phone varchar(100),
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT user_phone_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE user_role (
    id int(10) NOT NULL,
    short_name varchar(32) NOT NULL,
    label varchar(32),
    description varchar(255),
    parent_id int(10) DEFAULT NULL,
    ordinal int(3) NOT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * Starbar
 * 
 */


CREATE TABLE starbar (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255),
    user_pseudonym varchar(32), /* single tense i.e. "Little Monster", "Stalker", "Follower" */
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE starbar_content (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    starbar_id int(10) DEFAULT NULL,
    name varchar(64),
    content text,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    /* we probably do not want content to go away if a user is deleted */
    CONSTRAINT starbar_content_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT starbar_content_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * Lookup tables
 * 
 */

CREATE TABLE lookup_gender (
    id int(10) NOT NULL auto_increment,
    short_name varchar(32) NOT NULL,
    label varchar(32),
    description varchar(255),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_ethnicity (
    id int(10) NOT NULL auto_increment,
    short_name varchar(32) NOT NULL,
    label varchar(32),
    description varchar(255),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_income_range (
    id int(10) NOT NULL auto_increment,
    income_from int(10),
    income_to int(10),
    ordinal int(10),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    
CREATE TABLE lookup_age_range (
    id int(10) NOT NULL auto_increment,
    age_from int(10),
    age_to int(10),
    ordinal int(10),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_quota_percentile (
    id int(10) NOT NULL auto_increment,
    quota int(10),
    quarter boolean COMMENT "true for 25,50,75,100",
    ordinal int(10),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_survey_type (
    id int(10) NOT NULL,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255),
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_poll_frequency (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255),
    extra varchar(255), 
    default_frequency boolean,
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_email_frequency (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255),
    extra varchar(255), 
    default_frequency boolean,
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_search_engines (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255) DEFAULT NULL,
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_social_activity_type (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    description varchar(255) DEFAULT NULL,    
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE lookup_timeframe (
    id int(10) NOT NULL auto_increment,
    short_name varchar(100) NOT NULL,
    label varchar(100),
    seconds varchar(100),
    description varchar(255) DEFAULT NULL,    
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
    CONSTRAINT preference_general_poll_frequency_id FOREIGN KEY (poll_frequency_id) REFERENCES lookup_poll_frequency (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT preference_general_email_frequency_id FOREIGN KEY (email_frequency_id) REFERENCES lookup_email_frequency (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE preference_survey_type (
    id int(10) NOT NULL auto_increment,
    survey_type_id int(10) DEFAULT NULL,
    user_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT preference_survey_type_survey_type_id FOREIGN KEY (survey_type_id) REFERENCES lookup_survey_type (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT preference_survey_type_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/**
 * Study
 * NOTE: user_id for these tables is the user who setup the study
 */

CREATE TABLE study_domain (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    domain varchar(100) NOT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    UNIQUE KEY domain_unique (domain),
    CONSTRAINT domain_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE study_tag (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    name varchar(100) NOT NULL COMMENT "label",
    content text COMMENT "tag",
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT tag_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE study_tag_domain_map ( 
    tag_id int(10) NOT NULL,
    domain_id int(10) NOT NULL,
    UNIQUE KEY tag_domain_map_unique (domain_id, tag_id),
    CONSTRAINT tag_domain_map_tag_id FOREIGN KEY (tag_id) REFERENCES study_tag (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT tag_domain_map_domain_id FOREIGN KEY (domain_id) REFERENCES study_domain (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,
    name varchar(100) DEFAULT NULL,
    description varchar(255) DEFAULT NULL,
    size int(10) DEFAULT NULL,
    size_minimum int(10) DEFAULT NULL,
    begin_date datetime DEFAULT NULL,
    end_date datetime DEFAULT NULL,
    click_track boolean,
    /* is_survey boolean, s/not be nec. just check study_survey */
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_search_engines_map (
    study_id int(10) NOT NULL,
    lookup_search_engines_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_id, lookup_search_engines_id),
    CONSTRAINT search_engine_map_study FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT search_engine_map_search_engine FOREIGN KEY (lookup_search_engines_id) REFERENCES lookup_search_engines (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_social_activity_type_map (
    study_id int(10) NOT NULL,
    lookup_social_activity_type_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_id, lookup_social_activity_type_id),
    CONSTRAINT social_activity_map_study FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT social_activity_map_social_activity FOREIGN KEY (lookup_social_activity_type_id) REFERENCES lookup_social_activity_type (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_quota (
    id int(10) NOT NULL auto_increment,
    study_id int(10) DEFAULT NULL,
    percentile_id int(10) DEFAULT NULL,
    gender_id int(10) DEFAULT NULL,
    age_range_id int(10) DEFAULT NULL,
    ethnicity_id int(10) DEFAULT NULL,
    income_range_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_quota_study_id FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_quota_percentile_id FOREIGN KEY (percentile_id) REFERENCES lookup_quota_percentile (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT study_quota_gender_id FOREIGN KEY (gender_id) REFERENCES lookup_gender (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT study_quota_age_range_id FOREIGN KEY (age_range_id) REFERENCES lookup_age_range (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT study_quota_ethnicity_id FOREIGN KEY (ethnicity_id) REFERENCES lookup_ethnicity (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT study_quota_income_range_id FOREIGN KEY (income_range_id) REFERENCES lookup_income_range (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_survey (
    id int(10) NOT NULL auto_increment,
    study_id int(10) DEFAULT NULL,
    url varchar(255) COMMENT "URL to the iframe content",
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_survey_study_id FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_survey_criterion (
    id int(10) NOT NULL auto_increment,
    study_survey_id int(10) DEFAULT NULL,
    site varchar(255) COMMENT "domain",
    timeframe_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_survey_criterion_study_survey_id FOREIGN KEY (study_survey_id) REFERENCES study_survey (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_survey_criterion_timeframe_id FOREIGN KEY (timeframe_id) REFERENCES lookup_timeframe (id) ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_cell (
    id int(10) NOT NULL auto_increment,
    study_id int(10) DEFAULT NULL,
    size int(10) DEFAULT NULL,
    cell_type enum('test', 'control') DEFAULT 'test' COMMENT "control means all ads associated are generic and not part of the study",
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_cell_study_id FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8; 

CREATE TABLE study_cell_tag_map (
    cell_id int(10) NOT NULL,
    tag_id int(10) NOT NULL,
    CONSTRAINT study_cell_tag_map_cell_id FOREIGN KEY (cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_cell_tag_map_tag_id FOREIGN KEY (tag_id) REFERENCES study_tag (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_cell_qualifier_browsing (
    id int(10) NOT NULL auto_increment,
    cell_id int(10) DEFAULT NULL,
    exclude boolean,
    site varchar(255),
    timeframe_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_cell_qualifier_browsing_cell_id FOREIGN KEY (cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_cell_qualifier_browsing_timeframe_id FOREIGN KEY (timeframe_id) REFERENCES lookup_timeframe (id) ON DELETE SET NULL ON UPDATE CASCADE    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_cell_qualifier_search (
    id int(10) NOT NULL auto_increment,
    cell_id int(10) DEFAULT NULL,
    exclude boolean,
    term varchar(255) COMMENT "search term/query",
    search_engine_id int(10) DEFAULT NULL,
    timeframe_id int(10) DEFAULT NULL,
    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id),
    CONSTRAINT study_cell_qualifier_search_cell_id FOREIGN KEY (cell_id) REFERENCES study_cell (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT study_cell_qualifier_search_timeframe_id FOREIGN KEY (timeframe_id) REFERENCES lookup_timeframe (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT study_cell_qualifier_search_search_engine_id FOREIGN KEY (search_engine_id) REFERENCES lookup_search_engines (id) ON DELETE SET NULL ON UPDATE CASCADE    
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*
DROP TABLE IF EXISTS

CREATE TABLE name (
    id int(10) NOT NULL auto_increment,
    user_id int(10) DEFAULT NULL,

    created timestamp DEFAULT '0000-00-00 00:00:00',
    modified timestamp DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO lookup_foo (cols, ) VALUES 
    (1, ''),
    (2, '');
    
*/