ALTER TABLE survey_question ADD CONSTRAINT sq_s_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE;
DELETE FROM survey_response WHERE survey_id NOT IN (SELECT id FROM survey);
ALTER TABLE survey_response ADD CONSTRAINT sr_s_id FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE survey_question_response DROP FOREIGN KEY src_sq_id;
ALTER TABLE survey_question_response DROP FOREIGN KEY src_sqc_id;
ALTER TABLE survey_question_response ADD CONSTRAINT src_sq_id FOREIGN KEY (survey_question_id) REFERENCES survey_question (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE survey_question_response ADD CONSTRAINT src_sqc_id FOREIGN KEY (survey_question_choice_id) REFERENCES survey_question_choice (id) ON DELETE RESTRICT ON UPDATE CASCADE;
