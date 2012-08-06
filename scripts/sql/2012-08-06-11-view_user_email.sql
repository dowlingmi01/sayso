CREATE or REPLACE
VIEW `view_user_email` AS
SELECT
	`user_email`.`id` AS `id`,
	`user_email`.`user_id` AS `user_id`,
	`user_email`.`email` AS `email`,
	`user_email`.`created` AS `created`,
	`user_email`.`modified` AS `modified`,
	`user`.`username` AS `username`,
	`user`.`first_name` AS `first_name`,
	`user`.`last_name` AS `last_name`,
	`starbar`.`label` AS `label`
FROM
	(
		(
			`user_email`
			LEFT JOIN `user` ON(
				(
					`user`.`id` = `user_email`.`user_id`
				)
			)
		)
		LEFT JOIN `starbar` ON(
			(
				`starbar`.`id` = `user`.`originating_starbar_id`
			)
		)
	)
ORDER BY
	`user`.`id` DESC ;

