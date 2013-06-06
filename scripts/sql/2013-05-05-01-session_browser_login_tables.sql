#browsers (unique) 
DROP TABLE IF EXISTS browser;
CREATE TABLE `browser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browser_type_id` int(11) NOT NULL,
  `major_version` smallint(6) DEFAULT NULL,
  `minor_version` varchar(255) DEFAULT NULL,
  `agent_string` varchar(2000) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `browser_type_id_idx` (`browser_type_id`),
  CONSTRAINT `browser_type_id` FOREIGN KEY (`browser_type_id`) REFERENCES `browser_type` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

#browser types
DROP TABLE IF EXISTS `browser`;
CREATE TABLE `browser_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

#user session (not unique, new one started every time a user authenticates, user_session_id sent to client at that time, then user_session_id (or user_key, see below) is sent by the client with every request)
DROP TABLE IF EXISTS `user_session`;
CREATE TABLE `user_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `browser_id` int(11) NOT NULL,
  `session_key` varchar(32) NOT NULL,
  `expired` tinyint(4) DEFAULT '0',
  `ip` int(10) unsigned DEFAULT NULL,
  `new_session_id` int(11) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `us_session_key_unique` (`session_key`),
  KEY `us_user_id` (`user_id`),
  KEY `us_browser_id` (`browser_id`),
  CONSTRAINT `us_browser_id` FOREIGN KEY (`browser_id`) REFERENCES `browser` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `us_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;

#records active bans on ip addresses
CREATE TABLE `login_ban_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned DEFAULT NULL,
  `reason` varchar(45) DEFAULT NULL,
  `timestamp` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

#records failed login attempts. use the code to remove time expired strikes
CREATE TABLE `login_strikes_ip` (
  `time` int(11) NOT NULL,
  `ip` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

#records failed login attempt strikes against a username. use the code to remove expired strikes
CREATE TABLE `login_strikes_user` (
  `time` int(11) NOT NULL,
  `username` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;