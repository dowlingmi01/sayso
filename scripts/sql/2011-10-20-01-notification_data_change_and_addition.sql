UPDATE notification_message_group SET minimum_interval = 43205, start_after = 43205, short_name = 'Send every 12 hours after 12 hours' WHERE id = 2;
INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popBoxToOpen) VALUES (null, 'Update Game', 1, null, 1, null, null, 'Message not shown: Update Game Info', null);
