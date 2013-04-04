ALTER TABLE survey CHANGE type type enum('survey','poll','quiz','trailer', 'mission') NOT NULL
;
CREATE TABLE survey_mission_info
     ( id         int(10)      NOT NULL AUTO_INCREMENT
     , survey_id  int(10)      NOT NULL
     , short_name varchar(255) NOT NULL
     , number_of_stages int(4) NOT NULL
     , created    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP
     , modified   timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , UNIQUE KEY survey_mission_info_survey_id (survey_id)
     , UNIQUE KEY survey_mission_info_short_name (short_name)
     , CONSTRAINT survey_mission_info_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE
     )
;
CREATE TABLE survey_mission_progress
     ( id            int(10)      NOT NULL AUTO_INCREMENT
     , survey_id     int(10)      NOT NULL
     , user_id       int(10)      NOT NULL
     , top_frame_id  int(10)      NOT NULL
     , stage         int(10)      NOT NULL
     , created       timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP
     , modified      timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , UNIQUE KEY survey_mission_progress_key (survey_id, user_id, top_frame_id)
     , CONSTRAINT survey_mission_progress_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE RESTRICT ON UPDATE CASCADE
     , CONSTRAINT survey_mission_progress_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE RESTRICT ON UPDATE CASCADE
     )
;
