RENAME TABLE external_user TO external_user_1, external_user_install TO external_user_install_1;

CREATE TABLE external_user
     ( id         int(10)      NOT NULL AUTO_INCREMENT
     , user_id    int(10)      DEFAULT NULL COMMENT 'This may be null if the ''internal'' user has not been created yet'
     , uuid       varchar(255)
     , uuid_type  enum('integer','email','username','hash')
     , starbar_id int(10)
     , email      varchar(255) DEFAULT NULL
     , username   varchar(255) DEFAULT NULL
     , first_name varchar(64)  DEFAULT NULL
     , last_name  varchar(64)  DEFAULT NULL
     , created    timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , modified   timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , UNIQUE KEY (starbar_id, uuid)
     , FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE
     , FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE
     )
;

CREATE TABLE external_user_install
     ( id                   int(10)      NOT NULL AUTO_INCREMENT
     , external_user_id     int(10)      NOT NULL
     , token                varchar(32)  NOT NULL
     , ip_address           varchar(255) NOT NULL
     , user_agent           varchar(255) NOT NULL
     , user_agent_supported tinyint      NOT NULL
     , origination          varchar(64)  NOT NULL
     , url                  varchar(512) NOT NULL
     , referrer             varchar(512) DEFAULT NULL
     , visited_ts           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP
     , click_ts             timestamp    NULL
     , first_access_ts      timestamp    NULL
     , created              timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , modified             timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY(id)
     , UNIQUE KEY(token)
     , FOREIGN KEY(external_user_id) REFERENCES external_user(id)
     )
;
CREATE TABLE user_key
     ( id                   int(10)      NOT NULL AUTO_INCREMENT
     , user_id              int(10)      NOT NULL
     , token                varchar(32)  NOT NULL
     , origin               smallint     NOT NULL
     , created              timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , modified             timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY(id)
     , UNIQUE KEY(token)
     , FOREIGN KEY(user_id) REFERENCES user(id)
     )
;
