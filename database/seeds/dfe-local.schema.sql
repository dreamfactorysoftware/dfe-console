/*
SQLyog Community v12.03 (64 bit)
MySQL - 5.5.41-0ubuntu0.14.04.1 : Database - dfe_local
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`dfe_local` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `dfe_local`;

/*Table structure for table `cluster_arch_t` */

DROP TABLE IF EXISTS `cluster_arch_t`;

CREATE TABLE `cluster_arch_t` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cluster_id_text` varchar(128) NOT NULL,
  `subdomain_text` varchar(128) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `cluster_server_asgn_arch_t` */

DROP TABLE IF EXISTS `cluster_server_asgn_arch_t`;

CREATE TABLE `cluster_server_asgn_arch_t` (
  `cluster_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cluster_id`,`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `cluster_server_asgn_t` */

DROP TABLE IF EXISTS `cluster_server_asgn_t`;

CREATE TABLE `cluster_server_asgn_t` (
  `cluster_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cluster_id`,`server_id`),
  KEY `fk_csa_server_id` (`server_id`),
  KEY `fk_csa_cluster_id` (`cluster_id`),
  CONSTRAINT `fk_csa_cluster_id` FOREIGN KEY (`cluster_id`) REFERENCES `cluster_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_csa_server_id` FOREIGN KEY (`server_id`) REFERENCES `server_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `cluster_t` */

DROP TABLE IF EXISTS `cluster_t`;

CREATE TABLE `cluster_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `cluster_id_text` varchar(128) NOT NULL,
  `subdomain_text` varchar(128) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_cluster_user_id_name` (`cluster_id_text`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `deactivation_arch_t` */

DROP TABLE IF EXISTS `deactivation_arch_t`;

CREATE TABLE `deactivation_arch_t` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `activate_by_date` datetime NOT NULL,
  `extend_count_nbr` int(1) NOT NULL DEFAULT '0',
  `user_notified_nbr` int(1) NOT NULL DEFAULT '0',
  `action_reason_nbr` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `deactivation_t` */

DROP TABLE IF EXISTS `deactivation_t`;

CREATE TABLE `deactivation_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `activate_by_date` datetime NOT NULL,
  `extend_count_nbr` int(1) NOT NULL DEFAULT '0',
  `user_notified_nbr` int(1) NOT NULL DEFAULT '0',
  `action_reason_nbr` int(11) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_user_instance` (`user_id`,`instance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12523 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `environment_t` */

DROP TABLE IF EXISTS `environment_t`;

CREATE TABLE `environment_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `environment_name_text` varchar(64) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `instance_arch_t` */

DROP TABLE IF EXISTS `instance_arch_t`;

CREATE TABLE `instance_arch_t` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_image_id` int(11) NOT NULL DEFAULT '0',
  `vendor_credentials_id` int(11) NOT NULL,
  `guest_location_nbr` int(11) NOT NULL DEFAULT '0',
  `instance_id_text` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `app_server_id` int(11) NOT NULL DEFAULT '6',
  `web_server_id` int(11) NOT NULL DEFAULT '5',
  `db_server_id` int(11) NOT NULL DEFAULT '4',
  `db_host_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'localhost',
  `db_port_nbr` int(11) NOT NULL DEFAULT '3306',
  `db_name_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dreamfactory',
  `db_user_text` varchar(64) CHARACTER SET utf8 NOT NULL,
  `db_password_text` varchar(64) CHARACTER SET utf8 NOT NULL,
  `cluster_id` int(11) NOT NULL DEFAULT '1',
  `storage_id_text` varchar(64) CHARACTER SET utf8 NOT NULL,
  `storage_version_nbr` int(11) NOT NULL DEFAULT '0',
  `flavor_nbr` int(11) NOT NULL DEFAULT '0',
  `base_image_text` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT 't1.micro',
  `instance_name_text` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `region_text` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `availability_zone_text` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `security_group_text` varchar(1024) CHARACTER SET latin1 DEFAULT NULL,
  `ssh_key_text` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `root_device_type_nbr` int(11) NOT NULL DEFAULT '0',
  `public_host_text` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `public_ip_text` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `private_host_text` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `private_ip_text` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `request_id_text` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `request_date` datetime DEFAULT NULL,
  `deprovision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `provision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `trial_instance_ind` tinyint(1) NOT NULL DEFAULT '1',
  `state_nbr` int(11) NOT NULL DEFAULT '0',
  `vendor_state_nbr` int(11) DEFAULT NULL,
  `vendor_state_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `ready_state_nbr` int(11) NOT NULL DEFAULT '0',
  `platform_state_nbr` int(11) NOT NULL DEFAULT '0',
  `environment_id` int(11) NOT NULL DEFAULT '1',
  `activate_ind` tinyint(1) NOT NULL DEFAULT '0',
  `last_state_date` datetime NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `terminate_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22651 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_janitor_t` */

DROP TABLE IF EXISTS `instance_janitor_t`;

CREATE TABLE `instance_janitor_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storage_id_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `private_ind` tinyint(1) NOT NULL DEFAULT '0',
  `registration_ind` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `user_storage_id_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20428 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_server_asgn_arch_t` */

