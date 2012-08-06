CREATE or REPLACE
VIEW `view_metrics_search` AS
SELECT
	`m`.`id` AS `id`,
	`m`.`created` AS `created`,
	`u`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	`se`.`label` AS `label`,
	`m`.`query` AS `query`
FROM
	(
		(
			(
				`metrics_search` `m`
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
		LEFT JOIN `lookup_search_engines` `se` ON(
			(
				`se`.`id` = `m`.`search_engine_id`
			)
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

