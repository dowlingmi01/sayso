SET foreign_key_checks = 0;


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '636055', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', 1, 'Do you play electric or acoustic instruments?');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '636056', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', NULL, 'Bob Dylan pre or post Newport?');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'complete');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '636057', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', 1, 'Favorite Decade in Modern Music');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'complete');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '636058', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', NULL, 'Guitar Center or the Corner Music Shop');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'archive');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '636059', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', 1, 'Your First Digital Music Player');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'archive');


SET foreign_key_checks = 1;
