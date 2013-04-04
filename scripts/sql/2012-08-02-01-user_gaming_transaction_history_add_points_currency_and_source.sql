ALTER TABLE `user_gaming_transaction_history`
ADD COLUMN `points`  decimal(5,2) NOT NULL AFTER `action_on_id`,
ADD COLUMN `currency`  decimal(5,2) NOT NULL AFTER `points`,
ADD COLUMN `source`  varchar(255) NULL AFTER `currency`;
