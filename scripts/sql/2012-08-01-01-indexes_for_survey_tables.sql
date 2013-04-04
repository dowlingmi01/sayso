CREATE INDEX survey_response_survey_id ON survey_response (survey_id);
CREATE INDEX survey_response_user_id ON survey_response (user_id);
ALTER TABLE survey_response ADD UNIQUE INDEX (survey_id, user_id);
CREATE INDEX starbar_survey_map_survey_id ON starbar_survey_map (survey_id);
CREATE INDEX starbar_survey_map_starbar_id ON starbar_survey_map (starbar_id);
ALTER TABLE starbar_survey_map ADD UNIQUE INDEX (survey_id, starbar_id);
CREATE INDEX survey_type ON survey (type);
