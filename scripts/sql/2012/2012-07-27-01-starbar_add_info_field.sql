ALTER TABLE starbar ADD COLUMN info varchar(4000) DEFAULT '';
UPDATE starbar SET info = CONCAT(label + '<br>Information about this community can go here. Information about this community can go here.');
