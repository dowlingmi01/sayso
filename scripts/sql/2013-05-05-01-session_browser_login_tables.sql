#browser types
CREATE TABLE `browser_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

#browsers (unique)
CREATE TABLE `browser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browser_type_id` int(11) NOT NULL,
  `major_version` smallint(6) DEFAULT NULL,
  `minor_version` smallint(6) DEFAULT NULL,
  `agent_string` varchar(255) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_browser` (`agent_string`) USING HASH,
  CONSTRAINT `b_browser_type_id` FOREIGN KEY (`browser_type_id`) REFERENCES `browser_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

#user_session
CREATE TABLE `user_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `browser_id` int(11) NOT NULL,
  `session_key` varchar(32) NOT NULL,
  `expired` datetime DEFAULT NULL,
  `ip` int(10) unsigned DEFAULT NULL,
  `new_user_session_id` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `us_session_key_unique` (`session_key`),
  CONSTRAINT `b_browser_id` FOREIGN KEY (`browser_id`) REFERENCES `browser` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

#records active bans on ip addresses
DROP TABLE IF EXISTS `login_ban_ip`;
CREATE TABLE `login_ban_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned DEFAULT NULL,
  `reason` varchar(45) DEFAULT NULL,
  `timestamp` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
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
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
