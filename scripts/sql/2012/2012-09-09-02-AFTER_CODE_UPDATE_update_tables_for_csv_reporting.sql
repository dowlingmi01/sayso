UPDATE survey_question_response SET response_csv = '1' WHERE data_type = 'choice';
UPDATE survey_question_response SET response_csv = REPLACE(response_string, '"', '\"') WHERE data_type = 'string';
UPDATE survey_question_response SET response_csv = response_integer WHERE data_type = 'integer';
UPDATE survey_question_response SET response_csv = response_decimal WHERE data_type = 'decimal' OR data_type = 'monetary';
