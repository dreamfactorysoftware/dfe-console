CREATE TABLE `vendor_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name_text` varchar(48) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
