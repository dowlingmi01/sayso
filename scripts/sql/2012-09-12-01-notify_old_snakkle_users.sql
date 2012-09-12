START TRANSACTION
;
INSERT notification_message
     ( survey_id, notification_message_group_id, ordinal
     , message
     , notification_area, popbox_to_open, color, created )
VALUES
     ( 71, 301, 3
     , 'Welcome to Movie Say.So! Your say-so snakk-le has become an all-things-movies meal. Check out new content and rewards!'
     , 'alerts', NULL, 'Green', now()
     ),
     ( 71, 301, 4
     , 'To claim the bucks and stars you earned in Snakkle Say.So, send an email to info@say.so with subject CINEBUCKS. We\'ll get you sorted!'
     , 'alerts', NULL, 'Green', now()
     ),
     ( 71, 301, 5
     , 'Snakkle Say.So has become Movie Say.So. All new content, rewards and giveaways!'
     , 'alerts', NULL, 'Green', now()
     ),
     ( 71, 301, 6
     , 'Convert your Snakkle Bucks to CineBucks. Email info@say.so with the subject line "CINEBUCKS"'
     , 'alerts', NULL, 'Green', now()
     )
;
SET @notif_id = last_insert_id();
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT id, @notif_id, now()
  FROM user u
 WHERE originating_starbar_id = 2;
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT id, @notif_id+1, now()
  FROM user u
 WHERE originating_starbar_id = 2;
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT id, @notif_id+2, now()
  FROM user u
 WHERE originating_starbar_id = 2;
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT id, @notif_id+3, now()
  FROM user u
 WHERE originating_starbar_id = 2;
;
COMMIT
;
