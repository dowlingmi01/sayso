CREATE TABLE `ssmart_user_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


CREATE TABLE `ssmart_endpoint_class` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ssmart_user_type_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `ssmart_endpoint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ssmart_endpoint_class_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `se_ssmart_endpoint_class_id` FOREIGN KEY (`ssmart_endpoint_class_id`) REFERENCES `ssmart_endpoint_class` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `ssmart_log_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `ssmart_endpoint_id` int(11) DEFAULT NULL,
  `parameters` text COMMENT 'Can be either a json encoded Ssmart_EndpointRequest object or a json encoded Ssmart_Request object in the case of api level errors',
  `error_code` varchar(200) DEFAULT NULL,
  `error_message` varchar(200) DEFAULT NULL,
  `error_type` varchar(45) DEFAULT NULL,
  `error_response_name` varchar(45) DEFAULT NULL,
  `exception_trace` text,
  `exception_file` varchar(300) DEFAULT NULL,
  `exception_line` int(11) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `sle_session_id` FOREIGN KEY (`session_id`) REFERENCES `user_session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sle_ssmart_endpoint_id` FOREIGN KEY (`ssmart_endpoint_id`) REFERENCES `ssmart_endpoint` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sle_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



CREATE TABLE `ssmart_log_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `ssmart_endpoint_id` int(11) NOT NULL,
  `parameters` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `sl_endpoint_id` FOREIGN KEY (`ssmart_endpoint_id`) REFERENCES `ssmart_endpoint` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_session_id` FOREIGN KEY (`session_id`) REFERENCES `user_session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sl_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



