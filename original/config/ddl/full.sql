--
-- Table structure for table `oauth_access_token_t`
--

DROP TABLE IF EXISTS `oauth_access_token_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_access_token_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_text` varchar(64) NOT NULL,
  `client_id_text` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_nbr` int(11) NOT NULL,
  `scope_text` varchar(256) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_token_text` (`token_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_access_token_t`
--

LOCK TABLES `oauth_access_token_t` WRITE;
/*!40000 ALTER TABLE `oauth_access_token_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_access_token_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_auth_code_t`
--

DROP TABLE IF EXISTS `oauth_auth_code_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_auth_code_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `code_text` varchar(64) NOT NULL,
  `client_id_text` varchar(64) NOT NULL,
  `redirect_uri_text` varchar(1024) NOT NULL,
  `expires_nbr` int(11) NOT NULL,
  `scope_text` varchar(256) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_code_text` (`code_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_auth_code_t`
--

LOCK TABLES `oauth_auth_code_t` WRITE;
/*!40000 ALTER TABLE `oauth_auth_code_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_auth_code_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_client_t`
--

DROP TABLE IF EXISTS `oauth_client_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_client_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `client_id_text` varchar(64) NOT NULL,
  `client_secret_text` varchar(64) NOT NULL,
  `redirect_uri_text` varchar(1024) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_client_id_text` (`client_id_text`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_client_t`
--

LOCK TABLES `oauth_client_t` WRITE;
/*!40000 ALTER TABLE `oauth_client_t` DISABLE KEYS */;
INSERT INTO `oauth_client_t` VALUES (1,1,'nO6XzW8VuyHEXm02SdtMOEeVaaKlSzLu','05a984a8a76d215c010e4f895a6f405cbfd4cee30a1119023f7268b3fd5836c4','http://df.local/oauth/authorize/','2012-10-16 19:44:12','2012-10-16 19:44:12');
/*!40000 ALTER TABLE `oauth_client_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_refresh_token_t`
--

DROP TABLE IF EXISTS `oauth_refresh_token_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oauth_refresh_token_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_text` varchar(64) NOT NULL,
  `client_id_text` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_nbr` int(11) NOT NULL,
  `scope_text` varchar(256) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_token_text` (`token_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_refresh_token_t`
--

LOCK TABLES `oauth_refresh_token_t` WRITE;
/*!40000 ALTER TABLE `oauth_refresh_token_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth_refresh_token_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_config_t`
--

DROP TABLE IF EXISTS `service_config_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_config_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `active_ind` tinyint(1) NOT NULL DEFAULT '0',
  `active_date` datetime DEFAULT NULL,
  `config_text` mediumtext,
  `expire_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_service_id` (`service_id`),
  CONSTRAINT `fk_service_config_service_id` FOREIGN KEY (`service_id`) REFERENCES `service_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_config_t`
--

LOCK TABLES `service_config_t` WRITE;
/*!40000 ALTER TABLE `service_config_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_config_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_t`
--

DROP TABLE IF EXISTS `service_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_type_nbr` int(11) NOT NULL DEFAULT '0',
  `service_name_text` varchar(128) NOT NULL,
  `service_tag_text` varchar(64) NOT NULL,
  `description_text` text,
  `controller_class_text` varchar(256) DEFAULT NULL,
  `default_variables_text` mediumtext,
  `owner_id` int(11) NOT NULL,
  `public_ind` tinyint(4) NOT NULL DEFAULT '0',
  `enable_ind` tinyint(4) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_service_service_tag_text` (`service_tag_text`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_t`
--

LOCK TABLES `service_t` WRITE;
/*!40000 ALTER TABLE `service_t` DISABLE KEYS */;
INSERT INTO `service_t` VALUES (1,0,'QR Code Generator','qrCode','QR code generator service','4',NULL,0,1,1,'2012-10-09 01:31:12','2012-10-11 12:47:03'),(2,0,'Microsoft Azure SQL database service','azureSql','Microsoft Azure SQL database service','4',NULL,0,1,1,'2012-10-09 18:58:52','2012-10-10 13:35:32'),(3,0,'Amazon DynamoDb','dynamo','<p>this is a new description it really is</p>\r\n','',NULL,0,1,1,'0000-00-00 00:00:00','2012-10-19 19:56:38'),(4,0,'Microsft Azure Blob Service','azureBlob','The <strong>azureBlob</strong> service stores text and binary data. This service offers the following three resources: the storage account, containers, and blobs. Within your storage account, containers provide a way to organize sets of blobs.\r\n\r\nYou can store text and binary data in blobs.','',NULL,0,0,1,'2012-10-11 20:51:46','2012-10-18 12:14:16'),(5,0,'MySQL Database Service','mySql',NULL,NULL,NULL,0,1,1,'2012-10-15 17:35:55','2012-10-15 17:35:55');
/*!40000 ALTER TABLE `service_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_token_t`
--

DROP TABLE IF EXISTS `service_token_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_token_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_text` varchar(64) DEFAULT NULL,
  `token_secret_text` varchar(64) DEFAULT NULL,
  `refresh_token_text` varchar(64) DEFAULT NULL,
  `expire_date` datetime DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_setting_user_id` (`user_id`),
  KEY `fk_service_setting_service_id` (`service_id`),
  CONSTRAINT `fk_service_setting_service_id` FOREIGN KEY (`service_id`) REFERENCES `service_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_service_setting_user_id` FOREIGN KEY (`user_id`) REFERENCES `service_user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_token_t`
--

LOCK TABLES `service_token_t` WRITE;
/*!40000 ALTER TABLE `service_token_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_token_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_user_map_t`
--

DROP TABLE IF EXISTS `service_user_map_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_user_map_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_config_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sum_user_id` (`user_id`),
  KEY `fk_sum_service_id` (`service_id`),
  CONSTRAINT `fk_sum_user_id` FOREIGN KEY (`user_id`) REFERENCES `service_user_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sum_service_id` FOREIGN KEY (`service_id`) REFERENCES `service_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_user_map_t`
--

LOCK TABLES `service_user_map_t` WRITE;
/*!40000 ALTER TABLE `service_user_map_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_user_map_t` ENABLE KEYS */;
UNLOCK TABLES;

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
  `password_text` varchar(200) NOT NULL COMMENT 'Big cuz it is a hash',
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `last_login_date` datetime DEFAULT NULL,
  `last_login_ip_text` varchar(64) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_user_owner_id` (`owner_id`),
  CONSTRAINT `fk_service_user_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `service_user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_user_t`
--

LOCK TABLES `service_user_t` WRITE;
/*!40000 ALTER TABLE `service_user_t` DISABLE KEYS */;
INSERT INTO `service_user_t` VALUES (1,'Jerry','Ablan',NULL,'jerryablan@dreamfactory.com','ad317edc35b2d5c5e8d5aea81087ffc0b09aaacdf99f27cae1e4006bef0b294ef8febf1467af59b2253eb8dc037b533d9b685bcfd958cec91b0936fd1169a488',NULL,NULL,'2012-10-16 18:53:14',NULL,'2012-10-16 18:53:14','2012-10-16 18:53:14');
/*!40000 ALTER TABLE `service_user_t` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_variable_t`
--

DROP TABLE IF EXISTS `service_variable_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_variable_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `name_text` varchar(128) NOT NULL,
  `value_text` mediumtext,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_service_id_name` (`service_id`,`name_text`),
  CONSTRAINT `fk_service_variable_service_id` FOREIGN KEY (`service_id`) REFERENCES `service_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_variable_t`
--

LOCK TABLES `service_variable_t` WRITE;
/*!40000 ALTER TABLE `service_variable_t` DISABLE KEYS */;
INSERT INTO `service_variable_t` VALUES (1,1,'test','test','2012-10-11 12:09:17','2012-10-11 12:09:17'),(10,3,'test1','tests','2012-10-11 12:34:45','2012-10-11 12:34:45'),(13,1,'asdfasdf','asdfadsf','2012-10-11 16:40:22','2012-10-11 16:40:22');
/*!40000 ALTER TABLE `service_variable_t` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
