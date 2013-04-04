CREATE or REPLACE
VIEW `view_user_gaming_transaction_history` AS
SELECT
	`m`.`id` AS `id`,
	`m`.`status` AS `status`,
	`m`.`action` AS `action`,
	`m`.`created` AS `created`,
	`s`.`title` AS `title`,
	`s`.`type` AS `type`,
	`sb`.`label` AS `starbar`,
	`ue`.`email` AS `email`,
	`m`.`points` AS `points`,
	`m`.`currency` AS `currency`,
	`m`.`source` AS `source`
FROM
	(
		(
			(
				(
					`user_gaming_transaction_history` `m`
					LEFT JOIN `survey` `s` ON(
						(
							`s`.`id` = `m`.`action_on_id`
						)
					)
				)
				LEFT JOIN `user_gaming` `ug` ON(
					(
						`m`.`user_gaming_id` = `ug`.`id`
					)
				)
			)
			LEFT JOIN `user_email` `ue` ON(
				(
					`ug`.`user_id` = `ue`.`user_id`
				)
			)
		)
		LEFT JOIN `starbar` `sb` ON(
			(
				`sb`.`id` = `ug`.`starbar_id`
			)
		)
	)
WHERE
	(
		(
			NOT(
				(`ue`.`email` LIKE '%@say.so')
			)
		)
		AND(`ug`.`starbar_id` > 1)
	) ;

