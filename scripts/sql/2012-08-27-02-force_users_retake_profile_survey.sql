START TRANSACTION
;
INSERT notification_message
     ( survey_id, notification_message_group_id, ordinal
     , message
     , validate, notification_area, popbox_to_open, color, created )
VALUES
     ( 71, 401, 10
     , 'WHOOPS! We made a mistake! You may qualify for additional PREMIUM CONTENT and COINS!'
     , 'Take Survey', 'alerts', NULL, 'Green', now()
     ),
     ( 71, 401, 20
     , 'Take the FULL PROFILE SURVEY to see if you qualify. It only takes a few minutes and you will need to take it to re-unlock the Reward Center! Sorry!'
     , 'Take Survey', 'alerts', 'surveys', 'Green', now()
     )
;
SET @notif_id = last_insert_id()
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT user_id, @notif_id, now()
  FROM user_disqual_from_primary_survey
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT user_id, @notif_id + 1, now()
  FROM user_disqual_from_primary_survey
;
DELETE FROM sr
 USING survey_response sr, user_disqual_from_primary_survey ud
 WHERE sr.id = ud.survey_response_id
;
COMMIT
;
