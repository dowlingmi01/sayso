TRUNCATE TABLE `metrics_log`;

--
-- Triggers `metrics_social_activity`
--

DROP TRIGGER IF EXISTS `metrics_social_activity_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_social_activity_to_metrics_log` AFTER INSERT ON `metrics_social_activity`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (
            SELECT
                msa.id, msa.created, msa.user_id, 3, msa.starbar_id,
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

--
-- Triggers `metrics_tag_view`
--

DROP TRIGGER IF EXISTS `metrics_tag_view_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_tag_view_to_metrics_log` AFTER INSERT ON `metrics_tag_view`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (
            SELECT
                mtv.id, mtv.created, mtv.user_id, 4, mtv.starbar_id,
                    concat('Cell: ', c.cell_type , ', tag: ', t.`name`, ', url: ', t.target_url)
            FROM
                metrics_tag_view mtv, `user` u, starbar s, study_tag t, study_cell c
            WHERE
                mtv.id = NEW.id
                AND mtv.user_id = u.id
                AND mtv.starbar_id = s.id
                AND mtv.tag_id = t.id
                AND mtv.cell_id = c.id
        );
  END;
//
DELIMITER ;

--
-- Triggers `metrics_tag_click_thru`
--

DROP TRIGGER IF EXISTS `metrics_tag_click_thru_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_tag_click_thru_to_metrics_log` AFTER INSERT ON `metrics_tag_click_thru`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (
            SELECT
                mct.id, mtv.created, mtv.user_id, 5, mtv.starbar_id,
                    concat('Click through for cell: ', c.cell_type , ', tag: ', t.`name`, ', url: ', t.target_url)
            FROM
                metrics_tag_click_thru mct, metrics_tag_view mtv, `user` u, starbar s, study_tag t, study_cell c
            WHERE
                mct.id = NEW.id
                AND mct.metrics_tag_view_id = mtv.id
                AND mtv.user_id = u.id
                AND mtv.starbar_id = s.id
                AND mtv.tag_id = t.id
                AND mtv.cell_id = c.id
        );
  END;
//
DELIMITER ;

--
-- Triggers `metrics_creative_view`
--

DROP TRIGGER IF EXISTS `metrics_creative_view_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_creative_view_to_metrics_log` AFTER INSERT ON `metrics_creative_view`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (
            SELECT
                mcv.id, mcv.created, mcv.user_id, 6, mcv.starbar_id,
                    concat('Cell: ', c.cell_type , ', creative: ', t.`name`,
                        CASE WHEN t.url IS NOT NULL THEN CONCAT(', url: ', t.url) ELSE '' END )
            FROM
                metrics_creative_view mcv, `user` u, starbar s, study_creative t, study_cell c
            WHERE
                mcv.id = NEW.id
                AND mcv.user_id = u.id
                AND mcv.starbar_id = s.id
                AND mcv.creative_id = t.id
                AND mcv.cell_id = c.id
        );
  END;
//
DELIMITER ;

--
-- Triggers `metrics_creative_click_thru`
--

DROP TRIGGER IF EXISTS `metrics_creative_click_thru_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_creative_click_thru_to_metrics_log` AFTER INSERT ON `metrics_creative_click_thru`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (
            SELECT
                mzt.id, mcv.created, mcv.user_id, 7, mcv.starbar_id,
                    concat('Click through for cell: ', c.cell_type , ', creative: ', t.name,
                        CASE WHEN t.url IS NOT NULL THEN CONCAT(', url: ', t.url) ELSE '' END )
            FROM
                metrics_creative_click_thru mzt, metrics_creative_view mcv, `user` u, starbar s, study_creative t, study_cell c
            WHERE
                mzt.id = NEW.id
                AND mzt.metrics_creative_view_id = mcv.id
                AND mcv.user_id = u.id
                AND mcv.starbar_id = s.id
                AND mcv.creative_id = t.id
                AND mcv.cell_id = c.id
        );
  END;
//
DELIMITER ;


/**
--
-- Clean all related database tables is needed
--

SET foreign_key_checks = 0;

TRUNCATE TABLE metrics_search;
TRUNCATE TABLE metrics_page_view;
TRUNCATE TABLE metrics_social_activity;
TRUNCATE TABLE metrics_tag_view;
TRUNCATE TABLE metrics_tag_click_thru;
TRUNCATE TABLE metrics_creative_view;
TRUNCATE TABLE metrics_creative_click_thru;

SET foreign_key_checks = 1;
*/