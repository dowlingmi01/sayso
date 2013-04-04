ALTER TABLE study_tag CHANGE type type enum('Image', 'Flash', 'Facebook', 'Avail') DEFAULT 'Image';
ALTER TABLE study_creative CHANGE type type enum('Image', 'Flash', 'Facebook', 'HTML') DEFAULT 'Image';
ALTER TABLE study CHANGE name name VARCHAR(1000) DEFAULT NULL;
ALTER TABLE study CHANGE study_id study_id VARCHAR(1000) DEFAULT NULL;
ALTER TABLE study_cell CHANGE description description VARCHAR(1000) DEFAULT NULL;
ALTER TABLE study_tag CHANGE name name VARCHAR(1000) DEFAULT NULL;
ALTER TABLE study_creative CHANGE name name VARCHAR(1000) DEFAULT NULL;
