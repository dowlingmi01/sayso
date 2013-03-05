INSERT economy
     ( id, title, name, redeemable_currency, experience_currency)
VALUES
     ( 6, 'Music', 'Music', 'Music PaySos', 'Music SaySo');
;

INSERT starbar
     ( short_name, label, user_pseudonym, domain
     , auth_key, flags, created, modified, economy_id
     )
VALUES
     ( 'music', 'Music Say.So', 'Music Lover', 'say.so'
     , '309e34632c2ca9cd5edaf2388f5fa3db', 'adjuster_ads', now(), now(), 6
     )
;

INSERT INTO starbar_content (starbar_id, starbar_content_key_id, content)
SELECT 6, starbar_content_key_id, content FROM starbar_content WHERE starbar_id = 5;
