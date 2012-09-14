START TRANSACTION
;
INSERT notification_message
     ( survey_id, notification_message_group_id, ordinal
     , message
     , validate, notification_area, popbox_to_open, color, created )
VALUES
     ( 886, 401, 2
     , 'You have untaken premium surveys! Premium content is worth more XP and Coins.'
     , 'Take Survey', 'alerts', 'surveys', 'Green', now()
     )
;
SET @notif_id = last_insert_id()
;
INSERT notification_message_user_map
     ( user_id, notification_message_id, created )
SELECT v.user_id, @notif_id, now()
  FROM v_report_cell_user_map v, starbar_user_map m
 WHERE v.report_cell_id = 53
   AND m.user_id = v.user_id
   AND m.starbar_id = 4
   AND NOT EXISTS 
     ( SELECT * FROM survey_response sr
        WHERE sr.user_id = v.user_id
          AND sr.survey_id = 886
          AND sr.status = 'completed'
     )
;
COMMIT
;