DROP TABLE IF EXISTS `instance_server_asgn_arch_t`;

CREATE TABLE `instance_server_asgn_arch_t` (
  `instance_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`instance_id`,`server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_server_asgn_t` */

DROP TABLE IF EXISTS `instance_server_asgn_t`;

CREATE TABLE `instance_server_asgn_t` (
  `instance_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`instance_id`,`server_id`),
  KEY `fk_isa_server_id` (`server_id`),
  KEY `fk_isa_instance_id` (`instance_id`),
  CONSTRAINT `fk_isa_instance_id` FOREIGN KEY (`instance_id`) REFERENCES `instance_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_isa_server_id` FOREIGN KEY (`server_id`) REFERENCES `server_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_t` */

DROP TABLE IF EXISTS `instance_t`;

CREATE TABLE `instance_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_image_id` int(11) NOT NULL DEFAULT '0',
  `vendor_credentials_id` int(11) NOT NULL,
  `guest_location_nbr` int(11) NOT NULL DEFAULT '0',
  `instance_id_text` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `app_server_id` int(11) NOT NULL DEFAULT '6',
  `web_server_id` int(11) NOT NULL DEFAULT '5',
  `db_server_id` int(11) NOT NULL DEFAULT '4',
  `db_host_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'localhost',
  `db_port_nbr` int(11) NOT NULL DEFAULT '3306',
  `db_name_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dreamfactory',
  `db_user_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dsp_user',
  `db_password_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dsp_user',
  `cluster_id` int(11) NOT NULL DEFAULT '1',
  `storage_id_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `storage_version_nbr` int(11) NOT NULL DEFAULT '0',
  `flavor_nbr` int(11) NOT NULL DEFAULT '0',
  `base_image_text` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT 't1.micro',
  `instance_name_text` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `region_text` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `availability_zone_text` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `security_group_text` varchar(1024) CHARACTER SET latin1 DEFAULT NULL,
  `ssh_key_text` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `root_device_type_nbr` int(11) NOT NULL DEFAULT '0',
  `public_host_text` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `public_ip_text` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `private_host_text` varchar(256) CHARACTER SET latin1 DEFAULT NULL,
  `private_ip_text` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `request_id_text` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `request_date` datetime DEFAULT NULL,
  `deprovision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `provision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `trial_instance_ind` tinyint(1) NOT NULL DEFAULT '1',
  `state_nbr` int(11) NOT NULL DEFAULT '0',
  `vendor_state_nbr` int(11) DEFAULT NULL,
  `vendor_state_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `ready_state_nbr` int(11) NOT NULL DEFAULT '0',
  `platform_state_nbr` int(11) NOT NULL DEFAULT '0',
  `environment_id` int(11) NOT NULL DEFAULT '1',
  `activate_ind` tinyint(1) NOT NULL DEFAULT '0',
  `last_state_date` datetime NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `terminate_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_instance_name` (`instance_name_text`),
  KEY `fk_instance_vendor_id` (`vendor_id`),
  KEY `fk_instance_vendor_image_id` (`vendor_image_id`),
  KEY `fk_instance_vendor_credentials_id` (`vendor_credentials_id`),
  KEY `fk_instance_environment` (`environment_id`),
  KEY `fk_instance_user_id` (`user_id`),
  KEY `fk_instance_cluster_id` (`cluster_id`),
  KEY `fk_instance_app_server_id` (`app_server_id`),
  KEY `fk_instance_db_server_id` (`db_server_id`),
  KEY `fk_instance_web_server` (`web_server_id`),
  KEY `ix_state_date` (`last_state_date`),
  CONSTRAINT `fk_instance_app_server_id` FOREIGN KEY (`app_server_id`) REFERENCES `server_t` (`id`),
  CONSTRAINT `fk_instance_cluster_id` FOREIGN KEY (`cluster_id`) REFERENCES `cluster_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_db_server_id` FOREIGN KEY (`db_server_id`) REFERENCES `server_t` (`id`),
  CONSTRAINT `fk_instance_environment` FOREIGN KEY (`environment_id`) REFERENCES `environment_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_vendor_credentials_id` FOREIGN KEY (`vendor_credentials_id`) REFERENCES `vendor_credentials_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_vendor_image_id` FOREIGN KEY (`vendor_image_id`) REFERENCES `vendor_image_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_instance_web_server` FOREIGN KEY (`web_server_id`) REFERENCES `server_t` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24617 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `migration_t` */

