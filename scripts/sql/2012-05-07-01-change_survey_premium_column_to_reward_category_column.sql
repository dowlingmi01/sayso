ALTER TABLE survey ADD COLUMN reward_category enum('standard', 'premium', 'profile') NOT NULL DEFAULT 'standard';
UPDATE survey SET reward_category = 'premium' WHERE premium IS TRUE;
