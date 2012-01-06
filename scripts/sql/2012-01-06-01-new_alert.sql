UPDATE notification_message_group SET end_at = '2012-01-05 00:00:00' WHERE short_name LIKE 'Send Once 2011-12-28';
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (10, 'Send Once 2012-01-06', 'Scheduled', 1, 1, null, null, null);

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'Updates Soon 1 2012-01-06', 1, null, 10, null, 1, 'We are working on the possibility of extending the Music Bar program -- which would include ongoing content, special deals and rewards. More information for you next week!', null);

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'Updates Soon 2 2012-01-06', 1, null, 10, null, 2, 'We are working on the possibility of extending the Music Bar program -- which would include ongoing content, special deals and rewards. More information for you next week!', null);
