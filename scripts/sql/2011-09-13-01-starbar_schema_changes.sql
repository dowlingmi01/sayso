SET foreign_key_checks = 0;

ALTER TABLE starbar ADD domain varchar(64) AFTER user_pseudonym;

UPDATE starbar SET domain = 'hellomusic.com' WHERE short_name = 'hellomusic';

ALTER TABLE external_user ADD install_token varchar(64) AFTER starbar_id;

ALTER TABLE external_user CHANGE external_id uuid varchar(255);

ALTER TABLE external_user ADD install_ip_address varchar(255) AFTER install_token;

ALTER TABLE external_user ADD install_user_agent varchar(255) AFTER install_ip_address;

ALTER TABLE external_user ADD uuid_type enum('integer', 'email', 'username', 'hash') DEFAULT NULL AFTER uuid;

ALTER TABLE external_user ADD INDEX (install_token);

SET foreign_key_checks = 1;
