/* browsers (unique) */
CREATE TABLE browser (
	id int NOT NULL auto_increment,
	browser_type enum('FireFox', 'Internet Explorer', 'Chrome', 'Safari', 'Other') NOT NULL DEFAULT 'Other',
	major_version smallint,
	version varchar(255),
	agent_string varchar(2000),
	PRIMARY KEY (id),
	created timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/* user session (not unique, new one started every time a user authenticates, user_session_id sent to client at that time, then user_session_id (or user_key, see below) is sent by the client with every request) */
CREATE TABLE user_session (
	id int NOT NULL auto_increment,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	user_id int NOT NULL,
	browser_id int NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT us_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT us_browser_id FOREIGN KEY (browser_id) REFERENCES browser (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/* hostname, paths and parameters (GET variable names) (unique) */
CREATE TABLE hostname (
	id int NOT NULL auto_increment,
	hostname varchar(255) NOT NULL,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT hostname_unique UNIQUE (hostname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP PROCEDURE IF EXISTS proc_get_hostname_id //
CREATE PROCEDURE proc_get_hostname_id (passed_hostname VARCHAR(255), OUT out_hostname_id int)
	BEGIN
		SELECT id
			INTO out_hostname_id
			FROM hostname
			WHERE hostname.hostname = passed_hostname;

		IF (out_hostname_id IS NULL AND passed_hostname IS NOT NULL AND passed_hostname <> '') THEN
			INSERT INTO hostname (hostname) VALUES (passed_hostname);
        	/*SELECT LAST_INSERT_ID() INTO hostname_id;*/
        	SET out_hostname_id = LAST_INSERT_ID();
		END IF;
	END //
DELIMITER ;



CREATE TABLE hostname_path (
	id int NOT NULL auto_increment,
	hostname_id int NOT NULL,
	path varchar(255) DEFAULT NULL,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT hpath_hostname_id FOREIGN KEY (hostname_id) REFERENCES hostname (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT hpath_unique UNIQUE (hostname_id, path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP PROCEDURE IF EXISTS proc_get_hostname_path_id //
CREATE PROCEDURE proc_get_hostname_path_id (passed_hostname_id INT, passed_hostname_path VARCHAR(255), OUT out_hostname_path_id INT)
	BEGIN
		IF (passed_hostname_path = '' OR passed_hostname_path IS NULL) THEN
			SET passed_hostname_path = NULL;
			SELECT id
				INTO out_hostname_path_id
				FROM hostname_path
				WHERE hostname_id = passed_hostname_id
					AND path IS NULL;
		ELSE
			SELECT id
				INTO out_hostname_path_id
				FROM hostname_path
				WHERE hostname_id = passed_hostname_id
					AND path = passed_hostname_path;
		END IF;

		IF (out_hostname_path_id IS NULL AND passed_hostname_id IS NOT NULL AND passed_hostname_id <> 0) THEN
			INSERT INTO hostname_path (hostname_id, path) VALUES (passed_hostname_id, passed_hostname_path);
        	/*SELECT LAST_INSERT_ID() INTO hostname_id;*/
        	SET out_hostname_path_id = LAST_INSERT_ID();
		END IF;
	END //
DELIMITER ;



CREATE TABLE hostname_parameter (
	id int NOT NULL auto_increment,
	hostname_id int NOT NULL,
	title varchar(255) DEFAULT NULL,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT hparam_hostname_id FOREIGN KEY (hostname_id) REFERENCES hostname (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT hparam_unique UNIQUE (hostname_id, title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP FUNCTION IF EXISTS func_get_hostname_parameter_id //
CREATE FUNCTION func_get_hostname_parameter_id (passed_hostname_id INT, passed_hostname_parameter VARCHAR(255))
	RETURNS INT
	DETERMINISTIC
	READS SQL DATA
	MODIFIES SQL DATA
	BEGIN
		DECLARE out_hostname_parameter_id INT;
		SELECT id
			INTO out_hostname_parameter_id
			FROM hostname_parameter
			WHERE hostname_id = passed_hostname_id
				AND title = passed_hostname_parameter;

		IF (out_hostname_parameter_id IS NULL AND passed_hostname_id IS NOT NULL AND passed_hostname_id <> 0 AND passed_hostname_parameter IS NOT NULL AND passed_hostname_parameter <> '') THEN
			INSERT INTO hostname_parameter (hostname_id, title) VALUES (passed_hostname_id, passed_hostname_parameter);
        	SET out_hostname_parameter_id = LAST_INSERT_ID();
		END IF;

		RETURN out_hostname_parameter_id;
	END //
DELIMITER ;



/* urls, not unique */
CREATE TABLE log_url (
	id int NOT NULL auto_increment,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	hostname_id int NOT NULL,
	hostname_path_id int NOT NULL,
	protocol enum ('http', 'https'),
	PRIMARY KEY (id),
	CONSTRAINT lu_hostname_id FOREIGN KEY (hostname_id) REFERENCES hostname (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lu_hostname_path_id FOREIGN KEY (hostname_path_id) REFERENCES hostname_path (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP PROCEDURE IF EXISTS proc_add_log_url_no_parameters //
CREATE PROCEDURE proc_add_log_url_no_parameters (passed_protocol ENUM('http', 'https'), passed_hostname VARCHAR(255), passed_hostname_path VARCHAR(255), OUT out_log_url_id INT, OUT out_hostname_id INT, OUT out_hostname_path_id INT)
	BEGIN
		CALL proc_get_hostname_id(passed_hostname, out_hostname_id);
		CALL proc_get_hostname_path_id(out_hostname_id, passed_hostname_path, out_hostname_path_id);
		INSERT INTO log_url (hostname_id, hostname_path_id, protocol) VALUES (out_hostname_id, out_hostname_path_id, passed_protocol);
		SET out_log_url_id = LAST_INSERT_ID();
		SELECT out_log_url_id AS 'log_url_id', out_hostname_id AS 'hostname_id', out_hostname_path_id AS 'hostname_path_id'; /* need a select for php */
	END //
DELIMITER ;



CREATE TABLE log_url_parameter (
	id int NOT NULL auto_increment,
	log_url_id int NOT NULL,
	hostname_parameter_id int NOT NULL,
	value varchar(2000) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lup_log_url_id FOREIGN KEY (log_url_id) REFERENCES log_url (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lup_hostname_parameter_id FOREIGN KEY (hostname_parameter_id) REFERENCES hostname_parameter (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/* PAGE VIEW EVENT (not unique) */
CREATE TABLE log_event_page_view (
	id int NOT NULL auto_increment,
	created_hour mediumint UNSIGNED,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	user_id int NOT NULL,
	user_session_id int NOT NULL,
	log_url_id int NOT NULL,
	top_log_event_page_view_id int DEFAULT NULL,
	parent_log_event_page_view_id int DEFAULT NULL,
	time_on_page smallint DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lepv_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lepv_user_session_id FOREIGN KEY (user_session_id) REFERENCES user_session (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lepv_log_url_id FOREIGN KEY (log_url_id) REFERENCES log_url (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lepv_top_log_event_page_view_id FOREIGN KEY (top_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lepv_parent_log_event_page_view_id FOREIGN KEY (parent_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX lepv_created_hour ON log_event_page_view (created_hour);

DROP TRIGGER IF EXISTS `before_insert_log_event_page_view`;
DELIMITER //
CREATE TRIGGER `before_insert_log_event_page_view` BEFORE INSERT ON `log_event_page_view`
	FOR EACH ROW BEGIN
		SET NEW.created_hour = FLOOR(UNIX_TIMESTAMP(NEW.created)/3600);
	END;
//
DELIMITER ;



/* SOCIAL ACTION EVENT (not unique) */
CREATE TABLE log_event_social_action (
	id int NOT NULL auto_increment,
	created_hour mediumint UNSIGNED,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	user_id int NOT NULL,
	user_session_id int NOT NULL,
	top_log_event_page_view_id int DEFAULT NULL,
	parent_log_event_page_view_id int DEFAULT NULL,
	target_log_url_id int DEFAULT NULL,
	social_network enum ('Facebook', 'Twitter', 'Google+') NOT NULL,
	action enum ('Share', 'Like') NOT NULL,
	message varchar(255) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lesa_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lesa_user_session_id FOREIGN KEY (user_session_id) REFERENCES user_session (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lesa_log_url_id FOREIGN KEY (target_log_url_id) REFERENCES log_url (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lesa_top_log_event_page_view_id FOREIGN KEY (top_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lesa_parent_log_event_page_view_id FOREIGN KEY (parent_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX lesa_created_hour ON log_event_social_action (created_hour);

DROP TRIGGER IF EXISTS `before_insert_log_event_social_action`;
DELIMITER //
CREATE TRIGGER `before_insert_log_event_social_action` BEFORE INSERT ON `log_event_social_action`
	FOR EACH ROW BEGIN
		SET NEW.created_hour = FLOOR(UNIX_TIMESTAMP(NEW.created)/3600);
	END;
//
DELIMITER ;



CREATE TABLE log_search_keyword (
	id int NOT NULL auto_increment,
	keyword varchar(255),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT lsk_keyword_unique UNIQUE (keyword)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP FUNCTION IF EXISTS func_get_log_search_keyword_id //
CREATE FUNCTION func_get_log_search_keyword_id (passed_keyword VARCHAR(255))
	RETURNS INT
	DETERMINISTIC
	READS SQL DATA
	MODIFIES SQL DATA
	BEGIN
		DECLARE out_log_search_keyword_id INT;

		SELECT id
		INTO out_log_search_keyword_id
		FROM log_search_keyword
		WHERE log_search_keyword.keyword = LOWER(passed_keyword);

		IF (out_log_search_keyword_id IS NULL AND passed_keyword IS NOT NULL AND passed_keyword <> '') THEN
			INSERT INTO log_search_keyword (keyword) VALUES (LOWER(passed_keyword));
        	SET out_log_search_keyword_id = LAST_INSERT_ID();
		END IF;

		RETURN out_log_search_keyword_id;
	END //
DELIMITER ;



/* SEARCH EVENT, QUERY AND KEYWORDS (not unique) */
CREATE TABLE log_event_search (
	id int NOT NULL auto_increment,
	created_hour mediumint UNSIGNED,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	user_id int NOT NULL,
	user_session_id int NOT NULL,
	top_log_event_page_view_id int DEFAULT NULL,
	parent_log_event_page_view_id int DEFAULT NULL,
	search_engine enum ('Google', 'Bing', 'Yahoo!', 'Amazon') NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT les_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT les_user_session_id FOREIGN KEY (user_session_id) REFERENCES user_session (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT les_top_log_event_page_view_id FOREIGN KEY (top_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT les_parent_log_event_page_view_id FOREIGN KEY (parent_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX les_created_hour ON log_event_search (created_hour);

DROP TRIGGER IF EXISTS `before_insert_log_event_search`;
DELIMITER //
CREATE TRIGGER `before_insert_log_event_search` BEFORE INSERT ON `log_event_search`
	FOR EACH ROW BEGIN
		SET NEW.created_hour = FLOOR(UNIX_TIMESTAMP(NEW.created)/3600);
	END;
//
DELIMITER ;



CREATE TABLE log_event_search_query (
	log_event_search_id int NOT NULL,
	query varchar(255),
	PRIMARY KEY (log_event_search_id),
	CONSTRAINT lesq_log_event_search_id FOREIGN KEY (log_event_search_id) REFERENCES log_event_search (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_search_keyword (
	log_event_search_id int NOT NULL,
	log_search_keyword_id int NOT NULL,
	PRIMARY KEY (log_event_search_id, log_search_keyword_id),
	CONSTRAINT lesk_log_event_search_id FOREIGN KEY (log_event_search_id) REFERENCES log_event_search (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lesk_log_search_keyword_id FOREIGN KEY (log_search_keyword_id) REFERENCES log_search_keyword (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/* general event logging, unique tables */
CREATE TABLE log_asset_type ( # types of assets, e.g. "video" is a type
	id int NOT NULL auto_increment,
	lookup varchar(20) NOT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lat_unique UNIQUE (lookup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_asset_type_action ( # things a user can do to an asset, e.g. a user can "play" a "video"
	id int NOT NULL auto_increment,
	log_asset_type_id int NOT NULL,
	lookup varchar(20) NOT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lata_log_asset_type_id FOREIGN KEY (log_asset_type_id) REFERENCES log_asset_type (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lata_unique UNIQUE (log_asset_type_id, lookup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_asset_type_property ( # something to track about an asset action of a certain type, e.g. a "video" has a "channel" and a "length"
	id int NOT NULL auto_increment,
	log_asset_type_id int NOT NULL,
	type enum ('provider_category', 'integer', 'decimal', 'string', 'url') NOT NULL,
	lookup varchar(20) NOT NULL,
	title varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT latp_log_asset_type_id FOREIGN KEY (log_asset_type_id) REFERENCES log_asset_type (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT latp_unique unique (log_asset_type_id, lookup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_asset_type_action_property_map ( # something to track about an asset action of a certain type, e.g. a "video" "play" has a "channel" and a "length"
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	PRIMARY KEY (log_asset_type_action_id, log_asset_type_property_id),
	CONSTRAINT latapm_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT latapm_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_asset_provider ( # asset provider, e.g. "youtube" is a provider of the "video" type
	id int NOT NULL auto_increment,
	PRIMARY KEY (id),
	lookup varchar(20) NOT NULL,
	title varchar(255) NOT NULL,
	CONSTRAINT lap_unique UNIQUE (lookup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_asset_provider_category ( # a specific category for a specific property for a particular asset type
	id int NOT NULL auto_increment,
	log_asset_provider_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	external_id varchar(20) NOT NULL,
	title varchar(255) DEFAULT NULL, # string (255) -- e.g. "Machinima", "Machinima Respawn", "Red", "Blue", etc.
	PRIMARY KEY (id),
	CONSTRAINT lapc_log_asset_provider_id FOREIGN KEY (log_asset_provider_id) REFERENCES log_asset_provider (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lapc_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lapc_unique UNIQUE (log_asset_provider_id, log_asset_type_property_id, external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP FUNCTION IF EXISTS func_get_log_asset_provider_category_id //

CREATE FUNCTION func_get_log_asset_provider_category_id (passed_log_asset_provider_id INT, passed_log_asset_type_property_id INT, passed_external_id VARCHAR(20), passed_title VARCHAR(255))
	RETURNS INT
	DETERMINISTIC
	READS SQL DATA
	MODIFIES SQL DATA
	BEGIN
		DECLARE out_log_asset_provider_category_id INT;
		DECLARE log_asset_provider_category_title VARCHAR(255);

		SELECT id, title
			INTO out_log_asset_provider_category_id, log_asset_provider_category_title
			FROM log_asset_provider_category
			WHERE log_asset_provider_id = passed_log_asset_provider_id
				AND log_asset_type_property_id = passed_log_asset_type_property_id
				AND external_id = passed_external_id;

		IF (out_log_asset_provider_category_id IS NOT NULL AND log_asset_provider_category_title != passed_title AND passed_title IS NOT NULL AND passed_title <> '') THEN
			UPDATE log_asset_provider_category SET title = passed_title WHERE id = out_log_asset_provider_category_id;
		ELSEIF (out_log_asset_provider_category_id IS NULL AND passed_log_asset_provider_id IS NOT NULL AND passed_log_asset_provider_id <> 0 AND passed_log_asset_type_property_id IS NOT NULL AND passed_log_asset_type_property_id <> 0 AND passed_external_id IS NOT NULL AND passed_external_id <> '') THEN
			INSERT INTO log_asset_provider_category (log_asset_provider_id, log_asset_type_property_id, external_id, title) VALUES (passed_log_asset_provider_id, passed_log_asset_type_property_id, passed_external_id, passed_title);
        	SET out_log_asset_provider_category_id = LAST_INSERT_ID();
		END IF;

		RETURN out_log_asset_provider_category_id;
	END //
DELIMITER ;



CREATE TABLE log_asset ( # a single asset, e.g. a single video on youtube or a product on amazon
	id int NOT NULL auto_increment,
	log_asset_provider_id int NOT NULL,
	log_asset_type_id int NOT NULL,
	external_id int NOT NULL,
	title varchar(255) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT la_log_asset_provider_id FOREIGN KEY (log_asset_provider_id) REFERENCES log_asset_provider (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT la_log_asset_type_id FOREIGN KEY (log_asset_type_id) REFERENCES log_asset_type (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT la_unique UNIQUE (log_asset_provider_id, log_asset_type_id, external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER //
DROP PROCEDURE IF EXISTS proc_get_log_asset_id //
CREATE PROCEDURE proc_get_log_asset_id (passed_log_asset_provider_id INT, passed_log_asset_type_id INT, passed_external_id VARCHAR(20), passed_title VARCHAR(255), OUT out_log_asset_id INT)
	BEGIN
		DECLARE log_asset_title VARCHAR(255);

		SELECT id, title
			INTO out_log_asset_id, log_asset_title
			FROM log_asset
			WHERE log_asset_provider_id = passed_log_asset_provider_id
				AND log_asset_type_id = passed_log_asset_type_id
				AND external_id = passed_external_id;

		IF (out_log_asset_id IS NOT NULL AND log_asset_title != passed_title AND passed_title IS NOT NULL AND passed_title <> '') THEN
			UPDATE log_asset SET title = passed_title WHERE id = out_log_asset_id;
		ELSEIF (out_log_asset_id IS NULL AND passed_log_asset_provider_id IS NOT NULL AND passed_log_asset_provider_id <> 0 AND passed_log_asset_type_id IS NOT NULL AND passed_log_asset_type_id <> 0 AND passed_external_id IS NOT NULL AND passed_external_id <> '') THEN
			INSERT INTO log_asset (log_asset_provider_id, log_asset_type_id, external_id, title) VALUES (passed_log_asset_provider_id, passed_log_asset_type_id, passed_external_id, passed_title);
        	SET out_log_asset_id = LAST_INSERT_ID();
		END IF;
	END //
DELIMITER ;



/* GENERAL EVENTS (not unique) */
CREATE TABLE log_event_asset (
	id int NOT NULL auto_increment,
	created_hour mediumint UNSIGNED,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	user_id int NOT NULL,
	user_session_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	top_log_event_page_view_id int DEFAULT NULL,
	parent_log_event_page_view_id int DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lea_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lea_user_session_id FOREIGN KEY (user_session_id) REFERENCES user_session (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lea_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lea_log_asset_type_id FOREIGN KEY (log_asset_type_id) REFERENCES log_asset_type (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lea_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT lea_top_log_event_page_view_id FOREIGN KEY (top_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lea_parent_log_event_page_view_id FOREIGN KEY (parent_log_event_page_view_id) REFERENCES log_event_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_provider_category (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	log_asset_provider_category_id int NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leappc_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leappc_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leappc_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leappc_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leappc_log_asset_provider_category_id FOREIGN KEY (log_asset_provider_category_id) REFERENCES log_asset_provider_category (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_integer (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	value int NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leapi_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leapi_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapi_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapi_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_decimal (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	value decimal(10,6) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leapd_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leapd_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapd_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapd_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_string (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	value varchar(2000) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leaps_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leaps_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leaps_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leaps_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_url (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	log_asset_type_property_id int NOT NULL,
	log_url_id int NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leapu_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leapu_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapu_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapu_log_asset_type_property_id FOREIGN KEY (log_asset_type_property_id) REFERENCES log_asset_type_property (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapu_log_url_id FOREIGN KEY (log_url_id) REFERENCES log_url (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE log_event_asset_property_unknown (
	id int NOT NULL auto_increment,
	log_event_asset_id int NOT NULL,
	log_asset_id int NOT NULL,
	log_asset_type_action_id int NOT NULL,
	title varchar(255) NOT NULL,
	value varchar(2000) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT leapun_log_event_asset_id FOREIGN KEY (log_event_asset_id) REFERENCES log_event_asset (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT leapun_log_asset_id FOREIGN KEY (log_asset_id) REFERENCES log_asset (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT leapun_log_asset_type_action_id FOREIGN KEY (log_asset_type_action_id) REFERENCES log_asset_type_action (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

