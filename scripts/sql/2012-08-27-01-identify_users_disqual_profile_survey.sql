CREATE TABLE user_disqual_from_primary_survey
     ( user_id int(10) NOT NULL PRIMARY KEY
     , survey_response_id int(10) NOT NULL
     )
;
INSERT user_disqual_from_primary_survey
     ( user_id, survey_response_id)
SELECT user_id, survey_response_id
  FROM survey_question_response sqr1, survey_response sr
 WHERE sr.id = sqr1.survey_response_id
   AND survey_question_id = 415
   AND NOT EXISTS
     ( SELECT * FROM survey_question_response sqr2
        WHERE sqr2.survey_response_id = sqr1.survey_response_id
          AND sqr2.survey_question_id = 420 )
;
