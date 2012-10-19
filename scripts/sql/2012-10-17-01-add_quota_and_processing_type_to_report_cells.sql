ALTER TABLE report_cell ADD COLUMN quota int(4) DEFAULT NULL;
ALTER TABLE report_cell ADD COLUMN processing_type enum('automatic', 'manual') NOT NULL DEFAULT 'automatic';
