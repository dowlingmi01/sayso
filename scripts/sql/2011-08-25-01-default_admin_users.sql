

INSERT INTO `user` (id, username, password, password_salt, first_name, last_name, gender_id, ethnicity_id, income_range_id, birthdate, url, timezone, primary_email_id, user_role_id, created) VALUES 
    (null, 'david', md5(concat(md5('12345'),'doon')), 'doon', 'David', 'James', 1, 1, 4, '1969-11-01', 'http://www.davidbjames.info', '+1:00', null, 6, now());

INSERT INTO user_email (id, user_id, email, created) VALUES (null, last_insert_id(), 'david@saysollc.com', now());

UPDATE `user` SET primary_email_id = last_insert_id() WHERE username = 'david';
