UPDATE developer.application SET game = 'Starbar_HelloMusic' WHERE app_key = 'ede421a1d0e08aa672c552e8883e645f';

ALTER TABLE sayso.starbar ADD auth_key varchar(32) AFTER domain;

UPDATE sayso.starbar SET auth_key = '309e34632c2ca9cd5edaf2388f5fa3db' WHERE short_name = 'hellomusic';