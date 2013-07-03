CREATE TABLE user_password_change_request (
  id int NOT NULL auto_increment,
  user_id int NOT NULL,
  verification_code varchar(20) NOT NULL,
  has_been_fulfilled tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id),
  CONSTRAINT upcr_user_id FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
