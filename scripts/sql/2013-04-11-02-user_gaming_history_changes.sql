INSERT game_asset
     ( economy_id, type, name, bdid )
SELECT DISTINCT 2, 'purchasable', concat('Snakkle good id ', cast(h.good_id as char)), h.good_id
  FROM user_gaming u, user_gaming_order_history h
 WHERE u.id = h.user_gaming_id
   AND u.starbar_id = 2
;
ALTER TABLE user_gaming_order_history
  ADD user_id             int NULL
, ADD game_asset_id       int NULL
, ADD game_transaction_id int NULL
;
UPDATE user_gaming_order_history h, user_gaming g
   SET h.user_id = g.user_id
 WHERE g.id = h.user_gaming_id
;
UPDATE user_gaming_order_history h, game_asset a
   SET h.game_asset_id = a.id
 WHERE h.good_id = a.bdid
;  
ALTER TABLE user_gaming_order_history
  DROP KEY ugoh_ug_id
, DROP FOREIGN KEY ugoh_ug_id
, DROP COLUMN user_gaming_id
, DROP COLUMN good_id
, CHANGE user_id       user_id       int NOT NULL
, CHANGE game_asset_id game_asset_id int NOT NULL
, ADD KEY ugoh_user_id ( user_id )
, ADD CONSTRAINT ugoh_user_id FOREIGN KEY (user_id) REFERENCES user (id)
, ADD CONSTRAINT ugoh_game_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
, ADD CONSTRAINT ugoh_game_transaction_id FOREIGN KEY (game_transaction_id) REFERENCES game_transaction (id)
;
