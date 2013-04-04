create or replace
VIEW `view_user_install` AS
SELECT
	`i`.`id` AS `id`,
	`i`.`location` AS `location`,
	`i`.`external_user_id` AS `external_user_id`,
	`e`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	`i`.`ip_address` AS `ip_address`,
	`i`.`user_agent` AS `user_agent`,
	`operatingsystem`(`i`.`user_agent`)AS `operatingsystem`,
	`browser`(`i`.`user_agent`)AS `browser`,
	`i`.`user_agent_supported` AS `user_agent_supported`,
	`i`.`origination` AS `origination`,
	`i`.`url` AS `url`,
	`i`.`referrer` AS `referrer`,
	`i`.`client_data` AS `client_data`,
	`i`.`visited_ts` AS `visited_ts`,
	`i`.`click_ts` AS `click_ts`,
	`i`.`first_access_ts` AS `first_access_ts`,
	`i`.`created` AS `created`,
	`i`.`modified` AS `modified`
FROM
	(
		(
			`user_install` `i`
			LEFT JOIN `starbar` `s` ON(
				(`s`.`id` = `i`.`starbar_id`)
			)
		)
		LEFT JOIN `user_email` `e` ON(
			(
				`i`.`user_id` = `e`.`user_id`
			)
		)
	)
WHERE
	(
		(
			`i`.`first_access_ts` IS NOT NULL
		)
		AND(
			NOT(
				(`e`.`email` LIKE '%@say.so')
			)
		)
	) ;

