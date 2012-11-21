ALTER TABLE `survey_trailer_info`
CHANGE COLUMN `wraparound_id` `trailer_template_id`  int(10) NULL DEFAULT NULL COMMENT 'Links to the survey_id of the trailer template survey record' AFTER `category`;