DROP TABLE IF EXISTS `migration_t`;

CREATE TABLE `migration_t` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `owner_hash_t` */

DROP TABLE IF EXISTS `owner_hash_t`;

CREATE TABLE `owner_hash_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_type_nbr` int(11) NOT NULL,
  `hash_text` varchar(128) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Table structure for table `role_t` */

DROP TABLE IF EXISTS `role_t`;

CREATE TABLE `role_t` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description_text` text COLLATE utf8_unicode_ci NOT NULL,
  `active_ind` tinyint(4) NOT NULL,
  `home_view_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lmod_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_t_role_name_text_unique` (`role_name_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `route_hash_t` */

DROP TABLE IF EXISTS `route_hash_t`;

CREATE TABLE `route_hash_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_nbr` int(11) NOT NULL DEFAULT '0',
  `hash_text` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `actual_path_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `expire_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_route_hash_hash` (`hash_text`)
) ENGINE=MyISAM AUTO_INCREMENT=4673 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `server_arch_t` */

DROP TABLE IF EXISTS `server_arch_t`;

CREATE TABLE `server_arch_t` (
  `id` int(11) NOT NULL,
  `server_type_id` int(11) NOT NULL,
  `server_id_text` varchar(128) NOT NULL,
  `host_text` varchar(1024) NOT NULL,
  `config_text` mediumtext,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `server_t` */

DROP TABLE IF EXISTS `server_t`;

CREATE TABLE `server_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_type_id` int(11) NOT NULL,
  `server_id_text` varchar(128) NOT NULL,
  `host_text` varchar(1024) NOT NULL,
  `config_text` mediumtext,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_server_type_id` (`server_type_id`),
  KEY `ux_server_id_name` (`server_type_id`,`server_id_text`),
  CONSTRAINT `fk_server_server_type_id` FOREIGN KEY (`server_type_id`) REFERENCES `server_type_t` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `server_type_t` */

DROP TABLE IF EXISTS `server_type_t`;

