ALTER TABLE user ADD COLUMN originating_starbar_id integer(10) DEFAULT NULL;
ALTER TABLE user ADD CONSTRAINT FOREIGN KEY (originating_starbar_id) REFERENCES starbar(id) ON DELETE SET NULL ON UPDATE CASCADE;
