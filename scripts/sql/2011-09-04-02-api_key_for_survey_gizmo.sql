USE developer;

SELECT @model_id := id FROM model WHERE name = 'sayso';

INSERT developer VALUES (null, 'SurveyGizmo', null, null, now());

INSERT application values (null, last_insert_id(), @model_id, 'SurveyGizmo', '77611de07a95c8fb98ba996c5249d646', 'b9646ce9c366242bd4e99c6aafe19d9d', 'External API', null, null);

USE sayso;