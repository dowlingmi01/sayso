--
-- Table structure for table metrics
--

DROP TABLE IF EXISTS metrics;

CREATE TABLE metrics (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  legacy_id int(10) NOT NULL,
  created datetime NOT NULL,
  user_id int(10) unsigned NOT NULL,
  metrics_type tinyint(3) unsigned NOT NULL,
  starbar_id int(10) unsigned NOT NULL,
  param_1 int(11) unsigned NOT NULL DEFAULT '0',
  param_2 int(11) unsigned NOT NULL DEFAULT '0',
  param_3 varchar(80) NOT NULL DEFAULT '',
  param_4 varchar(80) NOT NULL DEFAULT '',
  content text NOT NULL,
  PRIMARY KEY (id),
  KEY created (created),
  KEY user_id (user_id),
  KEY metrics_type (metrics_type),
  KEY starbar_id (starbar_id),
  KEY param_1 (param_1),
  KEY param_2 (param_2),
  KEY param_3 (param_3(4)),
  KEY param_4 (param_4(4))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;