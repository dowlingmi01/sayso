INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (401, 'User Actions', 'User Actions', 1, 4, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (402, 'Send every 12 hours after 12 hours', 'Scheduled', 1, 4, 1, 43405, 43405);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (403, 'Send once upon joining', 'Scheduled', 1, 4, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (404, 'Send once after a week', 'Scheduled', 1, 4, null, null, 604800);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES
('alerts', 'Level Up', 1, null, 401, null, null, null, 'user-level'),
('alerts', 'FB Account Connected', 1, null, 401, null, null, 'Sweet! Linking your Facebook account just earned you more XP and Coins!', null),
('alerts', 'TW Account Connected', 1, null, 401, null, null, 'Awesome, you just connected your Twitter account! More Coins and XP!', null),

('alerts', 'Checking in', 1, null, 402, null, 10, 'Dedication is rewarding - Earn by checking in each day!', null),
('alerts', 'New Trailers', 1, null, 402, 'New Trailers', 20, 'You have new Machinima videos!', null),

('alerts', 'Welcome', 1, null, 403, null, 10, 'Welcome to Machinima | Recon!\nWhere your say-so earns pay-so!', null),
('alerts', 'Associate your FB Account', 1, null, 403, 'Facebook Connect', 20, 'Link your Facebook Account to earn more Coins and XP!', 'user-profile'),

(null, 'Update Game', 1, null, 401, null, null, 'Message not shown: Update Game Info', null);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
SELECT
'alerts', 'Profile Survey Reminder', 1, s.id, 404, 'Take Survey', 10, 'Take the Profile Survey and earn 150 Coins and 2000 XP!', null
FROM survey s INNER JOIN starbar_survey_map ssm ON s.id = ssm.survey_id AND ssm.starbar_id = 4 WHERE reward_category = 'profile';
