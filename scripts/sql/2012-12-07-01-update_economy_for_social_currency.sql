UPDATE economy
SET redeemable_currency = 'Social PaySos',
 experience_currency = 'Social SaySos',
 modified = now()
WHERE
	id = 5;