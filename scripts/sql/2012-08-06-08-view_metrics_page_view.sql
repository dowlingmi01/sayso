CREATE or REPLACE
VIEW `view_metrics_page_view` AS
SELECT
	`m`.`id` AS `id`,
	`m`.`created` AS `created`,
	`u`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	`m`.`url` AS `url`,
	`domain`(`m`.`url`)AS `domain`
FROM
	(
		(
			`metrics_page_view` `m`
			LEFT JOIN `user_email` `u` ON(
				(
					`u`.`user_id` = `m`.`user_id`
				)
			)
		)
		LEFT JOIN `starbar` `s` ON(
			(`s`.`id` = `m`.`starbar_id`)
		)
	)
WHERE
	(
		(
			NOT(
				(`u`.`email` LIKE '%@say.so')
			)
		)
		AND(`m`.`starbar_id` > 1)
	) ;
