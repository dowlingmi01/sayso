INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (501, 'User Actions', 'User Actions', 1, 5, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (502, 'Send every 12 hours after 12 hours', 'Scheduled', 1, 5, 1, 43305, 43305);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (503, 'Send once upon joining', 'Scheduled', 1, 5, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (504, 'Send once after a week', 'Scheduled', 1, 5, null, null, 604800);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES
('alerts', 'Level Up', 1, null, 501, null, null, null, 'user-level'),
('alerts', 'FB Account Connected', 1, null, 501, null, null, 'Sweet! Linking your Facebook account just earned you more PaySos and SaySo!', null),
('alerts', 'TW Account Connected', 1, null, 501, null, null, 'Awesome, you just connected your Twitter account! More PaySos and SaySo!', null),

('alerts', 'Checking in', 1, null, 502, null, 10, 'Dedication is rewarding - Earn by checking in each day!', null),
('alerts', 'New Missions', 1, null, 502, 'New Missions', 20, 'You have new Missions!', null),

('alerts', 'Welcome', 1, null, 503, null, 10, 'Welcome to Social Say.So!\nWhere your say-so earns you pay-so!', null),
('alerts', 'Associate your FB Account', 1, null, 503, 'Facebook Connect', 20, 'Link your Facebook Account to earn more PaySos and SaySo!', 'user-profile'),

(null, 'Update Game', 1, null, 501, null, null, 'Message not shown: Update Game Info', null);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
SELECT
'alerts', 'Profile Survey Reminder', 1, s.id, 504, 'Take Survey', 10, 'Take the Profile Survey and earn 150 PaySos and 2000 SaySo!', null
FROM survey s INNER JOIN starbar_survey_map ssm ON s.id = ssm.survey_id AND ssm.starbar_id = 5 WHERE reward_category = 'profile';
