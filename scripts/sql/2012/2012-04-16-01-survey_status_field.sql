ALTER TABLE survey ADD COLUMN status enum('Active', 'Inactive') NOT NULL DEFAULT 'Active';
