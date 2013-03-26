ALTER TABLE survey CHANGE COLUMN size size enum('small', 'large', 'huge') NOT NULL DEFAULT 'small';
