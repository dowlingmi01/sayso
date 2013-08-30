CREATE TABLE starbar_valid_email
     ( id         int          NOT NULL AUTO_INCREMENT
     , starbar_id int          NOT NULL
     , email      varchar(255) NOT NULL
     , first_name varchar(64)  DEFAULT NULL
     , last_name  varchar(64)  DEFAULT NULL
     , PRIMARY KEY(id)
     , UNIQUE KEY (starbar_id, email)
     )
;