CREATE TABLE `server_type_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name_text` varchar(32) NOT NULL,
  `schema_text` mediumblob NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_server_type_name_text` (`type_name_text`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `service_user_t` */

DROP TABLE IF EXISTS `service_user_t`;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `user_role_asgn_t` */

DROP TABLE IF EXISTS `user_role_asgn_t`;

CREATE TABLE `user_role_asgn_t` (
  `user_id` int(10) NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_role_role_id` (`role_id`),
  CONSTRAINT `fk_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `role_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_role_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_t` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `user_t` */

DROP TABLE IF EXISTS `user_t`;

CREATE TABLE `user_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drupal_id` int(11) DEFAULT NULL,
  `api_token_text` varchar(128) DEFAULT NULL,
  `first_name_text` varchar(64) DEFAULT NULL,
  `last_name_text` varchar(64) DEFAULT NULL,
  `display_name_text` varchar(128) DEFAULT NULL,
  `email_addr_text` varchar(200) NOT NULL,
  `password_text` varchar(200) NOT NULL COMMENT 'Big cuz it is a hash',
  `drupal_password_text` varchar(200) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `company_name_text` varchar(128) DEFAULT NULL,
  `title_text` varchar(128) DEFAULT NULL,
  `city_text` varchar(64) DEFAULT NULL,
  `state_province_text` varchar(64) DEFAULT NULL,
  `country_text` varchar(2) DEFAULT NULL,
  `postal_code_text` varchar(32) DEFAULT NULL,
  `phone_text` varchar(32) DEFAULT NULL,
  `fax_text` varchar(32) DEFAULT NULL,
  `opt_in_ind` tinyint(1) NOT NULL DEFAULT '1',
  `agree_ind` tinyint(1) NOT NULL DEFAULT '0',
  `valid_email_hash_text` varchar(128) DEFAULT NULL,
  `valid_email_hash_expire_time` int(11) DEFAULT NULL,
  `valid_email_date` datetime DEFAULT NULL,
  `recover_hash_text` varchar(128) DEFAULT NULL,
  `recover_hash_expire_time` int(11) DEFAULT NULL,
  `last_login_date` datetime DEFAULT NULL,
  `last_login_ip_text` varchar(64) DEFAULT NULL,
  `admin_ind` tinyint(1) NOT NULL DEFAULT '0',
  `storage_id_text` varchar(64) NOT NULL,
  `activate_ind` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_user_email_addr_text` (`email_addr_text`),
  UNIQUE KEY `ixu_user_display_name_text` (`display_name_text`),
  KEY `fk_user_owner_id` (`owner_id`),
  CONSTRAINT `fk_user_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29916 DEFAULT CHARSET=utf8;

/*Table structure for table `vendor_credentials_t` */

DROP TABLE IF EXISTS `vendor_credentials_t`;

CREATE TABLE `vendor_credentials_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) NOT NULL,
  `environment_id` int(11) NOT NULL DEFAULT '0',
  `keys_text` mediumtext,
  `label_text` varchar(60) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_vendor_credentials_vendor_id` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `vendor_image_t` */

DROP TABLE IF EXISTS `vendor_image_t`;

CREATE TABLE `vendor_image_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `os_text` varchar(64) NOT NULL DEFAULT 'Linux',
  `license_text` varchar(64) DEFAULT 'Public',
  `image_id_text` varchar(64) NOT NULL,
  `image_name_text` varchar(256) DEFAULT NULL,
  `image_description_text` text,
  `architecture_nbr` int(11) NOT NULL DEFAULT '0',
  `region_text` varchar(64) DEFAULT NULL,
  `availability_zone_text` varchar(64) DEFAULT NULL,
  `root_storage_text` varchar(32) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ixu_vendor_image_id_name` (`vendor_id`,`image_id_text`),
  CONSTRAINT `fk_vendor_image_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendor_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13288 DEFAULT CHARSET=utf8;

/*Table structure for table `vendor_t` */

DROP TABLE IF EXISTS `vendor_t`;

CREATE TABLE `vendor_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name_text` varchar(48) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
