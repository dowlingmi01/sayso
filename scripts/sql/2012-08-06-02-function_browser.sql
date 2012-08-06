DROP FUNCTION IF EXISTS browser;
CREATE FUNCTION `browser`(user_agent VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
BEGIN
	-- Function logic here
	DECLARE
		webbrowser VARCHAR(255)DEFAULT NULL ; /* Derive the operating system */


IF instr(
		user_agent,
		'MSIE 6.0'
	)THEN

	SET webbrowser = 'IE 6' ;
	END if;

IF instr(
		user_agent,
		'MSIE 7.0'
	)THEN

	SET webbrowser = 'IE 7' ;
	END if;

IF instr(
		user_agent,
		'MSIE 8.0'
	)THEN

	SET webbrowser = 'IE 8' ;
	END if;

IF instr(
		user_agent,
		'MSIE 9.0'
	)THEN

	SET webbrowser = 'IE 9' ;
	END if;

IF instr(
		user_agent,
		'MSIE 10.0'
	)THEN

	SET webbrowser = 'IE 10' ;
	END if;

IF instr(
		user_agent,
		'Safari'
	)THEN

	SET webbrowser = 'Safari' ;
	END if;

IF instr(
		user_agent,
		'Mobile Safari'
	)THEN

	SET webbrowser = 'Mobile Safari' ;
	END if;
IF instr(
		user_agent,
		'iPad'
	)THEN

	SET webbrowser = 'iPad' ;
	END if;
IF instr(
		user_agent,
		'iPhone'
	)THEN

	SET webbrowser = 'iPhone' ;
	END if;

IF instr(
		user_agent,
		'FireFox/1.'
	)THEN

	SET webbrowser = 'Firefox 1' ;
	END if;
IF instr(
		user_agent,
		'FireFox/2.'
	)THEN

	SET webbrowser = 'Firefox 2' ;
	END if;
IF instr(
		user_agent,
		'FireFox/3.'
	)THEN

	SET webbrowser = 'Firefox 3' ;
	END if;
IF instr(
		user_agent,
		'FireFox/4.'
	)THEN

	SET webbrowser = 'Firefox 4' ;
	END if;
IF instr(
		user_agent,
		'FireFox/5.'
	)THEN

	SET webbrowser = 'Firefox 5' ;
	END if;
IF instr(
		user_agent,
		'FireFox/6.'
	)THEN

	SET webbrowser = 'Firefox 6' ;
	END if;
IF instr(
		user_agent,
		'FireFox/7.'
	)THEN

	SET webbrowser = 'Firefox 7' ;
	END if;
IF instr(
		user_agent,
		'FireFox/8.'
	)THEN

	SET webbrowser = 'Firefox 8' ;
	END if;
IF instr(
		user_agent,
		'FireFox/9.'
	)THEN

	SET webbrowser = 'Firefox 9' ;
	END if;
IF instr(
		user_agent,
		'FireFox/10.'
	)THEN

	SET webbrowser = 'Firefox 10' ;
	END if;
IF instr(
		user_agent,
		'FireFox/11.'
	)THEN

	SET webbrowser = 'Firefox 11' ;
	END if;
IF instr(
		user_agent,
		'FireFox/12.'
	)THEN

	SET webbrowser = 'Firefox 12' ;
	END if;
IF instr(
		user_agent,
		'FireFox/13.'
	)THEN

	SET webbrowser = 'Firefox 13' ;
	END if;
IF instr(
		user_agent,
		'FireFox/14.'
	)THEN

	SET webbrowser = 'Firefox 14' ;
	END if;
IF instr(
		user_agent,
		'FireFox/15.'
	)THEN

	SET webbrowser = 'Firefox 15' ;
	END if;
IF instr(
		user_agent,
		'FireFox/15.'
	)THEN

	SET webbrowser = 'Firefox 15' ;
	END if;
IF instr(
		user_agent,
		'FireFox/16.'
	)THEN

	SET webbrowser = 'Firefox 16' ;
	END if;
IF instr(
		user_agent,
		'FireFox/17.'
	)THEN

	SET webbrowser = 'Firefox 17' ;
	END if;
IF instr(
		user_agent,
		'FireFox/18.'
	)THEN

	SET webbrowser = 'Firefox 18' ;
	END if;
IF instr(
		user_agent,
		'FireFox/19.'
	)THEN

	SET webbrowser = 'Firefox 19' ;
	END if;
IF instr(
		user_agent,
		'Opera'
	)THEN

	SET webbrowser = 'Opera' ;
	END if;

IF instr(
		user_agent,
		'Chrome/1.'
	)THEN

	SET webbrowser = 'Chrome 1' ;
	END if;

IF instr(
		user_agent,
		'Chrome/2.'
	)THEN

	SET webbrowser = 'Chrome 2' ;
	END if;
IF instr(
		user_agent,
		'Chrome/3.'
	)THEN

	SET webbrowser = 'Chrome 3' ;
	END if;
IF instr(
		user_agent,
		'Chrome/4.'
	)THEN

	SET webbrowser = 'Chrome 4' ;
	END if;
IF instr(
		user_agent,
		'Chrome/5.'
	)THEN

	SET webbrowser = 'Chrome 5' ;
	END if;
IF instr(
		user_agent,
		'Chrome/6.'
	)THEN

	SET webbrowser = 'Chrome 6' ;
	END if;
IF instr(
		user_agent,
		'Chrome/7.'
	)THEN

	SET webbrowser = 'Chrome 7' ;
	END if;
IF instr(
		user_agent,
		'Chrome/8.'
	)THEN

	SET webbrowser = 'Chrome 8' ;
	END if;
IF instr(
		user_agent,
		'Chrome/9.'
	)THEN

	SET webbrowser = 'Chrome 9' ;
	END if;
IF instr(
		user_agent,
		'Chrome/10.'
	)THEN

	SET webbrowser = 'Chrome 10' ;
	END if;
IF instr(
		user_agent,
		'Chrome/11.'
	)THEN

	SET webbrowser = 'Chrome 11' ;
	END if;
IF instr(
		user_agent,
		'Chrome/12.'
	)THEN

	SET webbrowser = 'Chrome 12' ;
	END if;
IF instr(
		user_agent,
		'Chrome/13.'
	)THEN

	SET webbrowser = 'Chrome 13' ;
	END if;
IF instr(
		user_agent,
		'Chrome/14.'
	)THEN

	SET webbrowser = 'Chrome 14' ;
	END if;
IF instr(
		user_agent,
		'Chrome/15.'
	)THEN

	SET webbrowser = 'Chrome 15' ;
	END if;
IF instr(
		user_agent,
		'Chrome/16.'
	)THEN

	SET webbrowser = 'Chrome 16' ;
	END if;
IF instr(
		user_agent,
		'Chrome/17.'
	)THEN

	SET webbrowser = 'Chrome 17' ;
	END if;
IF instr(
		user_agent,
		'Chrome/18.'
	)THEN

	SET webbrowser = 'Chrome 18' ;
	END if;
IF instr(
		user_agent,
		'Chrome/19.'
	)THEN

	SET webbrowser = 'Chrome 19' ;
	END if;
IF instr(
		user_agent,
		'Chrome/20.'
	)THEN

	SET webbrowser = 'Chrome 20' ;
	END if;
IF instr(
		user_agent,
		'Chrome/21.'
	)THEN

	SET webbrowser = 'Chrome 21' ;
	END if;
IF instr(
		user_agent,
		'Chrome/22.'
	)THEN

	SET webbrowser = 'Chrome 22' ;
	END if;
IF instr(
		user_agent,
		'Chrome/23.'
	)THEN

	SET webbrowser = 'Chrome 23' ;
	END if;
IF instr(
		user_agent,
		'Chrome/24.'
	)THEN

	SET webbrowser = 'Chrome 24' ;
	END if;
IF instr(
		user_agent,
		'Chrome/25.'
	)THEN

	SET webbrowser = 'Chrome 25' ;
	END if;


	IF(webbrowser IS NULL)THEN

	SET webbrowser = '** Not Known' ;
	END
	IF ; RETURN webbrowser ; END