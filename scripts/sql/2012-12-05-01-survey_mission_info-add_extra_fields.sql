ALTER TABLE `survey_mission_info`
ADD COLUMN `description`  longtext NULL AFTER `number_of_stages`,
ADD COLUMN `preview_image` varchar(255);
