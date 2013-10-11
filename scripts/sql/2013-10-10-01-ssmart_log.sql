CREATE TABLE `ssmart_user_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




CREATE TABLE `ssmart_endpoint_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ssmart_user_type_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `ssmart_endpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ssmart_endpoint_class_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `text` text,
  `example_request` text,
  `example_response` text,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `se_ssmart_endpoint_class_id` FOREIGN KEY (`ssmart_endpoint_class_id`) REFERENCES `ssmart_endpoint_classes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;





CREATE TABLE `ssmart_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `ssmart_user_type_id` int(11) NOT NULL,
  `ssmart_endpoint_class_id` int(11) NOT NULL,
  `ssmart_endpoint_id` int(11) NOT NULL,
  `parameters` text,
  `status` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `sl_session_id` FOREIGN KEY (`session_id`) REFERENCES `user_session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_endpoint_id` FOREIGN KEY (`ssmart_endpoint_id`) REFERENCES `ssmart_endpoints` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_ssmart_endpoint_endpoint_class_id` FOREIGN KEY (`ssmart_endpoint_class_id`) REFERENCES `ssmart_endpoint_classes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_ssmart_endpoint_user_type_id` FOREIGN KEY (`ssmart_user_type_id`) REFERENCES `ssmart_user_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


