ALTER TABLE game_level
  ADD img_url_small          varchar(1024) DEFAULT NULL
;
ALTER TABLE game_asset
  ADD img_url_preview        varchar(1024) DEFAULT NULL
, ADD img_url_preview_bought varchar(1024) DEFAULT NULL
;
CREATE OR REPLACE VIEW game_purchasable_view AS
SELECT id, economy_id, name, bdid, img_url, img_url_preview, img_url_preview_bought, p.type, price
  FROM game_asset a, game_purchasable p
 WHERE a.id = p.game_asset_id
   AND a.type = 'purchasable'
;
UPDATE game_level
   SET img_url       = concat('http://media.saysollc.com/media/moviebar/Level', ordinal,'_B.png')
     , img_url_small = concat('http://media.saysollc.com/media/moviebar/Level', ordinal,'_S.png')
 WHERE game_asset_id = 56
;
UPDATE game_level
   SET img_url       = concat('http://media.saysollc.com/media/machinima/Level', ordinal,'_B.png')
     , img_url_small = concat('http://media.saysollc.com/media/machinima/Level', ordinal,'_S.png')
 WHERE game_asset_id = 57
;
UPDATE game_level
   SET img_url       = concat('http://media.saysollc.com/media/socialbar/Level', ordinal,'_B.png')
     , img_url_small = concat('http://media.saysollc.com/media/socialbar/Level', ordinal,'_S.png')
 WHERE game_asset_id = 58
;
UPDATE game_level
   SET img_url       = concat('http://media.saysollc.com/media/musicbar/Level', ordinal,'_B.png')
     , img_url_small = concat('http://media.saysollc.com/media/musicbar/Level', ordinal,'_S.png')
 WHERE game_asset_id = 77
;
