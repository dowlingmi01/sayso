TRUNCATE TABLE study_tag;

ALTER TABLE study_tag CHANGE content tag varchar(255), ADD UNIQUE KEY tag_unique (tag);

/**
 * Purpose of all this is just to change two column names.
 * It's actually easier to just do new creates wrapped in 
 * no foreign key checks, then to do alter statements
 */
SET foreign_key_checks = 0;

DROP TABLE IF EXISTS study_search_engines_map;
DROP TABLE IF EXISTS study_social_activity_type_map; 

CREATE TABLE study_search_engines_map (
    study_id int(10) NOT NULL,
    search_engines_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_id, search_engines_id),
    CONSTRAINT search_engine_map_study FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT search_engine_map_search_engine FOREIGN KEY (search_engines_id) REFERENCES lookup_search_engines (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_social_activity_type_map (
    study_id int(10) NOT NULL,
    social_activity_type_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_id, social_activity_type_id),
    CONSTRAINT social_activity_map_study FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT social_activity_map_social_activity FOREIGN KEY (social_activity_type_id) REFERENCES lookup_social_activity_type (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



ALTER TABLE study_survey ADD UNIQUE KEY (url);

ALTER TABLE study_survey_criterion ADD UNIQUE KEY (site, timeframe_id);

/**
 * Refactor ER for surveys. 
 * Basically, allow surveys to be re-used, create a mapping
 * table so that each study can have a survey which may be
 * used by other studies as well.
 */

/* remove study_id from study_survey */
ALTER TABLE study_survey DROP FOREIGN KEY study_survey_study_id, DROP KEY study_survey_study_id, DROP study_id;

/* remove study_survey_id from study_survey_criterion */
ALTER TABLE study_survey_criterion DROP FOREIGN KEY study_survey_criterion_study_survey_id, DROP KEY study_survey_criterion_study_survey_id, DROP study_survey_id;

CREATE TABLE study_survey_map (
    id int(10) NOT NULL PRIMARY KEY auto_increment,
    study_id int(10) NOT NULL,
    survey_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_id, survey_id),
    CONSTRAINT survey_map_study_id FOREIGN KEY (study_id) REFERENCES study (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT survey_map_survey_id FOREIGN KEY (survey_id) REFERENCES study_survey (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE study_survey_criterion_map (
    study_survey_map_id int(10) NOT NULL,
    survey_criterion_id int(10) NOT NULL,
    UNIQUE KEY map_unique (study_survey_map_id, survey_criterion_id),
    CONSTRAINT survey_criterion_map_study_survey_map_id FOREIGN KEY (study_survey_map_id) REFERENCES study_survey_map (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT survey_criterion_survey_criterion_id FOREIGN KEY (survey_criterion_id) REFERENCES study_survey_criterion (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;