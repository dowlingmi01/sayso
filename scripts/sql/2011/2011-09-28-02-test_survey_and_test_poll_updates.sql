SET foreign_key_checks = 0;

INSERT INTO  `sayso`.`survey` (`id` ,`user_id` ,`type` ,`origin` ,`starbar_id` ,`title` ,`created` ,`modified` ,`external_id` ,`external_key` ,`premium`)
VALUES (NULL ,  '1',  'survey',  'SurveyGizmo',  '1',  'Survey V12',  now(),  '0000-00-00 00:00:00',  '651152',  'Survey-V12',  '1');

UPDATE survey SET number_of_answers = 4 WHERE type = 'poll';

SET foreign_key_checks = 1;
