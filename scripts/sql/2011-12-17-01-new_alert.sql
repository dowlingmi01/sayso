INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, end_at) VALUES (8, 'Send Once 2011-12-17', 'Scheduled', 1, 1, null, null, '2011-12-19 00:00:00');

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'Possible Downtime 2011-12-17', 1, null, 8, null, null, 'Some members will see the Music Bar for a day or two while we run some tests. We may take it offline again, FYI.', null);
