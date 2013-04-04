ALTER TABLE external_user ADD client_data varchar(2048) DEFAULT NULL
;
UPDATE external_user SET client_data = '{ "hm_pilot_user": true }'
;
