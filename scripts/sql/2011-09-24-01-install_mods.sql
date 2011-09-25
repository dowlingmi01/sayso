SET foreign_key_checks = 0;

ALTER TABLE external_user_install ADD UNIQUE KEY external_user_unique (ip_address, user_agent, begin_time);

ALTER TABLE external_user_install MODIFY token varchar(64) DEFAULT NULL;

SET foreign_key_checks = 1;
