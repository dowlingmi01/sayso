INSERT economy
     ( id, title, name, redeemable_currency, experience_currency)
VALUES
     ( 5, 'Social', 'Social', 'Bucks', 'Stars');
;
INSERT starbar 
     ( short_name, label, user_pseudonym, domain
     , auth_key, flags, created, modified, economy_id
     )
VALUES
     ( 'social', 'Social', 'Social Gamer', 'say.so'
     , '309e34632c2ca9cd5edaf2388f5fa3db', 'adjuster_ads', now(), now(), 5
     )
;
