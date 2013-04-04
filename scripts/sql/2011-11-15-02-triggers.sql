TRUNCATE TABLE `metrics_log`;

--
-- Triggers `metrics_search`
--

DROP TRIGGER IF EXISTS `metrics_search_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_search_to_metrics_log` AFTER INSERT ON `metrics_search`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (SELECT
            ms.id, ms.created, ms.user_id, 1, ms.starbar_id, concat(lsa.label, ', query: ', ms.query)
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

--
-- Triggers `metrics_page_view`
--

DROP TRIGGER IF EXISTS `metrics_page_view_to_metrics_log`;
DELIMITER //
CREATE TRIGGER `metrics_page_view_to_metrics_log` AFTER INSERT ON `metrics_page_view`
 FOR EACH ROW BEGIN
    INSERT INTO metrics_log
        (legacy_id, created, user_id, metrics_type, starbar_id, content)
        (SELECT
            mpv.id, mpv.created, mpv.user_id, 2, mpv.starbar_id, mpv.url
        FROM
            metrics_page_view mpv, `user` u, starbar s
        WHERE
            mpv.id = NEW.id
            AND mpv.user_id = u.id
            AND mpv.starbar_id = s.id);
  END;
//
DELIMITER ;