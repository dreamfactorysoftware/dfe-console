--
-- Table structure for table `service_user_t`
--

DROP TABLE IF EXISTS `service_user_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_user_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name_text` varchar(64) NOT NULL,
  `last_name_text` varchar(64) NOT NULL,
  `display_name_text` varchar(128) DEFAULT NULL,
  `email_addr_text` varchar(320) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_code` int(11) DEFAULT NULL,
  `password_text` varchar(256) NOT NULL,
  `last_login_date` int(11) DEFAULT NULL,
  `last_login_ip_text` varchar(64) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

