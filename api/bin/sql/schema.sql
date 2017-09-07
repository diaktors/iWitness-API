# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: vn-wo-db-01 (MySQL 5.5.37-0ubuntu0.12.04.1)
# Database: perpcast_api
# Generation Time: 2014-07-15 04:53:35 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table asset
# ------------------------------------------------------------

DROP TABLE IF EXISTS `asset`;

CREATE TABLE `asset` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `event_id` binary(16) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `media_type` varchar(200) DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `lat` float(10,6) DEFAULT NULL,
  `lng` float(10,6) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `meta` blob,
  `created_at` int(10) DEFAULT NULL,
  `flags` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `display_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  `attempted` int(10) unsigned DEFAULT '0',
  `log` text,
  PRIMARY KEY (`id`),
  KEY `FK_asset_user` (`user_id`),
  KEY `FK_asset_event` (`event_id`),
  CONSTRAINT `FK_asset_event` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  CONSTRAINT `FK_asset_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table contact
# ------------------------------------------------------------

DROP TABLE IF EXISTS `contact`;

CREATE TABLE `contact` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `phone_alt` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flags` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `relation_type` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `PK_contact_user_idx` (`user_id`),
  CONSTRAINT `PK_contact_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table coupon
# ------------------------------------------------------------

DROP TABLE IF EXISTS `coupon`;

