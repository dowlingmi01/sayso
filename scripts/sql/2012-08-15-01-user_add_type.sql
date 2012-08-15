ALTER TABLE user ADD COLUMN type ENUM('regular', 'test') DEFAULT 'regular' NOT NULL AFTER user_roll_id;
