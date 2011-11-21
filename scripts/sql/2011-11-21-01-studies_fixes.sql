SET foreign_key_checks = 0;

ALTER TABLE study ADD study_id VARCHAR( 16 ) NOT NULL AFTER name ;
ALTER TABLE study ADD study_type TINYINT UNSIGNED NOT NULL DEFAULT '1' AFTER id;

UPDATE sayso.lookup_social_activity_type SET label = 'Facebook Like',
description = 'Facebook Like button clicks' WHERE lookup_social_activity_type.id =1;

UPDATE sayso.lookup_social_activity_type SET label = 'Twitter',
description = 'Twitter tweets' WHERE lookup_social_activity_type.id =2;

SET foreign_key_checks = 1;