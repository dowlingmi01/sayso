ALTER TABLE survey ADD COLUMN size enum('small', 'large') NOT NULL DEFAULT 'small';
UPDATE survey SET size = 'large' WHERE id = 1;
