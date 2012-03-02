ALTER TABLE study_tag ADD type enum('Image', 'Flash', 'Facebook') DEFAULT 'Image';
ALTER TABLE study_creative ADD ad_title VARCHAR(255) DEFAULT NULL;
ALTER TABLE study_creative ADD ad_description VARCHAR(255) DEFAULT NULL;
ALTER TABLE study_creative ADD type enum('Image', 'Flash', 'Facebook') DEFAULT 'Image';
