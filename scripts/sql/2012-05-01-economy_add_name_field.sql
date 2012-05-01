ALTER TABLE economy ADD COLUMN name varchar(255) NULL;
UPDATE economy SET name = 'HelloMusic' WHERE id = 1;
UPDATE economy SET name = 'Snakkle' WHERE id = 2;
