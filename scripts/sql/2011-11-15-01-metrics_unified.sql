--
-- Table structure for table metrics
--

DROP TABLE IF EXISTS metrics_log;

CREATE TABLE metrics_log (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  legacy_id int(10) NOT NULL,
  created datetime NOT NULL,
  user_id int(10) unsigned NOT NULL,
  metrics_type tinyint(3) unsigned NOT NULL,
  starbar_id int(10) unsigned NOT NULL,  
  content text NOT NULL,
  PRIMARY KEY (id),
  KEY created (created),
  KEY user_id (user_id),
  KEY metrics_type (metrics_type),
  KEY starbar_id (starbar_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;