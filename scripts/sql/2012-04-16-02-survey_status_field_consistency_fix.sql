ALTER TABLE survey CHANGE COLUMN status status enum('Active', 'active', 'inactive') NOT NULL DEFAULT 'active';
UPDATE survey SET status = 'active';
ALTER TABLE survey CHANGE COLUMN status status enum('active', 'inactive') NOT NULL DEFAULT 'active';
