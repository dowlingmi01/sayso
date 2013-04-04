SET foreign_key_checks = 0;

INSERT INTO survey (user_id, origin, starbar_id, created, external_id, external_key, type, title)
VALUES (1, 'SurveyGizmo', 1, now(), '636054', '7MG588QL1S7OI911LF7UXQ4AJEH6IB', 'Poll', 'The Most Rocking Poll Ever');

SET foreign_key_checks = 1;
