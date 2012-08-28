ALTER TABLE metrics_log ADD type enum('search', 'page view', 'social activity', 'campaign view', 'campaign click', 'creative view', 'creative click') NOT NULL DEFAULT 'page view';


DROP TRIGGER IF EXISTS `metrics_search_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_search_to_metrics_log` AFTER INSERT ON `metrics_search`
 FOR EACH ROW BEGIN
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


DROP TRIGGER IF EXISTS `metrics_page_view_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_page_view_to_metrics_log` AFTER INSERT ON `metrics_page_view`
 FOR EACH ROW BEGIN
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
  END;
//
DELIMITER ;


DROP TRIGGER IF EXISTS `metrics_social_activity_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_social_activity_to_metrics_log` AFTER INSERT ON `metrics_social_activity`
 FOR EACH ROW BEGIN
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


CREATE TABLE study_ad (
	id int(10) NOT NULL auto_increment,
	type enum('campaign', 'creative') NOT NULL,
	existing_ad_type enum('image', 'flash', 'facebook') NOT NULL,
	existing_ad_tag varchar(2000) NOT NULL,
	existing_ad_domain varchar(2000) DEFAULT NULL,
	replacement_ad_type enum('image', 'flash', 'facebook') DEFAULT NULL,
	replacement_ad_url varchar(2000) DEFAULT NULL,
	replacement_ad_title varchar(2000) DEFAULT NULL,
	replacement_ad_description varchar(2000) DEFAULT NULL,
	ad_target varchar(2000) NOT NULL,
	PRIMARY KEY (id),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE study_ad_user_map (
	id int(10) NOT NULL auto_increment,
	starbar_id int(10) NOT NULL,
	user_id int(10) NOT NULL,
	study_ad_id int(10) NOT NULL,
	type enum('view', 'click') NOT NULL,
	url varchar(2000) DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT saum_s_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT saum_u_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT saum_sa_id FOREIGN KEY (study_ad_id) REFERENCES study_ad (id) ON DELETE CASCADE ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TRIGGER IF EXISTS `study_ad_user_map_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `study_ad_user_map_to_metrics_log` AFTER INSERT ON `study_ad_user_map`
 FOR EACH ROW BEGIN
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


DROP TRIGGER IF EXISTS `metrics_tag_view_to_metrics_log`;
DROP TRIGGER IF EXISTS `metrics_tag_click_thru_to_metrics_log`;
DROP TRIGGER IF EXISTS `metrics_creative_view_to_metrics_log`;
DROP TRIGGER IF EXISTS `metrics_creative_click_thru_to_metrics_log`;


UPDATE metrics_log SET type = 'search' WHERE metrics_type = 1;
/* DEFAULT: UPDATE metrics_log SET type = 'page view' WHERE metrics_type = 2; */
UPDATE metrics_log SET type = 'social activity' WHERE metrics_type = 3;
UPDATE metrics_log SET type = 'campaign view' WHERE metrics_type = 4;
UPDATE metrics_log SET type = 'campaign click' WHERE metrics_type = 5;
UPDATE metrics_log SET type = 'creative view' WHERE metrics_type = 6;
UPDATE metrics_log SET type = 'creative click' WHERE metrics_type = 7;

ALTER TABLE report_cell CHANGE category category enum('Internal', 'Custom', 'Panel', 'Gender', 'Age Range', 'Marital Status', 'Education', 'Ethnicity', 'Income', 'Parental Status', 'Study') NOT NULL DEFAULT 'Internal';
ALTER TABLE report_cell CHANGE title title VARCHAR(2000) NOT NULL;
ALTER TABLE report_cell_user_condition CHANGE condition_type condition_type enum('choice', 'string', 'integer', 'decimal', 'monetary', 'starbar', 'report_cell', 'study_ad') NOT NULL;
ALTER TABLE report_cell_user_condition CHANGE comparison_type comparison_type enum('<', '>', '=', '<=', '>=', '!=', 'contains', 'does not contain', 'viewed', 'clicked') NOT NULL;
ALTER TABLE report_cell_user_condition ADD COLUMN compare_study_ad_id int(10) DEFAULT NULL AFTER compare_survey_question_choice_id;
ALTER TABLE report_cell_user_condition ADD CONSTRAINT rcuc_csa_id FOREIGN KEY (compare_study_ad_id) REFERENCES study_ad (id) ON DELETE CASCADE ON UPDATE CASCADE;

