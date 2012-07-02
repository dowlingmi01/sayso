INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (301, 'User Actions', 'User Actions', 1, 3, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (302, 'Send every 12 hours after 12 hours', 'Scheduled', 1, 3, 1, 43305, 43305);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (303, 'Send once upon joining', 'Scheduled', 1, 3, null, null, null);
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (304, 'Send once after a week', 'Scheduled', 1, 3, null, null, 604800);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES
('alerts', 'Level Up', 1, null, 301, null, null, null, 'user-level'),
('alerts', 'FB Account Connected', 1, null, 301, null, null, 'Sweet! Linking your Facebook account just earned you more CineBucks and CineStars!', null),
('alerts', 'TW Account Connected', 1, null, 301, null, null, 'Awesome, you just connected your Twitter account! More CineBucks and CineStars!', null),

('alerts', 'Checking in', 1, null, 302, null, 10, 'Dedication is rewarding - Earn by checking in each day!', null),
('alerts', 'New Trailers', 1, null, 302, 'New Trailers', 20, 'You have new Trailers!', null),

('alerts', 'Welcome', 1, null, 303, null, 10, 'Welcome to Movie Say.So!\nWhere your say-so earns pay-so!', null),
('alerts', 'Associate your FB Account', 1, null, 303, 'Facebook Connect', 20, 'Link your Facebook Account to earn more CineBucks and CineStars!', 'user-profile'),

(null, 'Update Game', 1, null, 301, null, null, 'Message not shown: Update Game Info', null);

INSERT INTO notification_message
(notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
SELECT
'alerts', 'Profile Survey Reminder', 1, s.id, 304, 'Take Survey', 10, 'Take the Profile Survey and earn 150 CineBucks and 2000 CineStars!', null
FROM survey s INNER JOIN starbar_survey_map ssm ON s.id = ssm.survey_id AND ssm.starbar_id = 3 WHERE reward_category = 'profile';
