ALTER TABLE user_email ADD UNIQUE KEY user_email_unique (user_id, email);

ALTER TABLE external_user ADD install_counter smallint DEFAULT 0 AFTER install_user_agent;