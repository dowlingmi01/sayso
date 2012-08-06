DROP FUNCTION IF EXISTS operatingsystem;
CREATE FUNCTION `operatingsystem`(user_agent VARCHAR(255)) RETURNS varchar(255) CHARSET latin1
BEGIN
	-- Function logic here
	DECLARE
		webbrowser VARCHAR(255)DEFAULT NULL ;

/* Derive the operating system */
IF instr(
		user_agent,
		'Windows NT 4.0'
	)THEN

	SET webbrowser = 'Windows NT4' ;
	END if;

IF instr(
		user_agent,
		'Windows NT 5.0'
	)THEN

	SET webbrowser = 'Windows 2000' ;
	END if;


	IF instr(
		user_agent,
		'Windows NT 5.1'
	)THEN

	SET webbrowser = 'Windows XP' ;
	END if;
IF instr(
		user_agent,
		'Windows NT 5.2'
	)THEN

	SET webbrowser = 'Windows Server 2003' ;
	END
	IF ;

	IF instr(
		user_agent,
		'Windows NT 6.0'
	)THEN

	SET webbrowser = 'Windows Vista' ;
	END
	IF ;
	IF instr(
		user_agent,
		'Windows NT 6.1'
	)THEN

	SET webbrowser = 'Windows 7' ;
	END
	IF ;
	IF instr(
		user_agent,
		'Windows NT 6.2'
	)THEN

	SET webbrowser = 'Windows NT' ;
	END
	IF ;
IF instr(user_agent, 'BlackBerry')THEN

	SET webbrowser = 'BlackBerry' ;
	END
	IF ;
IF instr(user_agent, 'tablet')THEN

	SET webbrowser = 'Tablet' ;
	END
	IF ;
IF instr(user_agent, 'Windows Phone')THEN

	SET webbrowser = 'Windows Phone' ;
	END
	IF ;
	IF instr(user_agent, 'Mac')THEN

	SET webbrowser = 'Macintosh' ;
	END
	IF ;
	IF instr(user_agent, 'iPhone')THEN

	SET webbrowser = 'iPhone' ;
	END
	IF ;
IF instr(user_agent, 'iPad')THEN

	SET webbrowser = 'iPad' ;
	END
	IF ;
	IF instr(user_agent, 'Linux x86') or (instr(user_agent, 'Linux i686')) or (instr(user_agent, 'Intel X86')) THEN

	SET webbrowser = 'Linux' ;
	END
	IF ;
	IF instr(user_agent, 'Android') or (instr(user_agent, 'GINGERBREAD'))THEN

	SET webbrowser = 'Android' ;
	END
	IF ;
	IF instr(user_agent, 'Opera Mini')THEN

	SET webbrowser = 'Android' ;
	END
	IF ;
	IF instr(user_agent, 'Ubuntu')THEN

	SET webbrowser = 'Ubuntu' ;
	END
	IF ;
	IF instr(user_agent, 'Fedora')THEN

	SET webbrowser = 'Fedora' ;
	END
	IF ;
	IF(webbrowser IS NULL)THEN

	SET webbrowser = '** Not Known' ;
	END
	IF ; RETURN webbrowser ; END