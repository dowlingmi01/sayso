ALTER TABLE game_purchasable
  ADD visible enum ('never', 'purchasable', 'instock', 'always') NOT NULL DEFAULT 'instock'
, ADD available           bool          NOT NULL DEFAULT 1
, ADD unavailable_message varchar(2048) NULL
;
CREATE OR REPLACE VIEW game_purchasable_view AS
SELECT id, economy_id, name, bdid, img_url, img_url_preview, img_url_preview_bought
     , p.type, price, visible, available, unavailable_message
  FROM game_asset a, game_purchasable p
 WHERE a.id = p.game_asset_id
   AND a.type = 'purchasable'
;
