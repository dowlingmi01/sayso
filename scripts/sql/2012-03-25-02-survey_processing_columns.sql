ALTER TABLE survey ADD COLUMN processing_status enum('pending', 'completed') NOT NULL DEFAULT 'pending';
ALTER TABLE survey_response CHANGE processing_status enum('not required', 'pending', 'completed') NOT NULL DEFAULT 'not required';
UPDATE survey_response SET processing_status = 'not required';
UPDATE survey_response SET processing_status = 'pending' WHERE status = 'completed' OR status = 'disqualified';
