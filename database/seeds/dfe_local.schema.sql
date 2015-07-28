/*
SQLyog Community v12.03 (64 bit)
MySQL - 5.6.24-0ubuntu2 : Database - dfe_local
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`dfe_local` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `dfe_local`;

/*Table structure for table `app_key_t` */

DROP TABLE IF EXISTS `app_key_t`;

CREATE TABLE `app_key_t` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) NOT NULL,
  `owner_type_nbr` int(11) NOT NULL,
  `client_id` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `client_secret` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `server_secret` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `key_class_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_key_t_client_id_unique` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `auth_reset_t` */

DROP TABLE IF EXISTS `auth_reset_t`;

CREATE TABLE `auth_reset_t` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `auth_reset_t_email_addr_text_index` (`email`),
  KEY `auth_reset_t_token_text_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `cluster_id_text` varchar(128) NOT NULL,
  `subdomain_text` varchar(128) NOT NULL,
  `max_instances_nbr` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_cluster_cluster_id_text` (`cluster_id_text`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_guest_arch_t` */

DROP TABLE IF EXISTS `instance_guest_arch_t`;

CREATE TABLE `instance_guest_arch_t` (
  `id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_image_id` int(11) NOT NULL,
  `vendor_credentials_id` int(11) DEFAULT NULL,
  `flavor_nbr` int(11) NOT NULL,
  `base_image_text` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `region_text` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `availability_zone_text` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `security_group_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `ssh_key_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `root_device_type_nbr` int(11) NOT NULL,
  `public_host_text` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `public_ip_text` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `private_host_text` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `private_ip_text` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `state_nbr` int(11) NOT NULL,
  `state_text` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `instance_guest_t` */

DROP TABLE IF EXISTS `instance_guest_t`;

CREATE TABLE `instance_guest_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `vendor_image_id` int(11) NOT NULL DEFAULT '0',
  `vendor_credentials_id` int(11) DEFAULT NULL,
  `flavor_nbr` int(11) NOT NULL DEFAULT '0',
  `base_image_text` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT 't1.micro',
  `region_text` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `availability_zone_text` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `security_group_text` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
  `ssh_key_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `root_device_type_nbr` int(11) NOT NULL DEFAULT '0',
  `public_host_text` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `public_ip_text` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `private_host_text` varchar(256) CHARACTER SET utf8 DEFAULT NULL,
  `private_ip_text` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `state_nbr` int(11) NOT NULL DEFAULT '0',
  `state_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_instance_guest_instance_id` (`id`),
  KEY `fk_instance_guest_instance_id` (`instance_id`),
  CONSTRAINT `fk_instance_guest_instance_id` FOREIGN KEY (`instance_id`) REFERENCES `instance_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `guest_location_nbr` int(11) NOT NULL DEFAULT '0',
  `environment_id` int(11) NOT NULL DEFAULT '1',
  `instance_id_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `instance_name_text` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `instance_data_text` mediumtext COLLATE utf8_unicode_ci,
  `cluster_id` int(11) NOT NULL DEFAULT '1',
  `app_server_id` int(11) NOT NULL DEFAULT '6',
  `db_server_id` int(11) NOT NULL DEFAULT '4',
  `web_server_id` int(11) NOT NULL DEFAULT '5',
  `storage_id_text` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `db_host_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'localhost',
  `db_port_nbr` int(11) NOT NULL DEFAULT '3306',
  `db_name_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dreamfactory',
  `db_user_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dsp_user',
  `db_password_text` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT 'dsp_user',
  `request_id_text` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `request_date` datetime DEFAULT NULL,
  `activate_ind` tinyint(1) NOT NULL DEFAULT '0',
  `trial_instance_ind` tinyint(1) NOT NULL DEFAULT '1',
  `provision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `deprovision_ind` tinyint(1) NOT NULL DEFAULT '0',
  `state_nbr` int(11) NOT NULL DEFAULT '0',
  `ready_state_nbr` int(11) NOT NULL DEFAULT '0',
  `platform_state_nbr` int(11) NOT NULL DEFAULT '0',
  `storage_version_nbr` int(11) NOT NULL DEFAULT '0',
  `last_state_date` datetime NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `terminate_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_instance_name` (`instance_name_text`),
  KEY `fk_instance_environment` (`environment_id`),
  KEY `fk_instance_user_id` (`user_id`),
  KEY `fk_instance_cluster_id` (`cluster_id`),
  KEY `fk_instance_app_server_id` (`app_server_id`),
  KEY `fk_instance_db_server_id` (`db_server_id`),
  KEY `fk_instance_web_server` (`web_server_id`),
  KEY `ix_state_date` (`last_state_date`),
  CONSTRAINT `fk_instance_app_server_id` FOREIGN KEY (`app_server_id`) REFERENCES `server_t` (`id`),
  CONSTRAINT `fk_instance_cluster_id` FOREIGN KEY (`cluster_id`) REFERENCES `cluster_t` (`id`),
  CONSTRAINT `fk_instance_db_server_id` FOREIGN KEY (`db_server_id`) REFERENCES `server_t` (`id`),
  CONSTRAINT `fk_instance_environment` FOREIGN KEY (`environment_id`) REFERENCES `environment_t` (`id`),
  CONSTRAINT `fk_instance_user_id` FOREIGN KEY (`user_id`) REFERENCES `user_t` (`id`),
  CONSTRAINT `fk_instance_web_server` FOREIGN KEY (`web_server_id`) REFERENCES `server_t` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `job_fail_t` */

DROP TABLE IF EXISTS `job_fail_t`;

CREATE TABLE `job_fail_t` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `job_t` */

DROP TABLE IF EXISTS `job_t`;

CREATE TABLE `job_t` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `limit_t` */

DROP TABLE IF EXISTS `limit_t`;

CREATE TABLE `limit_t` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cluster_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `limit_key_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `limit_nbr` int(11) DEFAULT NULL,
  `period_nbr` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_limit_cluster_instance_key` (`cluster_id`,`instance_id`,`limit_key_text`),
  KEY `limit_t_cluster_id_index` (`cluster_id`),
  KEY `limit_t_instance_id_index` (`instance_id`),
  CONSTRAINT `fk_limit_cluster_id` FOREIGN KEY (`cluster_id`) REFERENCES `cluster_t` (`id`),
  CONSTRAINT `fk_limit_instance_id` FOREIGN KEY (`instance_id`) REFERENCES `instance_t` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `migration_t` */

DROP TABLE IF EXISTS `migration_t`;

CREATE TABLE `migration_t` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `mount_t` */

DROP TABLE IF EXISTS `mount_t`;

CREATE TABLE `mount_t` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mount_type_nbr` int(11) NOT NULL DEFAULT '0',
  `mount_id_text` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `root_path_text` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `config_text` mediumtext COLLATE utf8_unicode_ci,
  `last_mount_date` datetime DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mount_t_mount_id_text_unique` (`mount_id_text`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `server_arch_t` */

DROP TABLE IF EXISTS `server_arch_t`;

CREATE TABLE `server_arch_t` (
  `id` int(11) NOT NULL,
  `server_type_id` int(11) NOT NULL,
  `server_id_text` varchar(128) NOT NULL,
  `host_text` varchar(1024) NOT NULL,
  `mount_id` int(11) DEFAULT NULL,
  `config_text` mediumtext,
  `create_date` datetime NOT NULL,
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
  `mount_id` int(11) DEFAULT NULL,
  `config_text` mediumtext,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_server_server_type_id` (`server_type_id`),
  KEY `ux_server_id_name` (`server_type_id`,`server_id_text`),
  CONSTRAINT `fk_server_server_type_id` FOREIGN KEY (`server_type_id`) REFERENCES `server_type_t` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Data for the table `server_type_t` */

insert  into `server_type_t`(`id`,`type_name_text`,`schema_text`,`create_date`,`lmod_date`) values (1,'db','',now(),now()),(2,'web','',now(),now()),(3,'app','',now(),now());

/*Table structure for table `service_user_t` */

DROP TABLE IF EXISTS `service_user_t`;

CREATE TABLE `service_user_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name_text` varchar(64) NOT NULL,
  `last_name_text` varchar(64) NOT NULL,
  `nickname_text` varchar(128) DEFAULT NULL,
  `email_addr_text` varchar(320) NOT NULL,
  `password_text` varchar(200) NOT NULL COMMENT 'Big cuz it is a hash',
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `last_login_date` datetime DEFAULT NULL,
  `last_login_ip_text` varchar(64) DEFAULT NULL,
  `remember_token` varchar(128) DEFAULT NULL,
  `active_ind` tinyint(1) NOT NULL DEFAULT '0',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_service_user_owner_id` (`owner_id`),
  CONSTRAINT `fk_service_user_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `service_user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Table structure for table `snapshot_t` */

DROP TABLE IF EXISTS `snapshot_t`;

CREATE TABLE `snapshot_t` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  `route_hash_id` int(11) NOT NULL,
  `snapshot_id_text` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `public_ind` tinyint(1) NOT NULL DEFAULT '1',
  `public_url_text` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `expire_date` datetime NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `snapshot_t_user_id_snapshot_id_text_unique` (`user_id`,`snapshot_id_text`),
  KEY `snapshot_t_instance_id_foreign` (`instance_id`),
  CONSTRAINT `snapshot_t_instance_id_foreign` FOREIGN KEY (`instance_id`) REFERENCES `instance_t` (`id`) ON DELETE CASCADE,
  CONSTRAINT `snapshot_t_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user_t` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `user_t` */

DROP TABLE IF EXISTS `user_t`;

CREATE TABLE `user_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_addr_text` varchar(200) NOT NULL,
  `password_text` varchar(200) NOT NULL COMMENT 'Big cuz it is a hash',
  `remember_token` varchar(128) DEFAULT NULL,
  `first_name_text` varchar(64) DEFAULT NULL,
  `last_name_text` varchar(64) DEFAULT NULL,
  `nickname_text` varchar(128) DEFAULT NULL,
  `api_token_text` varchar(128) DEFAULT NULL,
  `storage_id_text` varchar(64) NOT NULL,
  `external_id_text` varchar(128) DEFAULT NULL,
  `external_password_text` varchar(200) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `owner_type_nbr` int(11) DEFAULT NULL,
  `company_name_text` varchar(128) DEFAULT NULL,
  `title_text` varchar(128) DEFAULT NULL,
  `city_text` varchar(64) DEFAULT NULL,
  `state_province_text` varchar(64) DEFAULT NULL,
  `country_text` varchar(2) DEFAULT NULL,
  `postal_code_text` varchar(32) DEFAULT NULL,
  `phone_text` varchar(32) DEFAULT NULL,
  `opt_in_ind` tinyint(1) NOT NULL DEFAULT '1',
  `agree_ind` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_date` datetime DEFAULT NULL,
  `last_login_ip_text` varchar(64) DEFAULT NULL,
  `admin_ind` tinyint(1) NOT NULL DEFAULT '0',
  `activate_ind` tinyint(1) NOT NULL DEFAULT '0',
  `active_ind` tinyint(1) NOT NULL DEFAULT '1',
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ixu_user_email_addr_text` (`email_addr_text`),
  KEY `fk_user_owner_id` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Data for the table `vendor_image_t` */

insert  into `vendor_image_t`(`id`,`vendor_id`,`os_text`,`license_text`,`image_id_text`,`image_name_text`,`image_description_text`,`architecture_nbr`,`region_text`,`availability_zone_text`,`root_storage_text`,`create_date`,`lmod_date`) values (34,1,'Linux','Public','ami-013f9768','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120728',NULL,1,NULL,NULL,'ebs','2012-11-26 17:28:04','2013-01-18 14:19:18'),(169,1,'Linux','Public','ami-057bcf6c','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120822',NULL,0,NULL,NULL,'ebs','2012-11-26 17:28:16','2013-01-18 14:19:21'),(430,1,'Linux','Public','ami-0d3f9764','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120728',NULL,0,NULL,NULL,'ebs','2012-11-26 17:28:37','2013-01-18 14:19:27'),(624,1,'Linux','Public','ami-137bcf7a','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120822',NULL,1,NULL,NULL,'ebs','2012-11-26 17:28:54','2013-01-18 14:19:33'),(1865,1,'Linux','Public','ami-3b4ff252','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121001',NULL,0,NULL,NULL,'ebs','2012-11-26 17:30:44','2013-01-18 14:20:06'),(1920,1,'Linux','Public','ami-3d4ff254','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121001',NULL,1,NULL,NULL,'ebs','2012-11-26 17:30:49','2013-01-18 14:20:07'),(3981,1,'Linux','Public','ami-82fa58eb','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120616',NULL,1,NULL,NULL,'ebs','2012-11-26 17:34:14','2013-01-18 14:21:05'),(4275,1,'Linux','Public','ami-8cfa58e5','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120616',NULL,0,NULL,NULL,'ebs','2012-11-26 17:34:42','2013-01-18 14:21:14'),(4647,1,'Linux','Public','ami-9878c0f1','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121026.1',NULL,0,NULL,NULL,'ebs','2012-11-26 17:35:14','2013-01-18 14:21:23'),(4764,1,'Linux','Public','ami-9c78c0f5','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121026.1',NULL,1,NULL,NULL,'ebs','2012-11-26 17:35:24','2013-01-18 14:21:26'),(4946,1,'Linux','Public','ami-a29943cb','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20120424',NULL,1,NULL,NULL,'ebs','2012-11-26 17:35:40','2013-01-18 14:21:31'),(5246,1,'Linux','Public','ami-ac9943c5','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20120424',NULL,0,NULL,NULL,'ebs','2012-11-26 17:36:06','2013-01-18 14:21:39'),(5635,1,'Linux','Public','ami-0baf7662','ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120403',NULL,1,NULL,NULL,'ebs','2013-01-18 14:19:26','2013-01-18 14:19:26'),(5965,1,'Linux','Public','ami-1616ca7f','ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120312',NULL,0,NULL,NULL,'ebs','2013-01-18 14:19:34','2013-01-18 14:19:34'),(6944,1,'Linux','Public','ami-349b495d','ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120221',NULL,1,NULL,NULL,'ebs','2013-01-18 14:19:59','2013-01-18 14:19:59'),(7050,1,'Linux','Public','ami-37af765e','ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120403',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:03','2013-01-18 14:20:03'),(7274,1,'Linux','Public','ami-3e9b4957','ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120221',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:08','2013-01-18 14:20:08'),(7665,1,'Linux','Public','ami-4bad7422','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120401',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:19','2013-01-18 14:20:19'),(7728,1,'Linux','Public','ami-4dad7424','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120401',NULL,1,NULL,NULL,'ebs','2013-01-18 14:20:21','2013-01-18 14:20:21'),(7984,1,'Linux','Public','ami-55dc0b3c','ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120110',NULL,1,NULL,NULL,'ebs','2013-01-18 14:20:27','2013-01-18 14:20:27'),(8592,1,'Linux','Public','ami-699f3600','ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120723',NULL,1,NULL,NULL,'ebs','2013-01-18 14:20:44','2013-01-18 14:20:44'),(8657,1,'Linux','Public','ami-6ba27502','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120108',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:45','2013-01-18 14:20:45'),(8808,1,'Linux','Public','ami-6fa27506','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120108',NULL,1,NULL,NULL,'ebs','2013-01-18 14:20:49','2013-01-18 14:20:49'),(8879,1,'Linux','Public','ami-71dc0b18','ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120110',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:51','2013-01-18 14:20:51'),(8923,1,'Linux','Public','ami-7339b41a','ubuntu/images/ebs/ubuntu-quantal-12.10-i386-server-20121218',NULL,0,NULL,NULL,'ebs','2013-01-18 14:20:52','2013-01-18 14:20:52'),(8998,1,'Linux','Public','ami-7539b41c','ubuntu/images/ebs/ubuntu-quantal-12.10-amd64-server-20121218',NULL,1,NULL,NULL,'ebs','2013-01-18 14:20:54','2013-01-18 14:20:54'),(9362,1,'Linux','Public','ami-81c31ae8','ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120402',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:04','2013-01-18 14:21:04'),(9364,1,'Linux','Public','ami-81cf46e8','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20130103',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:04','2013-01-18 14:21:04'),(9441,1,'Linux','Public','ami-83cf46ea','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20130103',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:06','2013-01-18 14:21:06'),(9559,1,'Linux','Public','ami-87c31aee','ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120402',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:09','2013-01-18 14:21:09'),(9909,1,'Linux','Public','ami-9265dbfb','ubuntu/images/ebs/ubuntu-quantal-12.10-i386-server-20121017',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:18','2013-01-18 14:21:18'),(9966,1,'Linux','Public','ami-9465dbfd','ubuntu/images/ebs/ubuntu-quantal-12.10-amd64-server-20121017',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:20','2013-01-18 14:21:20'),(10309,1,'Linux','Public','ami-9f9c35f6','ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20120723',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:29','2013-01-18 14:21:29'),(10344,1,'Linux','Public','ami-a0ba68c9','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120222',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:29','2013-01-18 14:21:29'),(10487,1,'Linux','Public','ami-a562a9cc','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20111205',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:33','2013-01-18 14:21:33'),(10520,1,'Linux','Public','ami-a6b10acf','ubuntu/images/ebs/ubuntu-natty-11.04-i386-server-20121028',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:34','2013-01-18 14:21:34'),(11152,1,'Linux','Public','ami-bab10ad3','ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20121028',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:50','2013-01-18 14:21:50'),(11153,1,'Linux','Public','ami-baba68d3','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120222',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:50','2013-01-18 14:21:50'),(11297,1,'Linux','Public','ami-bf62a9d6','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20111205',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:54','2013-01-18 14:21:54'),(11313,1,'Linux','Public','ami-c012cea9','ubuntu/images/ebs/ubuntu-maverick-10.10-i386-server-20120310',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:54','2013-01-18 14:21:54'),(11368,1,'Linux','Public','ami-c19e37a8','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120722',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:55','2013-01-18 14:21:55'),(11445,1,'Linux','Public','ami-c412cead','ubuntu/images/ebs/ubuntu-maverick-10.10-amd64-server-20120310',NULL,1,NULL,NULL,'ebs','2013-01-18 14:21:57','2013-01-18 14:21:57'),(11502,1,'Linux','Public','ami-c5b202ac','ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120913',NULL,0,NULL,NULL,'ebs','2013-01-18 14:21:59','2013-01-18 14:21:59'),(11566,1,'Linux','Public','ami-c7b202ae','ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120913',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:00','2013-01-18 14:22:00'),(11702,1,'Linux','Public','ami-cbc072a2','ubuntu/images/ebs/ubuntu-oneiric-11.10-i386-server-20120918',NULL,0,NULL,NULL,'ebs','2013-01-18 14:22:04','2013-01-18 14:22:04'),(11771,1,'Linux','Public','ami-cdc072a4','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120918',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:06','2013-01-18 14:22:06'),(11948,1,'Linux','Public','ami-d38f57ba','ubuntu/images/ebs/ubuntu-maverick-10.10-i386-server-20120410',NULL,0,NULL,NULL,'ebs','2013-01-18 14:22:11','2013-01-18 14:22:11'),(12030,1,'Linux','Public','ami-d5e54dbc','ubuntu/images/ebs/ubuntu-lucid-10.04-amd64-server-20120724',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:13','2013-01-18 14:22:13'),(12079,1,'Linux','Public','ami-d78f57be','ubuntu/images/ebs/ubuntu-maverick-10.10-amd64-server-20120410',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:14','2013-01-18 14:22:14'),(12148,1,'Linux','Public','ami-d99e37b0','ubuntu/images/ebs/ubuntu-oneiric-11.10-amd64-server-20120722',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:16','2013-01-18 14:22:16'),(12360,1,'Linux','Public','ami-dfe54db6','ubuntu/images/ebs/ubuntu-lucid-10.04-i386-server-20120724',NULL,0,NULL,NULL,'ebs','2013-01-18 14:22:21','2013-01-18 14:22:21'),(12367,1,'Linux','Public','ami-e016ca89','ubuntu/images/ebs/ubuntu-natty-11.04-amd64-server-20120312',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:21','2013-01-18 14:22:21'),(12601,1,'Linux','Public','ami-e720ad8e','ubuntu/images/ebs/ubuntu-precise-12.04-i386-server-20121218',NULL,0,NULL,NULL,'ebs','2013-01-18 14:22:27','2013-01-18 14:22:27'),(13287,1,'Linux','Public','ami-fd20ad94','ubuntu/images/ebs/ubuntu-precise-12.04-amd64-server-20121218',NULL,1,NULL,NULL,'ebs','2013-01-18 14:22:45','2013-01-18 14:22:45');

/*Table structure for table `vendor_t` */

DROP TABLE IF EXISTS `vendor_t`;

CREATE TABLE `vendor_t` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name_text` varchar(48) NOT NULL,
  `create_date` datetime NOT NULL,
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

/*Data for the table `vendor_t` */

insert  into `vendor_t`(`id`,`vendor_name_text`,`create_date`,`lmod_date`) values (1,'Amazon EC2','2012-11-12 11:54:50','2012-11-12 11:54:50'),(2,'DreamFactory','2012-11-12 11:54:57','2012-11-12 11:54:57'),(3,'Windows Azure','2012-11-12 11:55:04','2012-11-12 11:55:04'),(4,'Rackspace','2012-11-12 11:55:12','2012-11-12 11:55:12'),(5,'OpenStack','0000-00-00 00:00:00','2013-03-02 23:36:49');

/* Trigger structure for table `cluster_server_asgn_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `csa_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `csa_beforeDelete` BEFORE DELETE ON `cluster_server_asgn_t` FOR EACH ROW BEGIN
	INSERT INTO `cluster_server_asgn_arch_t`
		SELECT *
		FROM `cluster_server_asgn_t`
		WHERE 
		`cluster_id` = old.cluster_id AND `server_id` = old.server_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `cluster_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `cluster_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `cluster_beforeDelete` BEFORE DELETE ON `cluster_t` FOR EACH ROW BEGIN
		INSERT INTO `cluster_arch_t` SELECT * FROM `cluster_t` WHERE `id` = old.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `deactivation_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `deactivation_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `deactivation_beforeDelete` BEFORE DELETE ON `deactivation_t` FOR EACH ROW BEGIN
	INSERT INTO `deactivation_arch_t` SELECT * FROM `deactivation_t` WHERE `id` = old.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `instance_server_asgn_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `isa_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `isa_beforeDelete` BEFORE DELETE ON `instance_server_asgn_t` FOR EACH ROW BEGIN
		INSERT INTO `instance_server_asgn_arch_t`
		
			SELECT * 
			FROM `instance_server_asgn_t`
			WHERE 
				`server_id` = old.server_id AND 
				`instance_id` = old.instance_id;
    END */$$


DELIMITER ;

/* Trigger structure for table `instance_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `instance_afterInsert` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `instance_afterInsert` AFTER INSERT ON `instance_t` FOR EACH ROW BEGIN
	DELETE FROM `deactivation_t` where user_id = new.user_id and instance_id = new.id;
    
	INSERT INTO `deactivation_t` 
		(user_id, instance_id, activate_by_date, create_date )
	VALUES
		(new.user_id, new.id, CURRENT_TIMESTAMP + INTERVAL 7 DAY, current_timestamp );
		
    END */$$


DELIMITER ;

/* Trigger structure for table `instance_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `instance_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `instance_beforeDelete` BEFORE DELETE ON `instance_t` FOR EACH ROW BEGIN
	Insert into `dfe_local`.`instance_arch_t` select * from `dfe_local`.`instance_t` where `id` = old.id;
	DELETE from `dfe_local`.`deactivation_t` where `user_id` = old.user_id and `instance_id` = old.id;
    END */$$


DELIMITER ;

/* Trigger structure for table `server_t` */

DELIMITER $$

/*!50003 DROP TRIGGER*//*!50032 IF EXISTS */ /*!50003 `server_beforeDelete` */$$

/*!50003 CREATE */ /*!50003 TRIGGER `server_beforeDelete` BEFORE DELETE ON `server_t` FOR EACH ROW BEGIN
		INSERT INTO `server_arch_t` SELECT * FROM `server_t` WHERE `id` = old.id;
    END */$$


DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
