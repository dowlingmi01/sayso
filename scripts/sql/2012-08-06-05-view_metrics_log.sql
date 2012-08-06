CREATE or REPLACE
VIEW `view_active_users` AS
SELECT
	`e`.`user_id` AS `user_id`,
	`e`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	count(0)AS `page_views`,
	min(`m`.`created`)AS `first_page_view`,
	max(`m`.`created`)AS `last_page_view`
FROM
	(
		`user_email` `e`
		JOIN(
			`metrics_log` `m`
			LEFT JOIN `starbar` `s` ON(
				(`m`.`starbar_id` = `s`.`id`)
			)
		)
	)
WHERE
	(
		(
			`e`.`user_id` = `m`.`user_id`
		)
		AND(`e`.`user_id` > 3767)
		AND(
			NOT(
				(`e`.`email` LIKE '%@say.so')
			)
		)
	)
GROUP BY
	`e`.`user_id`,
	`e`.`email` ;


