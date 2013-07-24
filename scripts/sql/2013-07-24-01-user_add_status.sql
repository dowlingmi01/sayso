ALTER TABLE user ADD COLUMN status enum ('active', 'banned') NOT NULL DEFAULT 'active';
