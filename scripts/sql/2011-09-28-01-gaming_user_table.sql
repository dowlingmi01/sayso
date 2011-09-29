SET foreign_key_checks = 0;

CREATE TABLE user_gaming (
  id int(10) NOT NULL AUTO_INCREMENT,
  gaming_id varchar(64) NOT NULL,
  user_id int(10) DEFAULT NULL,
  starbar_id int(10) DEFAULT NULL,
  created timestamp DEFAULT '0000-00-00 00:00:00',
  modified timestamp DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  UNIQUE KEY user_gaming_unique_gaming_id (gaming_id),
  UNIQUE KEY user_gaming_unique_user (user_id, starbar_id),
  CONSTRAINT user_gaming_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT user_gaming_starbar_id FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE developer.application SET game = 'HelloMusic', game_economy = 'HelloMusic' WHERE app_key = 'ede421a1d0e08aa672c552e8883e645f';

SET foreign_key_checks = 1;
