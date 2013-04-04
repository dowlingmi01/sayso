START TRANSACTION
;
INSERT notification_message
     ( survey_id, notification_message_group_id, ordinal
     , message
     , validate, notification_area, popbox_to_open, color, created )
VALUES
     ( 71, 201, 1
     , 'WHOOPS! We made a mistake! You may qualify for additional PREMIUM CONTENT and BUCKS!'
     , 'Take Survey', 'alerts', NULL, 'Green', now()
     ),
     ( 71, 301, 1
     , 'WHOOPS! We made a mistake! You may qualify for additional PREMIUM CONTENT and CINEBUCKS!'
     , 'Take Survey', 'alerts', NULL, 'Green', now()
     ),
     ( 71, 401, 1
     , 'WHOOPS! We made a mistake! You may qualify for additional PREMIUM CONTENT and COINS!'
     , 'Take Survey', 'alerts', NULL, 'Green', now()
     ),
     ( 71, 201, 2
     , 'Take the FULL PROFILE SURVEY to see if you qualify. It only takes a few minutes and you will need to take it to re-unlock the Reward Center! Sorry!'
     , 'Take Survey', 'alerts', 'surveys', 'Green', now()
     ),
     ( 71, 301, 2
     , 'Take the FULL PROFILE SURVEY to see if you qualify. It only takes a few minutes and you will need to take it to re-unlock the Reward Center! Sorry!'
     , 'Take Survey', 'alerts', 'surveys', 'Green', now()
     ),
     ( 71, 401, 2
     , 'Take the FULL PROFILE SURVEY to see if you qualify. It only takes a few minutes and you will need to take it to re-unlock the Reward Center! Sorry!'
     , 'Take Survey', 'alerts', 'surveys', 'Green', now()
     )
;
SET @notif_id = last_insert_id()
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT d.user_id, @notif_id + m.starbar_id - 2, now()
  FROM user_disqual_from_primary_survey d, starbar_user_map m
 WHERE d.user_id = m.user_id AND m.starbar_id > 1
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT d.user_id, @notif_id + m.starbar_id + 1, now()
  FROM user_disqual_from_primary_survey d, starbar_user_map m
 WHERE d.user_id = m.user_id AND m.starbar_id > 1
;
DELETE FROM sr
 USING survey_response sr, user_disqual_from_primary_survey ud
 WHERE sr.id = ud.survey_response_id
;
COMMIT
;
