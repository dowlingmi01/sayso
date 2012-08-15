ALTER TABLE user_install ADD COLUMN type ENUM('regular', 'test') DEFAULT 'regular' NOT NULL AFTER client_data;
