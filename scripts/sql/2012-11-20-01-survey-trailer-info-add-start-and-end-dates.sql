ALTER TABLE  `survey_trailer_info` ADD  `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER  `entertainment_title`;
ALTER TABLE  `survey_trailer_info` ADD  `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER  `entertainment_title`;
