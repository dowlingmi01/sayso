UPDATE notification_message_group SET end_at = '2012-01-31 00:00:00' WHERE short_name LIKE 'Send Once 2012-01-06';
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (11, 'Send Once 2012-01-06', 'Scheduled', 1, 1, null, null, null);

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'Shutting Down 2012-01-06', 1, null, 11, null, 1, 'We are updating the Music Bar and will be offline for the next couple of weeks. We will send you an email with details soon. Check our Facebook page for more updates!', null);
