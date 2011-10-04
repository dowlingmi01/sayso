SELECT 
    m.survey_id AS survey_user_map_survey_id, m.user_id AS survey_user_map_user_id, m.created AS survey_user_map_created,
    m.response_id AS survey_user_map_response_id, m.status AS survey_user_map_status,
    s.id AS survey_id, s.user_id AS survey_user_id, s.`type` AS survey_type, s.origin AS survey_origin,
    s.starbar_id AS survey_starbar_id, s.title AS survey_title, s.created AS survey_created, s.modified AS survey_modified,
    s.external_id AS survey_external_id, s.external_key AS survey_external_key, s.premium AS survey_premium,
    s.number_of_answers AS survey_number_of_answers, s.number_of_questions AS survey_number_of_questions,
    s.ordinal AS survey_ordinal
FROM
    survey_user_map m
LEFT JOIN 
    survey s ON m.survey_id = s.id
WHERE
    m.user_id = @user_id
ORDER BY 
    m.created ASC