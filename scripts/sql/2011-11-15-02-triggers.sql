TRUNCATE TABLE `metrics`;

--
-- Triggers `metrics_search`
--

DROP TRIGGER IF EXISTS `metrics_search_to_metrics`;
DELIMITER //
CREATE TRIGGER `metrics_search_to_metrics` AFTER INSERT ON `metrics_search`
 FOR EACH ROW BEGIN
    INSERT INTO metrics
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
  END
//
DELIMITER ;

