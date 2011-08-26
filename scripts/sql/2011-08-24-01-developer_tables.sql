CREATE TABLE application (
	id int(11) NOT NULL AUTO_INCREMENT,
	developer_id int(11) DEFAULT NULL,
	model_id int(11) DEFAULT NULL,
	name varchar(255) DEFAULT NULL,
	app_key varchar(255) DEFAULT NULL,
	app_secret varchar(255) DEFAULT NULL,
	app_type varchar(100) DEFAULT NULL,
	game varchar(32) DEFAULT NULL,
	game_economy varchar(32) DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE developer (
	id int(11) NOT NULL AUTO_INCREMENT,
	company varchar(100) DEFAULT NULL,
	first_name varchar(100) DEFAULT NULL,
	last_name varchar(100) DEFAULT NULL,
	created date DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE model (
	id int(11) NOT NULL AUTO_INCREMENT,
	name varchar(100) DEFAULT NULL,
	db_name varchar(100) DEFAULT NULL,
	base_domain varchar(255) DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @appname = 'SaySo';
SET @appkey = md5(concat(to_days(now()), @appname));
SET @appsecret =  md5(concat(@appname, @appkey, to_days(now())));

INSERT developer VALUES (1, 'INTERNAL', 'David', 'James', now());

INSERT model VALUES (1, 'sayso', 'sayso', 'saysollc.com');

INSERT application values (null, 1, 1, 'SaySo App', @appkey, @appsecret, 'Main', null, null);
