ALTER TABLE survey ADD COLUMN start_day int(4) NOT NULL DEFAULT 1;
ALTER TABLE starbar ADD COLUMN launched timestamp DEFAULT CURRENT_TIMESTAMP;

UPDATE survey_response SET processing_status = 'not required' WHERE processing_status = 'pending';