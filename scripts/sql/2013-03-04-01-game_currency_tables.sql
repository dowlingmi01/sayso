ALTER TABLE economy DROP redeemable_currency_id, DROP experience_currency_id
;
CREATE TABLE game_currency_type
     ( id                                int           NOT NULL
     , short_name                        varchar(50)   NOT NULL
     , PRIMARY KEY (id)
     )
;
CREATE TABLE game_currency
     ( game_asset_id                     int           NOT NULL
     , game_currency_type_id             int           NOT NULL
     , PRIMARY KEY (game_asset_id)
     , CONSTRAINT game_currency_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     , CONSTRAINT game_currency_currency_type_id FOREIGN KEY (game_currency_type_id) REFERENCES game_currency_type (id)
     )
;
CREATE OR REPLACE VIEW game_currency_view AS
SELECT a.id, economy_id, name, bdid, c.game_currency_type_id, t.short_name
  FROM game_asset a, game_currency c, game_currency_type t
 WHERE a.id = c.game_asset_id
   AND t.id = c.game_currency_type_id
   AND a.type = 'currency'
;
