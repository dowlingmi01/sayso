INSERT INTO economy (id, title, redeemable_currency, experience_currency) VALUES (2, 'Snakkle', 'Snakkle Bucks', 'Stars');
UPDATE starbar SET economy_id = 2 WHERE id = 2;
