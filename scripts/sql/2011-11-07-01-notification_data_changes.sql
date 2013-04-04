DELETE FROM notification_message WHERE short_name LIKE 'Level Up to 2';
DELETE FROM notification_message WHERE short_name LIKE 'Level Up to 3';
INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'Level Up', 1, null, 1, null, null, null, 'user-levels');
