#browser types
CREATE TABLE `browser_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

#browsers (unique) 
ALTER TABLE `browser` CHANGE COLUMN `agent_string` `agent_string` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `browser` drop column `browser_type`;
ALTER TABLE `browser` change version minor_version SMALLINT;
ALTER TABLE `browser` ADD `comment` VARCHAR(45);
ALTER TABLE `browser` ADD `browser_type_id` INT NOT NULL;
ALTER TABLE `browser` ADD CONSTRAINT `b_browser_type_id`
                             FOREIGN KEY (`browser_type_id`)
                             REFERENCES `browser_type` (`id`);
ALTER TABLE `browser` ADD INDEX `unique_browser` USING HASH (`agent_string`); 

#user session (not unique, new one started every time a user authenticates, user_session_id sent to client at that time, then user_session_id (or user_key, see below) is sent by the client with every request)
ALTER TABLE `user_session` ADD `session_key` varchar(32) NOT NULL;
ALTER TABLE `user_session` ADD `expired` varchar(32) NOT NULL;
ALTER TABLE `user_session` ADD `ip` int(10) unsigned DEFAULT NULL;
ALTER TABLE `user_session` ADD `new_user_session_id` int(11) DEFAULT NULL;
ALTER TABLE `user_session` ADD UNIQUE KEY `us_session_key_unique` (`session_key`);


#records active bans on ip addresses
DROP TABLE IF EXISTS `login_ban_ip`;
CREATE TABLE `login_ban_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` int(10) unsigned DEFAULT NULL,
  `reason` varchar(45) DEFAULT NULL,
  `timestamp` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

#records failed login attempts. use the code to remove time expired strikes
DROP TABLE IF EXISTS `login_strikes_ip`;
CREATE TABLE `login_strikes_ip` (
  `ip` int(10) unsigned NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


#records failed login attempt strikes against a username. use the code to remove expired strikes
DROP TABLE IF EXISTS `login_strikes_user`;
CREATE TABLE `login_strikes_user` (
  `username` varchar(45) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
) ENGINE=InnoDB DEFAULT CHARSET=latin1;