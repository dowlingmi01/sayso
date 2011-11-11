SELECT @model_id := id FROM developer.model WHERE name = 'sayso';

INSERT developer.developer VALUES (null, 'Hello Music', null, null, now());

INSERT developer.application values (null, last_insert_id(), @model_id, 'Hello Music', 'ede421a1d0e08aa672c552e8883e645f', 'ffdc540a834d777bd490db844e9fb0c0', 'SDK', null, null);