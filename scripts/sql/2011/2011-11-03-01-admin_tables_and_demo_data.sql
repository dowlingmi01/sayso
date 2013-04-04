SET foreign_key_checks = 0;

DROP TABLE IF EXISTS admin_user;
DROP TABLE IF EXISTS admin_role;
DROP TABLE IF EXISTS admin_user_admin_role;

CREATE TABLE IF NOT EXISTS admin_user (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(100) DEFAULT NULL,
  password VARCHAR(32) DEFAULT NULL,
  first_name VARCHAR(64) DEFAULT NULL,
  last_name VARCHAR(64) DEFAULT NULL,
  created datetime NOT NULL,
  modified datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS admin_role (
  id INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(32) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  created datetime NOT NULL,
  modified datetime DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS admin_user_admin_role (
  admin_user_id INT(10) UNSIGNED NOT NULL,
  admin_role_id INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (admin_user_id,admin_role_id),
  KEY admin_role_id (admin_role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE admin_user_admin_role ADD FOREIGN KEY ( admin_role_id ) REFERENCES sayso.admin_role (id) ON DELETE RESTRICT ON UPDATE RESTRICT ;
ALTER TABLE admin_user_admin_role ADD FOREIGN KEY ( admin_user_id ) REFERENCES sayso.admin_user (id) ON DELETE RESTRICT ON UPDATE RESTRICT ;

REPLACE INTO admin_user (id, email, `password`, first_name, last_name, created, modified)
    VALUES ('1', 'admin@email.com', MD5( '12345' ) , 'Super', 'Admin', NOW( ) , NULL);

REPLACE INTO admin_role (id , `name`, description, created, modified)
    VALUES ('1', 'superuser', 'Administrator with all privileges', NOW() , NULL);

REPLACE INTO admin_user_admin_role(admin_user_id, admin_role_id)
    VALUES ('1', '1');

SET foreign_key_checks = 1;
