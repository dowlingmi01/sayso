ALTER TABLE economy
  ADD redeemable_currency_id int NULL
, ADD experience_currency_id int NULL
, ADD imported         timestamp NULL
;
ALTER TABLE user_gaming ADD imported timestamp NULL AFTER starbar_id
;
INSERT user (id) VALUES (0)
;
CREATE TABLE game_asset
     ( id                                int           NOT NULL AUTO_INCREMENT
     , economy_id                        int           NOT NULL
     , type   enum('currency', 'level', 'purchasable') NOT NULL
     , name                              varchar(255)  NOT NULL
     , bdid                              int           NULL
     , img_url                           varchar(1024) NULL
     , created                           timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , modified                          timestamp     NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , CONSTRAINT game_asset_economy_id FOREIGN KEY (economy_id) REFERENCES economy (id)
     )
;
CREATE TABLE game_purchasable
     ( game_asset_id                     int           NOT NULL
     , type       enum('physical', 'virtual', 'token') NOT NULL
     , price                             int           NOT NULL
     , PRIMARY KEY (game_asset_id)
     , CONSTRAINT game_purchasable_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     )
;
CREATE TABLE game_transaction_type
     ( id                                smallint      NOT NULL AUTO_INCREMENT
     , economy_id                        int           NOT NULL
     , short_name                        varchar(50)   NOT NULL
     , class                             varchar(50)   NOT NULL
     , name                              varchar(255)  NOT NULL
     , created                           timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , modified                          timestamp     NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , UNIQUE KEY game_transaction_type_economy_short_name (economy_id, short_name)
     , CONSTRAINT game_transaction_type_economy_id FOREIGN KEY (economy_id) REFERENCES economy (id)
     )
;
CREATE TABLE game_transaction_type_line
     ( id                                int           NOT NULL AUTO_INCREMENT
     , game_transaction_type_id          smallint      NOT NULL
     , game_asset_id                     int           NOT NULL
     , amount                            int           NOT NULL
     , PRIMARY KEY (id)
     , CONSTRAINT game_transaction_type_line_transaction_type_id FOREIGN KEY (game_transaction_type_id) REFERENCES game_transaction_type (id)
     , CONSTRAINT game_transaction_type_line_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     )
;
CREATE TABLE game_transaction
     ( id                                int           NOT NULL AUTO_INCREMENT
     , game_transaction_type_id          smallint      NOT NULL
     , user_id                           int           NOT NULL
     , survey_id                         int           NULL
     , parameters                        varchar(2000) NULL
     , ts                                timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , PRIMARY KEY (id)
     , CONSTRAINT game_transaction_transaction_type_id FOREIGN KEY (game_transaction_type_id) REFERENCES game_transaction_type (id)
     , CONSTRAINT game_transaction_survey_id FOREIGN KEY (survey_id) REFERENCES survey (id)
     , CONSTRAINT game_transaction_user_id FOREIGN KEY (user_id) REFERENCES user (id)
     )
;
CREATE TABLE game_transaction_line
     ( id                                int           NOT NULL AUTO_INCREMENT
     , game_transaction_id               int           NOT NULL
     , game_asset_id                     int           NOT NULL
     , previous_balance                  int           NOT NULL DEFAULT 0
     , amount                            int           NOT NULL
     , previous_balance_bd               int           NULL
     , current_balance_bd                int           NULL
     , PRIMARY KEY (id)
     , CONSTRAINT game_transaction_line_transaction_id FOREIGN KEY (game_transaction_id) REFERENCES game_transaction (id)
     , CONSTRAINT game_transaction_line_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     )
;
CREATE TABLE game_balance
     ( user_id                           int           NOT NULL
     , game_asset_id                     int           NOT NULL
     , credits                           int           NOT NULL
     , debits                            int           NOT NULL
     , ts                                timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
     , PRIMARY KEY (user_id, game_asset_id)
     , CONSTRAINT game_balance_user_id FOREIGN KEY (user_id) REFERENCES user (id)
     , CONSTRAINT game_balance_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     )
