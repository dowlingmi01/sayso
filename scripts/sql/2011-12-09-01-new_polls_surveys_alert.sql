INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (100, 1, 'survey', 'SurveyGizmo', 1, 'Entertainment', now(), '747900', 'Entertainment', null, 4);

INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (370, 1, 'poll', 'SurveyGizmo', 1, 'How many watts do you need in an amp?', now(), '746432', '10WMVJCQSN554B8J85TX6V1YL83I4Z', null, 4);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (380, 1, 'poll', 'SurveyGizmo', 1, 'Tube amps or solid state?', now(), '746446', 'ZI3TIC1W1MN6VVJT7A9HRH7JIUU36W', null, 5);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (390, 1, 'poll', 'SurveyGizmo', 1, 'Do the newer modeling amps sound like the real thing?', now(), '746455', 'J4EN8HFHL5QWI37D7N2N1Q56AGA9QE', null, 2);

INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (6, 'Send Once 2011-12-09', 'Scheduled', 1, 1, null, null, 600);

ALTER TABLE notification_message CHANGE validate validate enum('Facebook Connect', 'Twitter Connect', 'Take Survey', 'Taken Survey');

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'New Stuff 2011-12-09', 1, 1, 6, 'Taken Survey', null, 'You have NEW polls and surveys!', 'surveys');
