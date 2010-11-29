-- MySQL dump 10.13  Distrib 5.1.24-rc, for apple-darwin8.11.1 (i686)
--
-- Host: localhost    Database: mcp
-- ------------------------------------------------------
-- Server version	5.1.24-rc

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `MCP_ACL_ROLES`
--

DROP TABLE IF EXISTS `MCP_ACL_ROLES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_ACL_ROLES` (
  `roles_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `role_const` varchar(96) NOT NULL,
  `role_label` varchar(96) NOT NULL,
  PRIMARY KEY (`roles_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`role_const`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`role_label`),
  KEY `sites_id_3` (`sites_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_ACL_ROLES_TO_PERMISSIONS`
--

DROP TABLE IF EXISTS `MCP_ACL_ROLES_TO_PERMISSIONS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_ACL_ROLES_TO_PERMISSIONS` (
  `roles_to_permissions_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `roles_id` bigint(20) unsigned NOT NULL,
  `permission` varchar(96) NOT NULL,
  PRIMARY KEY (`roles_to_permissions_id`),
  UNIQUE KEY `roles_id` (`roles_id`,`permission`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_ACL_USERS_TO_PERMISSIONS`
--

DROP TABLE IF EXISTS `MCP_ACL_USERS_TO_PERMISSIONS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_ACL_USERS_TO_PERMISSIONS` (
  `users_to_permissions_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned NOT NULL,
  `permission` varchar(96) NOT NULL,
  `deny` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`users_to_permissions_id`),
  UNIQUE KEY `users_id` (`users_id`,`permission`),
  KEY `users_id_2` (`users_id`,`permission`,`deny`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_ACL_USERS_TO_ROLES`
--

DROP TABLE IF EXISTS `MCP_ACL_USERS_TO_ROLES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_ACL_USERS_TO_ROLES` (
  `users_to_roles_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned NOT NULL,
  `roles_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`users_to_roles_id`),
  UNIQUE KEY `users_id` (`users_id`,`roles_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_CACHED_DATA`
--

DROP TABLE IF EXISTS `MCP_CACHED_DATA`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_CACHED_DATA` (
  `sites_id` bigint(20) unsigned NOT NULL,
  `cache_name` varchar(128) NOT NULL,
  `pkg` varchar(128) NOT NULL DEFAULT '',
  `cache_value` longblob,
  `flush_cache` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sites_id`,`cache_name`,`pkg`),
  KEY `sites_id` (`sites_id`,`cache_name`,`pkg`,`flush_cache`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_CACHED_IMAGES`
--

DROP TABLE IF EXISTS `MCP_CACHED_IMAGES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_CACHED_IMAGES` (
  `cached_images_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `base_images_id` blob NOT NULL,
  PRIMARY KEY (`cached_images_id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_CACHED_IMAGES_OPTIONS`
--

DROP TABLE IF EXISTS `MCP_CACHED_IMAGES_OPTIONS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_CACHED_IMAGES_OPTIONS` (
  `cached_images_options_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cached_images_id` bigint(20) unsigned NOT NULL,
  `images_option` varchar(128) DEFAULT NULL,
  `images_value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`cached_images_options_id`),
  UNIQUE KEY `cached_images_id` (`cached_images_id`,`images_option`,`images_value`),
  KEY `cached_images_id_2` (`cached_images_id`)
) ENGINE=MyISAM AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_COMMENTS`
--

DROP TABLE IF EXISTS `MCP_COMMENTS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_COMMENTS` (
  `comments_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `comment_type` varchar(24) NOT NULL,
  `comment_types_id` bigint(20) unsigned NOT NULL,
  `sites_id` bigint(20) unsigned NOT NULL,
  `commenter_id` bigint(20) unsigned DEFAULT NULL,
  `commenter_first_name` varchar(24) DEFAULT NULL,
  `commenter_last_name` varchar(24) DEFAULT NULL,
  `commenter_email` varchar(128) DEFAULT NULL,
  `content_type` enum('php','html','text') NOT NULL DEFAULT 'html',
  `comment_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `comment_content` longtext NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`comments_id`),
  KEY `parent_id` (`parent_id`),
  KEY `comment_type` (`comment_type`,`comment_types_id`),
  KEY `sites_id` (`sites_id`),
  KEY `commenter_id` (`commenter_id`),
  KEY `comment_published` (`comment_published`),
  KEY `commenter_email` (`commenter_email`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_COMMENT_TYPES`
--

DROP TABLE IF EXISTS `MCP_COMMENT_TYPES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_COMMENT_TYPES` (
  `comment_type` varchar(24) NOT NULL,
  PRIMARY KEY (`comment_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_CONFIG`
--

DROP TABLE IF EXISTS `MCP_CONFIG`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_CONFIG` (
  `sites_id` bigint(20) unsigned NOT NULL,
  `config_name` varchar(48) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`sites_id`,`config_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_IMAGES`
--

DROP TABLE IF EXISTS `MCP_IMAGES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_IMAGES` (
  `images_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `image_label` varchar(128) NOT NULL,
  `image_mime` varchar(128) NOT NULL,
  `image_size` varchar(128) NOT NULL,
  `image_width` mediumint(8) unsigned NOT NULL,
  `image_height` mediumint(8) unsigned NOT NULL,
  `md5_checksum` longtext NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`images_id`),
  KEY `sites_id` (`sites_id`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_2` (`sites_id`,`creators_id`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_NAVIGATION`
--

DROP TABLE IF EXISTS `MCP_NAVIGATION`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_NAVIGATION` (
  `navigation_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned DEFAULT NULL,
  `users_id` bigint(20) unsigned NOT NULL,
  `menu_title` varchar(96) NOT NULL,
  `display_title` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `menu_location` enum('top','left','bottom','right') NOT NULL DEFAULT 'left',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`navigation_id`),
  KEY `menu_location` (`menu_location`),
  KEY `sites_id` (`sites_id`),
  KEY `users_id` (`users_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_NAVIGATION_LINKS`
--

DROP TABLE IF EXISTS `MCP_NAVIGATION_LINKS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_NAVIGATION_LINKS` (
  `navigation_links_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_type` enum('nav','link') NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `link_title` varchar(96) NOT NULL,
  `browser_title` varchar(96) DEFAULT NULL,
  `page_heading` varchar(96) DEFAULT NULL,
  `link_url` text,
  `sites_id` bigint(20) unsigned DEFAULT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `sites_internal_url` varchar(96) DEFAULT NULL,
  `target_module` text,
  `target_template` text,
  `target_module_args` blob,
  `target_module_config` blob,
  `header_content` longtext,
  `body_content` longtext,
  `footer_content` longtext,
  `header_content_type` enum('html','php','text') NOT NULL DEFAULT 'html',
  `body_content_type` enum('html','php','text') NOT NULL DEFAULT 'html',
  `footer_content_type` enum('html','php','text') NOT NULL DEFAULT 'html',
  `target_window` enum('_blank','_self','_parent','_top') NOT NULL DEFAULT '_self',
  `new_window_name` varchar(128) DEFAULT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL,
  `links_data` blob,
  `datasource_query` text,
  `datasource_dao` varchar(255) DEFAULT NULL,
  `datasource_dao_method` varchar(255) DEFAULT NULL,
  `datasource_dao_args` blob,
  `datasources_id` bigint(20) unsigned DEFAULT NULL,
  `datasources_row_id` bigint(20) unsigned DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`navigation_links_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`sites_internal_url`),
  KEY `sites_id_2` (`sites_id`,`creators_id`),
  KEY `parent_type` (`parent_type`,`parent_id`),
  KEY `creators_id` (`creators_id`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_NODE_TYPES`
--

DROP TABLE IF EXISTS `MCP_NODE_TYPES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_NODE_TYPES` (
  `node_types_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned DEFAULT NULL COMMENT 'May be created by system',
  `pkg` varchar(128) DEFAULT NULL,
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`node_types_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`pkg`,`system_name`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`pkg`,`human_name`),
  KEY `creators_id` (`creators_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_SESSIONS`
--

DROP TABLE IF EXISTS `MCP_SESSIONS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_SESSIONS` (
  `sessions_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` varchar(255) NOT NULL,
  `pid` varchar(255) NOT NULL,
  `users_id` bigint(20) unsigned DEFAULT NULL,
  `created_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_on_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` timestamp NULL DEFAULT NULL,
  `session_data` blob,
  PRIMARY KEY (`sessions_id`),
  UNIQUE KEY `sid` (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=798 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_SITES`
--

DROP TABLE IF EXISTS `MCP_SITES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_SITES` (
  `sites_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(128) NOT NULL,
  `site_directory` varchar(128) NOT NULL,
  `site_module_prefix` varchar(64) NOT NULL DEFAULT '',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sites_id`),
  UNIQUE KEY `site_name` (`site_name`),
  UNIQUE KEY `site_directory` (`site_directory`),
  UNIQUE KEY `site_module_prefix` (`site_module_prefix`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_TERMS`
--

DROP TABLE IF EXISTS `MCP_TERMS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_TERMS` (
  `terms_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_type` enum('vocabulary','term') NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `weight` tinyint(3) unsigned DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`terms_id`),
  UNIQUE KEY `parent_type` (`parent_type`,`parent_id`,`system_name`),
  UNIQUE KEY `parent_type_2` (`parent_type`,`parent_id`,`human_name`),
  KEY `creators_id` (`creators_id`)
) ENGINE=MyISAM AUTO_INCREMENT=300 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_USERS`
--

DROP TABLE IF EXISTS `MCP_USERS`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_USERS` (
  `users_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `username` varchar(24) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `pwd` char(40) NOT NULL,
  `uuid` char(40) DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `last_login_timestamp` timestamp NULL DEFAULT NULL,
  `banned_until_timestamp` timestamp NULL DEFAULT NULL,
  `user_data` blob,
  PRIMARY KEY (`users_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`username`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`email_address`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_VOCABULARY`
--

DROP TABLE IF EXISTS `MCP_VOCABULARY`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_VOCABULARY` (
  `vocabulary_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned DEFAULT NULL COMMENT 'The system will created some global vocabularies like countries that are shared between all sites.',
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `pkg` varchar(128) DEFAULT NULL,
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `weight` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`vocabulary_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`pkg`,`system_name`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`pkg`,`human_name`),
  KEY `creators_id` (`creators_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `MCP_NODES`
--

DROP TABLE IF EXISTS `MCP_NODES`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `MCP_NODES` (
  `nodes_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `authors_id` bigint(20) unsigned NOT NULL,
  `node_types_id` bigint(20) unsigned DEFAULT NULL,
  `content_type` enum('html','php','text') NOT NULL DEFAULT 'html',
  `intro_type` enum('html','php','text') NOT NULL DEFAULT 'html',
  `node_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `node_url` varchar(128) NOT NULL,
  `node_title` varchar(128) NOT NULL,
  `node_subtitle` varchar(128) DEFAULT NULL,
  `node_content` longtext NOT NULL,
  `intro_content` longtext,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`nodes_id`),
  UNIQUE KEY `node_url` (`node_url`,`sites_id`),
  KEY `sites_id` (`sites_id`),
  KEY `node_published` (`node_published`),
  KEY `authors_id` (`authors_id`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-10-28 10:19:22
