INSERT survey
     ( type, origin, title, created, report_cell_id )
VALUES
     ( 'mission', 'internal', 'Rise of the Guardians', now(), 1 )
;
SET @survey_id = last_insert_id();
INSERT starbar_survey_map
     ( starbar_id, survey_id )
VALUES
     ( 4, @survey_id )
;
INSERT survey_mission_info
     ( survey_id, short_name, number_of_stages )
VALUES
     ( @survey_id, 'riseoftheguardians', 5)
;
