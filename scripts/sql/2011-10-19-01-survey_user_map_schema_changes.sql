ALTER TABLE  `survey_user_map` CHANGE  `status`  `status` ENUM(  'complete',  'archive',  'completed',  'archived',  'new',  'disqualified' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'new';

UPDATE survey_user_map SET status = 'completed' WHERE status = 'complete';
UPDATE survey_user_map SET status = 'archived' WHERE status = 'archive';

ALTER TABLE  `survey_user_map` CHANGE  `status`  `status` ENUM(  'completed',  'archived',  'new',  'disqualified' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'new';
