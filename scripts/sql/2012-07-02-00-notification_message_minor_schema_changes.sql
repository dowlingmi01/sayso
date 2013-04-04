ALTER TABLE notification_message CHANGE validate validate enum('Facebook Connect', 'Twitter Connect', 'Take Survey', 'Taken Survey', 'New Quizzes', 'New Trailers');
ALTER TABLE notification_message CHANGE short_name short_name VARCHAR(255) DEFAULT NULL COMMENT "To reference in code if necessary";
ALTER TABLE notification_message CHANGE message message VARCHAR(255) DEFAULT NULL;

UPDATE notification_message SET validate = 'New Quizzes' WHERE short_name = 'New Quizzes';
UPDATE notification_message SET validate = 'New Trailers' WHERE short_name = 'New Trailers';

