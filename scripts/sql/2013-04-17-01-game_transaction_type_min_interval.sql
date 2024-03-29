ALTER TABLE game_transaction_type
  ADD min_interval int NOT NULL DEFAULT 0
;
UPDATE game_transaction_type
   SET min_interval = -1
 WHERE short_name IN
     ( 'FACEBOOK_ASSOCIATE', 'FB_LIKE_BRAND', 'FB_POLL_PREMIUM_SHARE'
     , 'FB_POLL_STANDARD_SHARE', 'FB_SURVEY_PREMIUM_SHARE', 'FB_SURVEY_PROFILE_SHARE'
     , 'FB_SURVEY_STANDARD_SHARE', 'FB_TRAILER_STANDARD_SHARE', 'STARBAR_OPT_IN'
	 , 'TW_POLL_PREMIUM_SHARE', 'TW_POLL_STANDARD_SHARE', 'TW_SURVEY_PREMIUM_SHARE'
     , 'TW_SURVEY_PROFILE_SHARE', 'TW_SURVEY_STANDARD_SHARE', 'TW_TRAILER_STANDARD_SHARE'
     , 'TWITTER_ASSOCIATE'
     )
;
UPDATE game_transaction_type
   SET min_interval = 1440
 WHERE short_name IN
     ( 'FB_SHARE_STARBAR', 'TW_SHARE_STARBAR' )
;
