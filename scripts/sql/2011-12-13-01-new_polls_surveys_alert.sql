INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_questions)
VALUES (110, 1, 'survey', 'SurveyGizmo', 1, 'Technology at Home', now(), '749646', 'HOUSEHOLD-TECH', null, 6);

INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (400, 1, 'poll', 'SurveyGizmo', 1, 'Iron Maiden or Metallica?', now(), '746459', 'V693CK5A4BZ7GYAB2A2ZTL31M47E11', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (410, 1, 'poll', 'SurveyGizmo', 1, 'Led Zeppelin or The Who?', now(), '746464', 'B7Y0LJZZK91CXY9W7HLLYD94VDGA31', null, 2);
INSERT INTO survey (ordinal, user_id, type, origin, starbar_id, title, created, external_id, external_key, premium, number_of_answers)
VALUES (420, 1, 'poll', 'SurveyGizmo', 1, 'In the studio, do you prefer recording to a click track or not?', now(), '751318', 'TC37CKV3AD8XH09OEJVAX78VM77AFW', null, 3);

UPDATE notification_message_group SET end_at = '2011-12-13 00:00:00' WHERE short_name LIKE 'Send Once 2011-12-09';
INSERT INTO notification_message_group (id, short_name, type, user_id, starbar_id, repeats, minimum_interval, start_after) VALUES (7, 'Send Once 2011-12-13', 'Scheduled', 1, 1, null, null, null);

INSERT INTO notification_message (notification_area, short_name, user_id, survey_id, notification_message_group_id, validate, ordinal, message, popbox_to_open)
VALUES ('alerts', 'New Stuff 2011-12-13', 1, 1, 7, 'Taken Survey', null, 'You have NEW polls and surveys!', 'surveys');
