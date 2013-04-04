DROP TABLE external_user_install;

CREATE TABLE user_install
     ( id                   int(10)      NOT NULL AUTO_INCREMENT
     , external_user_id     int(10)      DEFAULT NULL
     , user_id              int(10)      DEFAULT NULL
     , starbar_id           int(10)      DEFAULT NULL
     , location             varchar(32)  NOT NULL
     , token                varchar(32)  NOT NULL
     , ip_address           varchar(255) NOT NULL
     , user_agent           varchar(255) NOT NULL
     , user_agent_supported tinyint      NOT NULL
     , origination          varchar(64)  NOT NULL
     , url                  varchar(512) NOT NULL
     , referrer             varchar(512) DEFAULT NULL
     , client_data          varchar(2048) DEFAULT NULL
     , visited_ts           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP
     , click_ts             timestamp    NULL
     , first_access_ts      timestamp    NULL
     , created              timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , modified             timestamp    NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , FOREIGN KEY (starbar_id) REFERENCES starbar (id) ON DELETE SET NULL ON UPDATE CASCADE
     , FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE
     , FOREIGN KEY (external_user_id) REFERENCES external_user (id) ON DELETE SET NULL ON UPDATE CASCADE
     , UNIQUE KEY(token)
     )
;
