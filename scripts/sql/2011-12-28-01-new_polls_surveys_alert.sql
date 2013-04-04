INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (130, 1, 'survey', 'SurveyGizmo', 1, 'Economy', now(), '749765', 'Econmoic-Outlook', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (140, 1, 'survey', 'SurveyGizmo', 1, 'Social Networking', now(), '749751', 'Social-Networking', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (150, 1, 'survey', 'SurveyGizmo', 1, 'Digital Entertainment', now(), '749716', 'Digital-Entertainment', null, 6);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (160, 1, 'survey', 'SurveyGizmo', 1, 'Casual Games', now(), '749708', 'Casual-Games', null, 6);

INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (430, 1, 'poll', 'SurveyGizmo', 1, 'Bob Dylan?', now(), '746457', 'WM7MCAAJH1FCPDHRTSSO5DF0EMEYQ2', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (440, 1, 'poll', 'SurveyGizmo', 1, 'Bob Dylan or Neil Young?', now(), '746499', 'X33NWE9PT7HZ7O60YC0QJ3QPTH6YZ5', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (450, 1, 'poll', 'SurveyGizmo', 1, 'Rush, Yes, or Queensryche?', now(), '746463', 'OVP53KQZ15HZ4HWDMLIMXIBS2728GH', null, 3);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (460, 1, 'poll', 'SurveyGizmo', 1, 'What''s your recording style?', now(), '746438', 'U4XNRFNF5D7QBZN9BX0JFZQS862KU2', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (470, 1, 'poll', 'SurveyGizmo', 1, 'All the gear, no idear?', now(), '746421', '336HV316EJ2NIRQRJ8MR60IE3HXTYO', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (480, 1, 'poll', 'SurveyGizmo', 1, 'Band communication – do you like to talk about each song after you play it?', now(), '746409', '3R92ZX133P51FA1B3H1AIYHJVXZQ1A', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (490, 1, 'poll', 'SurveyGizmo', 1, 'How do you tune your guitar?', now(), '746405', 'TWTYCL7XFYK1BV8YFZXC5YCEZTPXOK', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (500, 1, 'poll', 'SurveyGizmo', 1, 'How many effects pedals do you own?', now(), '746380', '6I90HG0DRR14CK155PRVN5T4OZ2XZS', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (510, 1, 'poll', 'SurveyGizmo', 1, 'Telecaster or Jazzmaster?', now(), '746370', '87999ZWO8C5NP7S3K52YVYXU879HR0', null, 5);

UPDATE notification_message_group SET end_at = '2011-12-20 00:00:00' WHERE short_name LIKE 'Send Once 2011-12-13';
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (9, 'Send Once 2011-12-28', 'Scheduled', 1, 1, null, null, null);

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'New Stuff 2011-12-28', 1, 1, 9, 'Taken Survey', null, 'You have NEW polls and surveys!', 'surveys');
