SET foreign_key_checks = 0;


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630742', 'Hamza-s-Test-Survey', 'Survey', 1, 'The Jazziest Survey of All TIme');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630743', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Survey', NULL, 'Do you play electric or acoustic instruments?');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630744', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Survey', NULL, 'Bob Dylan pre or post Newport?');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'complete');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630745', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Survey', 1, 'Favorite Decade in Modern Music');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'complete');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630746', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Survey', NULL, 'Guitar Center or the Corner Music Shop');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'archive');


INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, `type`, premium, title) VALUES
(1, 'SurveyGizmo', 1, now(), '630747', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Survey', 1, 'Your First Digital Music Player');

INSERT INTO survey_user_map (survey_id, user_id, created, status) VALUES (last_insert_id(), 1, now(), 'archive');


SET foreign_key_checks = 1;
