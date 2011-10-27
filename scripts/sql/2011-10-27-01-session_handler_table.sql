SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `session`;

/**
 * Use ENGINE=MyISAM to boost up speed
 * This table should not be a part of any transactions or PK/FK relations,
 * the charset must NOT be utf8 for the
*/

CREATE TABLE IF NOT EXISTS `session` (
  `id` CHAR(32) CHARACTER SET ascii NOT NULL DEFAULT '',
  `modified` INT(11) DEFAULT NULL,
  `lifetime` INT(11) DEFAULT NULL,
  `data` TEXT,
  PRIMARY KEY (`id`),
  KEY `modified` (`modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;
