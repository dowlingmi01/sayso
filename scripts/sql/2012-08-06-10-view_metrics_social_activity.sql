CREATE or REPLACE
VIEW `view_metrics_social_activity` AS
SELECT
	`m`.`id` AS `id`,
	`u`.`email` AS `email`,
	`s`.`label` AS `starbar`,
	`sa`.`label` AS `activity`,
	`m`.`url` AS `url`,
	`m`.`content` AS `content`,
	`m`.`created` AS `created`
FROM
	(
		(
			(
				`metrics_social_activity` `m`
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
		LEFT JOIN `lookup_social_activity_type` `sa` ON(
			(
				`sa`.`id` = `m`.`social_activity_type_id`
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

