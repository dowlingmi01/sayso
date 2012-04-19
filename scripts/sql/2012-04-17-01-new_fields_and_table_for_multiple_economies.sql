CREATE TABLE economy (
	id int(10) NOT NULL auto_increment,
	title varchar(255) NOT NULL,
	redeemable_currency varchar(255) DEFAULT NULL,
	experience_currency varchar(255) DEFAULT NULL,
	PRIMARY KEY (id),
	created timestamp DEFAULT CURRENT_TIMESTAMP,
	modified timestamp DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE starbar ADD COLUMN economy_id int(10) DEFAULT NULL;
ALTER TABLE starbar ADD CONSTRAINT sb_e_id FOREIGN KEY (economy_id) REFERENCES economy (id) ON DELETE SET NULL ON UPDATE CASCADE;

INSERT INTO economy (id, title, redeemable_currency, experience_currency) VALUES (1, 'Hello Music', 'Notes', 'Chops');

UPDATE starbar SET economy_id = 1 WHERE id = 1;



UPDATE notification_message_group SET end_at = CURRENT_TIMESTAMP WHERE short_name LIKE 'Send Once 2012-01-06';
