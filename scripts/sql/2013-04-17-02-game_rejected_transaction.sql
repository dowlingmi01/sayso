CREATE TABLE game_rejected_transaction
     ( id                                int           NOT NULL AUTO_INCREMENT
     , game_transaction_type_id          smallint      NOT NULL
     , user_id                           int           NOT NULL
     , survey_id                         int           NULL
     , parameters                        varchar(2000) NULL
     , ts                                timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , status_code                       smallint      NOT NULL
     , PRIMARY KEY (id)
     )
;
