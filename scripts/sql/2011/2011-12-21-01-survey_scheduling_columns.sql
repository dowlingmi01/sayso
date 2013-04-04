ALTER TABLE survey ADD start_after int(10) DEFAULT NULL COMMENT "Minimum number of seconds after a user joins before they could see this survey/poll";
ALTER TABLE survey ADD start_at timestamp DEFAULT '0000-00-00 00:00:00';
ALTER TABLE survey ADD end_at timestamp DEFAULT '0000-00-00 00:00:00';
