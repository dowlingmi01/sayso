INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (1, 'User Actions', 'User Actions', 1, 1, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (2, 'Send daily after 1 day', 'Scheduled', 1, 1, 1, 86400, 86400);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (3, 'Send once upon joining', 'Scheduled', 1, 1, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (4, 'Send once after a week', 'Scheduled', 1, 1, null, null, 604800);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (5, 'Send daily immeediate', 'Scheduled', 1, 1, 1, 86400, null);
INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popBoxToOpen)
VALUES
('alerts', 'Level Up to 2', 1, null, 1, null, null, 'Nice! You just moved up to Busker Level. Keep say-soing!', null),
('alerts', 'Level Up to 3', 1, null, 1, null, null, 'Welcome to Band Member status! You just leveled up!', null),
('alerts', 'FB Account Connected', 1, null, 1, null, null, 'Sweet! You just linked up your Facebook account! You just made 750 Chops and 75 Notes!', null),
('alerts', 'TW Account Connected', 1, null, 1, null, null, 'Awesome, you just connected your Twitter account! That''s 750 Chops and 75 Notes for you!', null),

('alerts', 'Checking in', 1, null, 2, null, null, 'Dedication is rewarding - Earn by checking in each day!', null),

('alerts', 'Welcome', 1, null, 3, null, 10, 'Welcome to the Beat Bar!\nWhere your say-so earns pay-so!', null),
('alerts', 'Associate your FB Account', 1, null, 3, 'Facebook Connect', 20, 'Link your Facebook Account to earn 750 Chops and 75 Notes!', 'user-profile'),
('alerts', 'Premium Survey Reminder', 1, 1, 4, 'Take Survey', 10, 'Take the Premium Survey and earn up to 2500 Chops and 250 Notes!', null),

('promos', 'Daily Deals', 1, null, 5, null, null, 'You have 4 new Daily Deals', 'daily-deals');
