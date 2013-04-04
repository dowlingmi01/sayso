SET FOREIGN_KEY_CHECKS=0;


DROP TABLE IF EXISTS study_avail;
CREATE TABLE IF NOT EXISTS study_avail (
  id int(10) NOT NULL AUTO_INCREMENT,
  creative_id int(10) NOT NULL,
  label varchar(100) NOT NULL COMMENT 'label',
  selector varchar(255) NOT NULL,
  created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  modified timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  KEY creative_id (creative_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE study_avail
  ADD CONSTRAINT study_avail_ibfk_2 FOREIGN KEY (creative_id) REFERENCES study_creative (id) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;


DROP TABLE IF EXISTS study_avail_domain_map;
CREATE TABLE IF NOT EXISTS study_avail_domain_map (
  study_avail_id int(10) NOT NULL,
  domain_id int(10) NOT NULL,
  PRIMARY KEY (study_avail_id,domain_id),
  KEY domain_id (domain_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE study_avail_domain_map
  ADD CONSTRAINT study_avail_domain_map_ibfk_2 FOREIGN KEY (domain_id) REFERENCES study_domain (id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT study_avail_domain_map_ibfk_1 FOREIGN KEY (study_avail_id) REFERENCES study_avail (id) ON DELETE CASCADE ON UPDATE CASCADE;


SET FOREIGN_KEY_CHECKS=1;
