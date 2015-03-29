CREATE TABLE `vendor_credentials_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) NOT NULL,
  `environment_id` int(11) NOT NULL DEFAULT '0',
  `credentials_text` text NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vendor_credentials_vendor_id` (`vendor_id`),
  CONSTRAINT `fk_vendor_credentials_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;