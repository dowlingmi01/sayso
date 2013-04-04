ALTER TABLE user ADD COLUMN type ENUM('regular', 'test') DEFAULT 'regular' NOT NULL AFTER user_role_id;
UPDATE user u, user_email e SET u.type = 'test' WHERE u.id = e.user_id AND
(e.email LIKE '%@say.so' OR e.email LIKE '%@saysollc.com' OR e.email LIKE '%@interpretllc.com' OR e.email LIKE '%jimbanister%' OR e.email LIKE '%1106%');