CREATE TABLE `coupon` (
  `id` binary(16) NOT NULL,
  `code` varchar(40) NOT NULL,
  `current_usages` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `name` varchar(100) DEFAULT NULL,
  `max_redemption` int(10) NOT NULL DEFAULT '1',
  `redemption_start_date` int(10) DEFAULT NULL,
  `redemption_end_date` int(10) DEFAULT NULL,
  `subscription_length` int(10) DEFAULT NULL,
  `code_string` varchar(40) DEFAULT NULL,
  `plan` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'should check plan for safekid and seattle university',
  `recipient_email` varchar(40) DEFAULT NULL COMMENT 'gift card receipient email',
  `sender_id` binary(16) DEFAULT NULL COMMENT 'gift card sender email',
  `message` varchar(501) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'giftcard message',
  `is_deliveved` int(1) NOT NULL DEFAULT '0' COMMENT 'giftcard is delivered',
  `delivery_date` int(11) DEFAULT NULL COMMENT 'gift card deliverty date',
  `subscription_id` binary(16) DEFAULT NULL COMMENT 'tracsaction to create it, null if create by admin',
  `type` int(2) NOT NULL DEFAULT '1' COMMENT 'gift card or coupon',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table device
# ------------------------------------------------------------

DROP TABLE IF EXISTS `device`;

CREATE TABLE `device` (
  `id` binary(16) NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  `flags` mediumint(7) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table event
# ------------------------------------------------------------

DROP TABLE IF EXISTS `event`;

CREATE TABLE `event` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) DEFAULT NULL,
  `initial_lat` float(10,6) DEFAULT NULL,
  `initial_long` float(10,6) DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `display_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  `log` text,
  `attempted` int(10) unsigned DEFAULT '0',
  `flags` mediumint(7) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_event_user` (`user_id`),
  CONSTRAINT `FK_event_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table message
# ------------------------------------------------------------

DROP TABLE IF EXISTS `message`;

CREATE TABLE `message` (
  `id` binary(16) NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `flags` mediumint(7) unsigned DEFAULT '0' COMMENT 'broadcast or default message',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_access_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_access_tokens`;

CREATE TABLE `oauth_access_tokens` (
  `access_token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_authorization_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_authorization_codes`;

CREATE TABLE `oauth_authorization_codes` (
  `authorization_code` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `redirect_uri` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_token` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`authorization_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_clients
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_clients`;

CREATE TABLE `oauth_clients` (
  `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `client_secret` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `redirect_uri` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `grant_types` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_jwt
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_jwt`;

CREATE TABLE `oauth_jwt` (
  `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `public_key` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_public_keys
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_public_keys`;

CREATE TABLE `oauth_public_keys` (
  `client_id` text COLLATE utf8_unicode_ci,
  `public_key` text COLLATE utf8_unicode_ci,
  `private_key` text COLLATE utf8_unicode_ci,
  `encryption_algorithm` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'RS256',
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_refresh_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_refresh_tokens`;

CREATE TABLE `oauth_refresh_tokens` (
  `refresh_token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `client_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`refresh_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table oauth_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_scopes`;

CREATE TABLE `oauth_scopes` (
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'supported',
  `scope` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `client_id` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_default` smallint(6) DEFAULT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table subscription
# ------------------------------------------------------------

DROP TABLE IF EXISTS `subscription`;

CREATE TABLE `subscription` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) DEFAULT NULL,
  `original_phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `original_phone_model` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'should normalized ',
  `customer_ip` varchar(20) DEFAULT NULL,
  `coupon_id` binary(16) DEFAULT NULL,
  `arb_billing_id` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Automated Recurring Billing  id see http://www.authorize.net/support/ARB_guide.pdf',
  `plan` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '“plan” with an equally as terrible setup',
  `amount` double NOT NULL DEFAULT '0',
  `type` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `start_at` int(11) NOT NULL,
  `expire_at` int(11) NOT NULL,
  `suspended` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  `receipt_id` varchar(16) DEFAULT NULL,
  `payment_gateway` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_subscription_user` (`user_id`),
  KEY `FK_subscription_coupon` (`coupon_id`),
  CONSTRAINT `FK_subscription_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupon` (`id`),
  CONSTRAINT `FK_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` binary(16) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `phone_alt` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(50) DEFAULT NULL,
  `email` varchar(128) NOT NULL,
  `gender` tinyint(1) DEFAULT NULL,
  `birth_date` int(11) DEFAULT NULL,
  `height_feet` float DEFAULT NULL,
  `height_inches` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `eye_color` varchar(20) DEFAULT NULL,
  `hair_color` varchar(20) DEFAULT NULL,
  `ethnicity` varchar(100) DEFAULT NULL,
  `dist_feature` varchar(255) DEFAULT NULL,
  `photo` varchar(120) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `type` mediumint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'user type in system can be user, admin, prospect, sender, receiver',
  `subscription_id` binary(16) DEFAULT NULL COMMENT 'current subcription infomation',
  `subscription_start_at` int(11) DEFAULT NULL,
  `subscription_expire_at` int(11) DEFAULT NULL,
  `platform` varchar(200) DEFAULT NULL COMMENT 'prospect',
  `ip_address` varchar(40) DEFAULT NULL COMMENT 'prospect',
  `user_agent` varchar(250) DEFAULT NULL COMMENT 'prospect',
  `flags` mediumint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'status of user ex: banding or contact status',
  `secret_key` varchar(128) DEFAULT NULL COMMENT 'contact conformation key',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  `subscription_last_email` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table user_device
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_device`;

CREATE TABLE `user_device` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `device_id` binary(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_device_user` (`user_id`),
  KEY `FK_user_device_device` (`device_id`),
  CONSTRAINT `FK_user_device_device` FOREIGN KEY (`device_id`) REFERENCES `device` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_user_device_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table user_message
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_message`;

CREATE TABLE `user_message` (
  `id` binary(16) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `message_id` binary(16) NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `created` int(10) NOT NULL,
  `modified` int(10) DEFAULT NULL,
  `deleted` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_message_user` (`user_id`),
  KEY `FK_user_message_message` (`message_id`),
  CONSTRAINT `FK_user_message_message` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_user_message_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




--
-- Dumping routines (FUNCTION) for database 'perpcast_api'
--
DELIMITER ;;

# Dump of FUNCTION UUID_TO_BIN
# ------------------------------------------------------------

/*!50003 DROP FUNCTION IF EXISTS `UUID_TO_BIN` */;;
/*!50003 SET SESSION SQL_MODE="NO_AUTO_VALUE_ON_ZERO"*/;;
/*!50003 CREATE*/ /*!50003 FUNCTION `UUID_TO_BIN`(id CHAR(36)) RETURNS binary(16)
    NO SQL
    DETERMINISTIC
BEGIN
      RETURN UNHEX(REPLACE(id, '-', ''));
    END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
# Dump of FUNCTION UUID_TO_STR
# ------------------------------------------------------------

/*!50003 DROP FUNCTION IF EXISTS `UUID_TO_STR` */;;
/*!50003 SET SESSION SQL_MODE="NO_AUTO_VALUE_ON_ZERO"*/;;
/*!50003 CREATE*/ /*!50003 FUNCTION `UUID_TO_STR`(id BINARY(16)) RETURNS char(36) CHARSET utf8
    DETERMINISTIC
BEGIN
      return LOWER(CONCAT(SUBSTRING(HEX(id),1,8),'-',SUBSTRING(HEX(id),9,4),'-',SUBSTRING(HEX(id),13,4),'-',SUBSTRING(HEX(id),17,4),'-',SUBSTRING(HEX(id),21)));
    END */;;

/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;;
DELIMITER ;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
