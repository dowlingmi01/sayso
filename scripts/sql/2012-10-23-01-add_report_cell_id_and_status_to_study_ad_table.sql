ALTER TABLE study_ad ADD COLUMN status enum('active', 'inactive') NOT NULL DEFAULT 'active';
ALTER TABLE study_ad ADD COLUMN report_cell_id int(10) DEFAULT NULL;
ALTER TABLE study_ad ADD CONSTRAINT sa_rc_id FOREIGN KEY (report_cell_id) REFERENCES report_cell (id) ON DELETE SET NULL ON UPDATE CASCADE;
