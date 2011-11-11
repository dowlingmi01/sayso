SELECT @model_id := id FROM developer.model WHERE name = 'sayso';

INSERT developer.developer VALUES (null, 'SurveyGizmo', null, null, now());

INSERT developer.application values (null, last_insert_id(), @model_id, 'SurveyGizmo', '77611de07a95c8fb98ba996c5249d646', 'b9646ce9c366242bd4e99c6aafe19d9d', 'External API', null, null);