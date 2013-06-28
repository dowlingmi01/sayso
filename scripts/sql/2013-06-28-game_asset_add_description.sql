ALTER TABLE `sayso`.`game_asset` ADD COLUMN `description` VARCHAR(255) NULL  AFTER `name` ;

UPDATE `sayso`.`game_asset` SET `description`='XP - determine your access to increasingly important levels of influence.' WHERE `id`='20';
UPDATE `sayso`.`game_asset` SET `description`='Coins - are almost as good as cash and can be redeemed for goods in the Rewards Center.' WHERE `id`='21';

CREATE OR REPLACE VIEW `game_currency_view` AS
    select 
        `a`.`id` AS `id`,
        `a`.`economy_id` AS `economy_id`,
        `a`.`name` AS `name`,
        `a`.`description` AS `description`,
        `a`.`bdid` AS `bdid`,
        `c`.`game_currency_type_id` AS `game_currency_type_id`,
        `t`.`short_name` AS `short_name`
    from
        ((`game_asset` `a`
        join `game_currency` `c`)
        join `game_currency_type` `t`)
    where
        ((`a`.`id` = `c`.`game_asset_id`)
            and (`t`.`id` = `c`.`game_currency_type_id`)
            and (`a`.`type` = 'currency'));