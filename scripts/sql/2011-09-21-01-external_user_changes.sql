SET foreign_key_checks = 0;

ALTER TABLE external_user CHANGE install_counter install_begin_time datetime;

UPDATE external_user SET install_begin_time = null;

SET foreign_key_checks = 1;
