ALTER TABLE user ADD COLUMN last_seen timestamp DEFAULT '0000-00-00 00:00:00';
ALTER TABLE user ADD COLUMN last_used_app timestamp DEFAULT '0000-00-00 00:00:00';

CREATE TABLE hostname (
	id int(10) NOT NULL auto_increment,
	hostname_suffix varchar(6) NOT NULL,
	hostname varchar(255) NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT hostname_unique UNIQUE (hostname),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX hostname_hostname_suffix ON hostname (hostname_suffix);


DELIMITER //
DROP PROCEDURE IF EXISTS proc_get_hostname_and_protocol_from_url //
CREATE PROCEDURE proc_get_hostname_and_protocol_from_url(IN url VARCHAR(2000), OUT hostname VARCHAR(255), OUT protocol VARCHAR(10))
	BEGIN
		SET @double_slash = LOCATE('//', url) + 2;
		SET @first_slash = LOCATE('/', url, @double_slash);
		SET @first_slash = IF(@first_slash, @first_slash - @double_slash, LENGTH(url));
		SET protocol = IF(@double_slash = 9, 'https', 'http');
		SET hostname = SUBSTR(url, @double_slash, @first_slash);
	END //
DELIMITER ;


DELIMITER //
DROP PROCEDURE IF EXISTS proc_get_hostname_id //
CREATE PROCEDURE proc_get_hostname_id(passed_hostname VARCHAR(255), OUT hostname_id INT(10))
	BEGIN
		SET @passed_hostname_suffix = RIGHT(passed_hostname, 6);

		SELECT id
		INTO hostname_id
		FROM hostname
		WHERE hostname.hostname_suffix = @passed_hostname_suffix
			AND hostname.hostname = passed_hostname;

		IF (hostname_id IS NULL AND passed_hostname IS NOT NULL AND passed_hostname <> '') THEN
			INSERT INTO hostname (hostname_suffix, hostname) VALUES (@passed_hostname_suffix, passed_hostname);
        	/*SELECT LAST_INSERT_ID() INTO hostname_id;*/
        	SET hostname_id = LAST_INSERT_ID();
		END IF;
	END //
DELIMITER ;


CREATE TABLE light_metrics_page_view (
	id int(10) NOT NULL auto_increment,
	user_id int(10) NOT NULL,
	starbar_id int(10) NOT NULL,
	hostname_id int(10) DEFAULT NULL,
	protocol enum('http', 'https') DEFAULT 'http',
	PRIMARY KEY (id),
	CONSTRAINT lmpv_u_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lmpv_s_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lmpv_h_id FOREIGN KEY (hostname_id) REFERENCES hostname (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE light_metrics_log (
	id int(10) NOT NULL auto_increment,
	user_id int(10) NOT NULL,
	starbar_id int(10) NOT NULL,
	hostname_id int(10) DEFAULT NULL,
	type enum('full page view', 'page view', 'search', 'social activity', 'campaign view', 'campaign click', 'creative view', 'creative click', 'app action', 'game event') NOT NULL,
	log_table enum('metrics_page_view', 'light_metrics_page_view', 'metrics_search', 'metrics_social_activity', 'study_ad_user_map', 'user_action_log', 'user_gaming_transaction_history') NOT NULL,
	metrics_page_view_id int(10) DEFAULT NULL,
	light_metrics_page_view_id int(10) DEFAULT NULL,
	metrics_search_id int(10) DEFAULT NULL,
	metrics_social_activity_id int(10) DEFAULT NULL,
	study_ad_user_map_id int(10) DEFAULT NULL,
	user_action_log_id int(10) DEFAULT NULL,
	user_gaming_transaction_history_id int(10) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT lml_u_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_s_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_h_id FOREIGN KEY (hostname_id) REFERENCES hostname (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_mpv_id FOREIGN KEY (metrics_page_view_id) REFERENCES metrics_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_lmpv_id FOREIGN KEY (light_metrics_page_view_id) REFERENCES light_metrics_page_view (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_ms_id FOREIGN KEY (metrics_search_id) REFERENCES metrics_search (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_msa_id FOREIGN KEY (metrics_social_activity_id) REFERENCES metrics_social_activity (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_saum_id FOREIGN KEY (study_ad_user_map_id) REFERENCES study_ad_user_map (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_ual_id FOREIGN KEY (user_action_log_id) REFERENCES user_action_log (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT lml_ugth_id FOREIGN KEY (user_gaming_transaction_history_id) REFERENCES user_gaming_transaction_history (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created_month int(6),
	created_date date,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX light_metrics_log_created_month ON light_metrics_log (created_month);
CREATE INDEX light_metrics_log_created_date ON light_metrics_log (created_date);

DROP TRIGGER IF EXISTS `before_insert_light_metrics_log`;
DELIMITER //
CREATE TRIGGER `before_insert_light_metrics_log` BEFORE INSERT ON `light_metrics_log`
	FOR EACH ROW BEGIN
		SET NEW.created_month = DATE_FORMAT(CURDATE(), '%Y%m') + 0;
		SET NEW.created_date = CURDATE();
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `metrics_page_view_to_metrics_log`;
DROP TRIGGER IF EXISTS `after_insert_metrics_page_view`;
DELIMITER //
CREATE TRIGGER `after_insert_metrics_page_view` AFTER INSERT ON `metrics_page_view`
	FOR EACH ROW BEGIN
		CALL proc_get_hostname_and_protocol_from_url(NEW.url, @hostname, @protocol);
		CALL proc_get_hostname_id(@hostname, @hostname_id);

		INSERT INTO light_metrics_page_view (created, user_id, starbar_id, hostname_id, protocol)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, @hostname_id, @protocol);
		INSERT INTO light_metrics_log (created, user_id, starbar_id, hostname_id, type, log_table, metrics_page_view_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, @hostname_id, 'full page view', 'metrics_page_view', NEW.id);

		/* keep this just in case... remove this later */
	    INSERT INTO metrics_log
	        (legacy_id, created, user_id, type, starbar_id, content)
	        (SELECT
	            mpv.id, mpv.created, mpv.user_id, 'page view', mpv.starbar_id, mpv.url
	        FROM
	            metrics_page_view mpv, `user` u, starbar s
	        WHERE
	            mpv.id = NEW.id
	            AND mpv.user_id = u.id
	            AND mpv.starbar_id = s.id);

	    /* this should be done when we update light_metrics_log in theory, but this is close enough and won't write to the database as often' */
	    UPDATE user SET last_seen = NOW() WHERE id = NEW.user_id;
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `after_insert_light_metrics_page_view`;
DELIMITER //
CREATE TRIGGER `after_insert_light_metrics_page_view` AFTER INSERT ON `light_metrics_page_view`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log (created, user_id, starbar_id, hostname_id, type, log_table, light_metrics_page_view_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, NEW.hostname_id, 'page view', 'light_metrics_page_view', NEW.id);
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `study_ad_user_map_to_metrics_log`;
DROP TRIGGER IF EXISTS `after_insert_study_ad_user_map`;
DELIMITER //
CREATE TRIGGER `after_insert_study_ad_user_map` AFTER INSERT ON `study_ad_user_map`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log
			(created, user_id, starbar_id, type, log_table, study_ad_user_map_id)
			(
					SELECT NEW.created, NEW.user_id, NEW.starbar_id, concat(NEW.type, ' ', sa.type), 'study_ad_user_map', NEW.id
					FROM study_ad sa
					INNER JOIN study_ad_user_map saum
							ON saum.id = NEW.id
							AND saum.study_ad_id = sa.id
			);

		/* keep this just in case... remove this later */
	    INSERT INTO metrics_log
	        (legacy_id, created, user_id, type, starbar_id, content)
	        (
	            SELECT
	                saum.id, saum.created, saum.user_id, concat(sa.type, ' ', saum.type), saum.starbar_id,
	                    concat(sa.type, ' ', saum.type, ': ', sa.existing_ad_type, ' AD: ', sa.existing_ad_tag, '(', sa.existing_ad_type, ' ad, ID: ', sa.id, '), url: ', saum.url)
	            FROM
	                study_ad_user_map saum, study_ad sa
	            WHERE
	                saum.id = NEW.id
	                AND saum.study_ad_id = sa.id
	        );
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `metrics_social_activity_to_metrics_log`;
DROP TRIGGER IF EXISTS `after_insert_metrics_social_activity`;
DELIMITER //
CREATE TRIGGER `after_insert_metrics_social_activity` AFTER INSERT ON `metrics_social_activity`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log (created, user_id, starbar_id, type, log_table, metrics_social_activity_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, 'social activity', 'metrics_social_activity', NEW.id);

		/* keep this just in case... remove this later */
	    INSERT INTO metrics_log
	        (legacy_id, created, user_id, type, starbar_id, content)
	        (
	            SELECT
	                msa.id, msa.created, msa.user_id, 'social activity', msa.starbar_id,
	                    concat(sat.short_name, ', url: ', msa.url , ', content: ', msa.content)
	            FROM
	                metrics_social_activity msa, `user` u, starbar s, lookup_social_activity_type sat
	            WHERE
	                msa.id = NEW.id
	                AND msa.user_id = u.id
	                AND msa.starbar_id = s.id
	                AND msa.social_activity_type_id = sat.id
	        );
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `metrics_search_to_metrics_log`;
DROP TRIGGER IF EXISTS `after_insert_metrics_search`;
DELIMITER //
CREATE TRIGGER `after_insert_metrics_search` AFTER INSERT ON `metrics_search`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log (created, user_id, starbar_id, type, log_table, metrics_search_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, 'search', 'metrics_search', NEW.id);

		/* keep this just in case... remove this later */
	    INSERT INTO metrics_log
	        (legacy_id, created, user_id, type, starbar_id, content)
	        (SELECT
	            ms.id, ms.created, ms.user_id, 'search', ms.starbar_id, concat(lsa.label, ', query: ', ms.query)
	        FROM
	            metrics_search ms, `user` u, starbar s, lookup_search_engines lsa
	        WHERE
	            ms.id = NEW.id
	            AND ms.user_id = u.id
	            AND ms.starbar_id = s.id
	            AND ms.search_engine_id = lsa.id);
	END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `after_insert_user_action_log`;
DELIMITER //
CREATE TRIGGER `after_insert_user_action_log` AFTER INSERT ON `user_action_log`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log (created, user_id, starbar_id, type, log_table, metrics_search_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, 'user action', 'user_action_log', NEW.id);

	    UPDATE user SET last_seen = NOW(), last_used_app = NOW() WHERE id = NEW.user_id;
	END;
//
DELIMITER ;

/*
NOT DONE -- user_gaming_transaction_history has no user_id and starbar_id directly, so this is probably easier to do in PHP code when inserting user_gaming_transaction_history records
DROP TRIGGER IF EXISTS `after_insert_user_gaming_transaction_history`;
DELIMITER //
CREATE TRIGGER `after_insert_user_gaming_transaction_history` AFTER INSERT ON `user_gaming_transaction_history`
	FOR EACH ROW BEGIN
		INSERT INTO light_metrics_log (created, user_id, starbar_id, type, log_table, metrics_search_id)
			VALUES (NEW.created, NEW.user_id, NEW.starbar_id, 'game event', 'user_gaming_transaction_history', NEW.id);
	END;
//
DELIMITER ;
*/