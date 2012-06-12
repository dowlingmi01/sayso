INSERT INTO economy (id, title, name, redeemable_currency, experience_currency) VALUES (3, 'Movie', 'Movie', 'CineBucks', 'CineStars');
UPDATE starbar SET economy_id = 3 WHERE id = 3;
