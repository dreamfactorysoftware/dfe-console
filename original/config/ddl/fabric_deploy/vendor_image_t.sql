CREATE TABLE `vendor_image_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `os_text` varchar(64) NOT NULL DEFAULT 'Ubuntu',
  `license_text` varchar(64) DEFAULT 'Public',
  `image_name_text` varchar(64) NOT NULL,
  `image_type_text` varchar(128) NOT NULL,
  `architecture_nbr` int(11) NOT NULL DEFAULT '0',
  `region_text` varchar(64) DEFAULT NULL,
  `availability_zone_text` varchar(64) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vendor_image_vendor_id` (`vendor_id`),
  CONSTRAINT `fk_vendor_image_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;