;
CREATE TABLE game_level
     ( id                                int           NOT NULL AUTO_INCREMENT
     , game_asset_id                     int           NOT NULL
     , ordinal                           int           NOT NULL
     , threshold                         int           NOT NULL
     , name                              varchar(255)  NOT NULL
     , description                       varchar(1024) NOT NULL
     , img_url                           varchar(1024) NULL
     , created                           timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP
     , modified                          timestamp     NOT NULL DEFAULT '0000-00-00 00:00:00'
     , PRIMARY KEY (id)
     , CONSTRAINT game_level_asset_id FOREIGN KEY (game_asset_id) REFERENCES game_asset (id)
     )
;
CREATE OR REPLACE VIEW game_purchasable_view AS
SELECT id, economy_id, name, bdid, img_url, p.type, price
  FROM game_asset a, game_purchasable p
 WHERE a.id = p.game_asset_id
   AND a.type = 'purchasable'
;
DROP TRIGGER IF EXISTS before_insert_game_transaction_line;
DELIMITER //
CREATE TRIGGER before_insert_game_transaction_line BEFORE INSERT ON game_transaction_line
FOR EACH ROW BEGIN
	DECLARE _user_id, _credit, _debit, _previous_balance int DEFAULT 0;
	SELECT user_id FROM game_transaction
	 WHERE id = NEW.game_transaction_id
	  INTO _user_id;
	IF NEW.amount > 0 THEN
		SET _credit = NEW.amount;
	ELSE
		SET _debit = -NEW.amount;
	END IF;

	SELECT credits - debits FROM game_balance
	 WHERE user_id = _user_id AND game_asset_id = NEW.game_asset_id
	  INTO _previous_balance;
	  
	SET NEW.previous_balance = _previous_balance;
	  
	INSERT game_balance
	   ( user_id, game_asset_id, credits, debits )
	VALUES
	   ( _user_id, NEW.game_asset_id, _credit, _debit )
	ON DUPLICATE KEY
	UPDATE credits = credits + _credit
	     , debits = debits + _debit;
	   
	IF _user_id > 0 THEN
		INSERT game_balance
		   ( user_id, game_asset_id, credits, debits )
		VALUES
		   ( 0, NEW.game_asset_id, _debit, _credit )
		ON DUPLICATE KEY
		UPDATE credits = credits + _debit
		     , debits = debits + _credit;
	END IF;
END
//
DELIMITER ;
INSERT game_transaction_type ( economy_id, short_name, class, name )
SELECT id, 'IMPORT_BD_STOCK', 'ImportBDStock', 'Import current stock status from BD'
  FROM economy
 WHERE id > 2
;
INSERT game_transaction_type ( economy_id, short_name, class, name )
SELECT id, 'IMPORT_BD_USER', 'ImportBDUser', 'Import current user status from BD'
  FROM economy
 WHERE id > 2
;
INSERT game_transaction_type ( economy_id, short_name, class, name )
SELECT id, 'PURCHASE', 'Purchase', 'Redeem a good'
  FROM economy
 WHERE id > 2
;
INSERT game_transaction_type ( economy_id, short_name, class, name )
SELECT id, 'LEVEL_UP', '', 'Up one level'
  FROM economy
 WHERE id > 2
;
INSERT game_transaction_type ( economy_id, short_name, class, name )
SELECT id, 'CHECK_BD_LEVEL', '', 'Compare current level with BD level after BD level up'
  FROM economy
 WHERE id > 2
;
INSERT game_asset (economy_id, type, name)
SELECT id, 'level', 'User Level'
  FROM economy
 WHERE id > 2
;
INSERT game_transaction_type_line ( game_transaction_type_id, game_asset_id, amount )
SELECT t.id, l.id, 1
  FROM game_transaction_type t, game_asset l
 WHERE t.economy_id = l.economy_id
   AND t.short_name = 'LEVEL_UP'
   AND l.type = 'level'
   AND t.economy_id > 2
;
INSERT game_transaction_type_line ( game_transaction_type_id, game_asset_id, amount )
SELECT t.id, l.id, 0
  FROM game_transaction_type t, game_asset l
 WHERE t.economy_id = l.economy_id
   AND t.short_name = 'CHECK_BD_LEVEL'
   AND l.type = 'level'
   AND t.economy_id > 2
;
