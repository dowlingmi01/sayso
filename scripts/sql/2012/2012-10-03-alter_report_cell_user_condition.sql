ALTER TABLE report_cell_user_condition ADD COLUMN compare_survey_id int(10) DEFAULT NULL;
ALTER TABLE report_cell_user_condition ADD CONSTRAINT rcuc_compare_survey_id FOREIGN KEY (compare_survey_id) REFERENCES survey (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE report_cell_user_condition CHANGE condition_type condition_type enum('single','multiple','choice','string','integer','decimal','monetary','starbar','report_cell','study_ad','survey_status') NOT NULL;
UPDATE report_cell_user_condition SET condition_type = 'single' WHERE condition_type = 'choice';
ALTER TABLE report_cell_user_condition CHANGE condition_type condition_type enum('single','multiple','string','integer','decimal','monetary','starbar','report_cell','study_ad','survey_status') NOT NULL;

ALTER TABLE report_cell_user_condition CHANGE comparison_type comparison_type enum('<','>','=','<=','>=','!=','contains','does not contain','viewed','clicked','in','not in') NOT NULL;
UPDATE report_cell_user_condition SET comparison_type = 'in' WHERE comparison_type = '=' AND (condition_type = 'report_cell' OR condition_type = 'starbar');
UPDATE report_cell_user_condition SET comparison_type = 'not in' WHERE comparison_type = '!=' AND (condition_type = 'report_cell' OR condition_type = 'starbar');

DELETE FROM report_cell_user_condition WHERE compare_survey_question_id = 424;
