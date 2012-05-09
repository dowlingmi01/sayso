INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (201, 'User Actions', 'User Actions', 1, 2, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (202, 'Send every 12 hours after 12 hours', 'Scheduled', 1, 2, 1, 43205, 43205);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (203, 'Send once upon joining', 'Scheduled', 1, 2, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (204, 'Send once after a week', 'Scheduled', 1, 2, null, null, 604800);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES
('alerts', 'Level Up', 1, null, 201, null, null, null, 'user-levels'),
('alerts', 'FB Account Connected', 1, null, 201, null, null, 'Sweet! Linking your Facebook account just earned you more Snakkle Bucks and Stars!', null),
('alerts', 'TW Account Connected', 1, null, 201, null, null, 'Awesome, you just connected your Twitter account! More Snakkle Bucks and Stars!', null),

('alerts', 'Checking in', 1, null, 202, null, 10, 'Dedication is rewarding - Earn by checking in each day!', null),
('alerts', 'New Quizzes', 1, null, 202, null, 20, 'You have new Quizzes!', null),

('alerts', 'Welcome', 1, null, 203, null, 10, 'Welcome to Snakkle Say.So!\nWhere your say-so earns pay-so!', null),
('alerts', 'Associate your FB Account', 1, null, 203, 'Facebook Connect', 20, 'Link your Facebook Account to earn more Snakkle Bucks and Stars!', 'user-profile'),
(null, 'Update Game', 1, null, 201, null, null, 'Message not shown: Update Game Info', null);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
SELECT
'alerts', 'Profile Survey Reminder', 1, id, 204, 'Take Survey', 10, 'Take the Profile Survey and earn 150 Snakkle Bucks and 2000 Stars!', null
FROM survey WHERE reward_category = 'profile';
