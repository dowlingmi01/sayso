CREATE TABLE starbar_content_change (
	id int(10) NOT NULL auto_increment,
	starbar_content_id int(10) NOT NULL,
	content text DEFAULT NULL,
	PRIMARY KEY (id),
	CONSTRAINT scc_sc_id FOREIGN KEY (starbar_content_id) REFERENCES starbar_content (id) ON DELETE RESTRICT ON UPDATE CASCADE,
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TRIGGER IF EXISTS `before_update_starbar_content`;
DELIMITER //
CREATE TRIGGER `before_update_starbar_content` BEFORE UPDATE ON `starbar_content`
    FOR EACH ROW BEGIN
    	IF (OLD.content != NEW.content) THEN
    		INSERT INTO starbar_content_change (starbar_content_id, content) VALUES (OLD.id, OLD.content);
    	END IF;
    END;
//
DELIMITER ;
