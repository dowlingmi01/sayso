#browser types
DROP TABLE IF EXISTS `browser_type`;
CREATE TABLE `browser_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#browsers (unique)
DROP TABLE IF EXISTS browser;
CREATE TABLE `browser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browser_type_id` int(11) NOT NULL,
  `major_version` smallint(6) DEFAULT NULL,
  `minor_version` smallint(6) DEFAULT NULL,
  `agent_string` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX unique_browser USING HASH ON browser (agent_string),
  CONSTRAINT `b_browser_type_id` FOREIGN KEY (`browser_type_id`) REFERENCES `browser_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

#records active bans on ip addresses
DROP TABLE IF EXISTS `login_ban_ip`;
CREATE TABLE `login_ban_ip` (
  `ip` int(10) unsigned NOT NULL,
  `reason` varchar(45) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#records failed login attempts. use the code to remove time expired strikes
DROP TABLE IF EXISTS `login_strikes_ip`;
CREATE TABLE `login_strikes_ip` (
  `ip` int(10) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#records failed login attempt strikes against a username. use the code to remove expired strikes
DROP TABLE IF EXISTS `login_strikes_user`;
CREATE TABLE `login_strikes_user` (
  `username` varchar(45) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#alter user_session table
#requires the user_sesion table from 2013-04-30-01-create_logging_tables_and_triggers.sql
ALTER TABLE `user_session` ADD COLUMN `session_key` VARCHAR(32) NOT NULL;
ALTER TABLE `user_session` ADD COLUMN `expired` DATETIME NOT NULL;
ALTER TABLE `user_session` ADD COLUMN `ip` int(10) unsigned DEFAULT NULL;
ALTER TABLE `user_session` ADD COLUMN `new_user_session_id`int(11) DEFAULT NULL;
ALTER TABLE `user_session` ADD UNIQUE INDEX `us_session_key_unique` (`session_key`) ;
ALTER TABLE `user_session` ADD CONSTRAINT `us_new_user_session_id` FOREIGN KEY (`new_user_session_id`) REFERENCES `user_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE