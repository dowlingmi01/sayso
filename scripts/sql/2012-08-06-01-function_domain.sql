DELIMITER $$
DROP FUNCTION IF EXISTS domain$$
CREATE FUNCTION `domain`(fullurl VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
BEGIN
	-- Function logic here
	DECLARE
		domain VARCHAR(255)DEFAULT NULL ;

set domain = 	substring_index(
		substring_index(
			trim(
				LEADING 'www.'
				FROM
					trim(
						LEADING 'https://'
						FROM
							trim(
								LEADING 'http://'
								FROM
									trim(fullurl)
							)
					)
			),
			'/',
			1
		),
		':',
		1
	);
 RETURN domain ; END
