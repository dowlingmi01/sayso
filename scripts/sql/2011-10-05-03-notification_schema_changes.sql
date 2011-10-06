ALTER TABLE  `notification_message` CHANGE  `popBoxToOpen`  `popbox_to_open` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  'E.g. ''polls'', ''surveys'', ''user-level'', etc.';
ALTER TABLE  `notification_message` ADD  `color` ENUM(  'Red',  'Green' ) NOT NULL DEFAULT  'Green' AFTER  `popbox_to_open`;
