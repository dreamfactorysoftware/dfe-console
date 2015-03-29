--
-- Table structure for table `service_t`
--

DROP TABLE IF EXISTS `service_t`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_type_nbr` int(11) NOT NULL DEFAULT '0',
  `service_name_text` varchar(128) CHARACTER SET latin1 NOT NULL,
  `service_tag_text` varchar(64) CHARACTER SET latin1 NOT NULL,
  `description_text` text CHARACTER SET latin1,
  `controller_class_text` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `default_variables_text` mediumtext CHARACTER SET latin1 COMMENT 'Serialized array of variables used by this service',
  `owner_id` int(11) DEFAULT NULL,
  `public_ind` tinyint(1) NOT NULL DEFAULT '0',
  `enable_ind` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`lmod_date`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `service_t` WRITE;
/*!40000 ALTER TABLE `service_t` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_t` ENABLE KEYS */;
UNLOCK TABLES;
