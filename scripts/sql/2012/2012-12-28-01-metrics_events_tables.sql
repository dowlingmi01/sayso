CREATE TABLE metrics_event_type
     ( id                                smallint      NOT NULL
     , name                              varchar(50)   NOT NULL
     , PRIMARY KEY (id)
     , UNIQUE KEY name (name)
     )
;
CREATE TABLE metrics_property
     ( id                                smallint      NOT NULL
     , name                              varchar(50)   NOT NULL
     , type  enum('lookup', 'int', 'string', 'double') NOT NULL
     , PRIMARY KEY (id)
     , UNIQUE KEY name (name)
     )
;
CREATE TABLE metrics_property_lookup_value
     ( id                                int           NOT NULL AUTO_INCREMENT
     , metrics_property_id               smallint      NOT NULL
     , value                             varchar(700)  NOT NULL
     , PRIMARY KEY (id)
     , UNIQUE KEY metrics_property_id_value (metrics_property_id, value)
     , CONSTRAINT metrics_property_lookup_value_metrics_property_id
       FOREIGN KEY (metrics_property_id) REFERENCES metrics_property (id)
     )
;
CREATE TABLE metrics_event
     ( id                                bigint        NOT NULL AUTO_INCREMENT
     , metrics_event_type_id             smallint      NOT NULL
     , user_id                           int           NOT NULL
     , ts                                timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , PRIMARY KEY (id)
     , KEY event_type_id (metrics_event_type_id)
     , KEY user_id_metrics_event_type_id (user_id, metrics_event_type_id)
     , CONSTRAINT metrics_event_metrics_event_type_id
       FOREIGN KEY (metrics_event_type_id) REFERENCES metrics_event_type (id)
     , CONSTRAINT metrics_event_user_id FOREIGN KEY (user_id) REFERENCES user (id)
     )
;
CREATE TABLE metrics_event_property_int
     ( metrics_event_id                  bigint        NOT NULL
     , metrics_property_id               smallint      NOT NULL
     , value                             int           NOT NULL
     , PRIMARY KEY (metrics_event_id, metrics_property_id)
     , KEY metrics_property_id_value (metrics_property_id, value)
     , CONSTRAINT metrics_event_property_int_metrics_event_id
       FOREIGN KEY (metrics_event_id) REFERENCES metrics_event (id)
     , CONSTRAINT metrics_event_property_int_metrics_property_id
       FOREIGN KEY (metrics_property_id) REFERENCES metrics_property (id)
     )
;
CREATE TABLE metrics_event_property_string
     ( metrics_event_id                  bigint        NOT NULL
     , metrics_property_id               smallint      NOT NULL
     , value                             varchar(4000) NOT NULL
     , PRIMARY KEY (metrics_event_id, metrics_property_id)
     , KEY metrics_property_id_value (metrics_property_id, value(255))
     , CONSTRAINT metrics_event_property_string_metrics_event_id
       FOREIGN KEY (metrics_event_id) REFERENCES metrics_event (id)
     , CONSTRAINT metrics_event_property_string_metrics_property_id
       FOREIGN KEY (metrics_property_id) REFERENCES metrics_property (id)
     )
;
CREATE TABLE metrics_event_property_double
     ( metrics_event_id                  bigint        NOT NULL
     , metrics_property_id               smallint      NOT NULL
     , value                             double        NOT NULL
     , PRIMARY KEY (metrics_event_id, metrics_property_id)
     , KEY metrics_property_id_value (metrics_property_id, value)
     , CONSTRAINT metrics_event_property_double_metrics_event_id
       FOREIGN KEY (metrics_event_id) REFERENCES metrics_event (id)
     , CONSTRAINT metrics_event_property_double_metrics_property_id
       FOREIGN KEY (metrics_property_id) REFERENCES metrics_property (id)
     )
;
CREATE TABLE metrics_event_property_lookup
     ( metrics_event_id                  bigint        NOT NULL
     , metrics_property_id               smallint      NOT NULL
     , metrics_property_lookup_value_id  int           NOT NULL
     , PRIMARY KEY (metrics_event_id, metrics_property_id)
     , KEY metrics_property_id_value (metrics_property_id, metrics_property_lookup_value_id)
     , CONSTRAINT metrics_event_property_lookup_metrics_event_id
       FOREIGN KEY (metrics_event_id) REFERENCES metrics_event (id)
     , CONSTRAINT metrics_event_property_lookup_metrics_property_id
       FOREIGN KEY (metrics_property_id) REFERENCES metrics_property (id)
     , CONSTRAINT metrics_event_property_lookup_metrics_property_lookup_value_id
       FOREIGN KEY (metrics_property_lookup_value_id) REFERENCES metrics_property_lookup_value (id)
     )
;
CREATE TABLE metrics_event_property_unknown
     ( metrics_event_id                  bigint        NOT NULL
     , property_name                     varchar(50)   NOT NULL
     , value                             varchar(4000) NOT NULL
     , PRIMARY KEY (metrics_event_id, property_name)
     , CONSTRAINT metrics_event_property_unknown_metrics_event_id
       FOREIGN KEY (metrics_event_id) REFERENCES metrics_event (id)
     )
;
INSERT metrics_event_type VALUES
     ( 1, 'unknown' )
;
