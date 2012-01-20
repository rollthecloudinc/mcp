-- MySQL dump 10.13  Distrib 5.1.52, for apple-darwin10.3.0 (i386)
--
-- Host: localhost    Database: mcp
-- ------------------------------------------------------
-- Server version	5.1.52

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
-- Table structure for table `MCP_CACHED_DATA`
--

DROP TABLE IF EXISTS `MCP_CACHED_DATA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  KEY `sites_id` (`sites_id`,`cache_name`,`pkg`,`flush_cache`),
  KEY `sites_id_2` (`sites_id`),
  CONSTRAINT `mcp_cached_data_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_CACHED_IMAGES`
--

DROP TABLE IF EXISTS `MCP_CACHED_IMAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_CACHED_IMAGES` (
  `cached_images_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `base_images_id` blob NOT NULL,
  PRIMARY KEY (`cached_images_id`)
) ENGINE=InnoDB AUTO_INCREMENT=302 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_CACHED_IMAGES_OPTIONS`
--

DROP TABLE IF EXISTS `MCP_CACHED_IMAGES_OPTIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_CACHED_IMAGES_OPTIONS` (
  `cached_images_options_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cached_images_id` bigint(20) unsigned NOT NULL,
  `images_option` varchar(128) DEFAULT NULL,
  `images_value` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`cached_images_options_id`),
  UNIQUE KEY `cached_images_id` (`cached_images_id`,`images_option`,`images_value`),
  KEY `cached_images_id_2` (`cached_images_id`),
  CONSTRAINT `mcp_cached_images_options_ibfk_1` FOREIGN KEY (`cached_images_id`) REFERENCES `mcp_cached_images` (`cached_images_id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_COMMENTS`
--

DROP TABLE IF EXISTS `MCP_COMMENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `content_type` varchar(25) NOT NULL DEFAULT 'html',
  `comment_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `comment_content` longtext NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`comments_id`),
  KEY `parent_id` (`parent_id`),
  KEY `comment_type` (`comment_type`,`comment_types_id`),
  KEY `sites_id` (`sites_id`),
  KEY `commenter_id` (`commenter_id`),
  KEY `comment_published` (`comment_published`),
  KEY `commenter_email` (`commenter_email`),
  KEY `deleted` (`deleted`),
  KEY `content_type` (`content_type`),
  CONSTRAINT `mcp_comments_ibfk_1` FOREIGN KEY (`content_type`) REFERENCES `mcp_enum_content_types` (`system_name`),
  CONSTRAINT `mcp_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `mcp_comments` (`comments_id`),
  CONSTRAINT `mcp_comments_ibfk_3` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_comments_ibfk_4` FOREIGN KEY (`commenter_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_COMMENT_TYPES`
--

DROP TABLE IF EXISTS `MCP_COMMENT_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_COMMENT_TYPES` (
  `comment_type` varchar(24) NOT NULL,
  PRIMARY KEY (`comment_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_CONFIG`
--

DROP TABLE IF EXISTS `MCP_CONFIG`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_CONFIG` (
  `sites_id` bigint(20) unsigned NOT NULL,
  `config_name` varchar(48) NOT NULL,
  `config_value` text NOT NULL,
  PRIMARY KEY (`sites_id`,`config_name`),
  KEY `sites_id` (`sites_id`),
  CONSTRAINT `mcp_config_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_ENUM_CONTENT_TYPES`
--

DROP TABLE IF EXISTS `MCP_ENUM_CONTENT_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_ENUM_CONTENT_TYPES` (
  `system_name` varchar(25) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  PRIMARY KEY (`system_name`),
  UNIQUE KEY `human_name` (`human_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Content types are shared between all sites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_ENUM_MIME_TYPES`
--

DROP TABLE IF EXISTS `MCP_ENUM_MIME_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_ENUM_MIME_TYPES` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(16) NOT NULL,
  `subtype` varchar(111) NOT NULL,
  `ext` varchar(8) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`subtype`,`ext`)
) ENGINE=MyISAM AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_ENUM_VIDEO_CODECS`
--

DROP TABLE IF EXISTS `MCP_ENUM_VIDEO_CODECS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_ENUM_VIDEO_CODECS` (
  `codecs_id` varchar(17) NOT NULL,
  `description` longtext,
  PRIMARY KEY (`codecs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_ENUM_VIDEO_CONTAINERS`
--

DROP TABLE IF EXISTS `MCP_ENUM_VIDEO_CONTAINERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_ENUM_VIDEO_CONTAINERS` (
  `containers_id` varchar(17) NOT NULL COMMENT 'container name',
  `ext` varchar(9) NOT NULL COMMENT 'container file extension',
  `description` longtext,
  PRIMARY KEY (`containers_id`),
  UNIQUE KEY `ext` (`ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_FIELDS`
--

DROP TABLE IF EXISTS `MCP_FIELDS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_FIELDS` (
  `fields_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL COMMENT 'person who created field',
  `entity_type` varchar(48) NOT NULL COMMENT 'Entity type such as; node type, user, etc',
  `entities_id` bigint(20) unsigned DEFAULT NULL COMMENT 'optional ID of entity row such as; specified node type for node type extensions',
  `cfg_name` varchar(128) NOT NULL COMMENT 'Unique dynamic field internal reference name',
  `cfg_label` varchar(128) NOT NULL COMMENT 'Label that will be shown next to form input',
  `cfg_widget` varchar(57) DEFAULT NULL,
  `cfg_description` text COMMENT 'Description shown to user about form purpose, contents, etc',
  `cfg_required` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'whether the field is required ie. not allowed to be empty',
  `cfg_default` text COMMENT 'Default value for the field',
  `cfg_min` smallint(5) unsigned DEFAULT NULL COMMENT 'minimum number of characters, if applicable',
  `cfg_max` smallint(5) unsigned DEFAULT NULL COMMENT 'maximum number of characters, if applicable',
  `cfg_type` varchar(128) DEFAULT NULL COMMENT 'application field type',
  `cfg_values` longtext COMMENT 'serialized array of select values - when supplied field is represented as select menu',
  `cfg_sql` text COMMENT 'Custom SQL to derive values to shown to user to select - use with caution',
  `cfg_dao_pkg` varchar(255) DEFAULT NULL COMMENT 'DAO package binding to derive select values',
  `cfg_dao_method` varchar(128) DEFAULT NULL COMMENT 'DAO package binding method to call',
  `cfg_dao_args` longtext COMMENT 'Serialized array of arguments to pass to DAO binding method call',
  `cfg_textarea` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether the field will be displayed as a textarea',
  `cfg_static` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'make this field invisible to user interface, always use default value',
  `cfg_size` tinyint(3) unsigned DEFAULT NULL COMMENT 'size attribute of a select menu',
  `cfg_serialized` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Whether data will be stored serialized',
  `cfg_multi` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cfg_multi_limit` smallint(5) unsigned DEFAULT NULL,
  `cfg_media` enum('image','video','audio','file') DEFAULT NULL,
  `db_value` enum('varchar','text','int','price','bool','timestamp','date') NOT NULL DEFAULT 'text',
  `db_ref_table` varchar(128) DEFAULT NULL COMMENT 'Foreign key reference table',
  `db_ref_col` varchar(128) DEFAULT NULL COMMENT 'Foreign key reference table column',
  `db_ref_context` varchar(128) DEFAULT NULL,
  `db_ref_context_id` bigint(20) unsigned DEFAULT NULL,
  `db_varchar` varchar(255) DEFAULT NULL,
  `db_text` longtext,
  `db_int` bigint(20) DEFAULT NULL,
  `db_price` decimal(20,2) DEFAULT NULL,
  `db_bool` tinyint(3) unsigned DEFAULT NULL,
  `db_timestamp` timestamp NULL DEFAULT NULL,
  `db_date` date DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`fields_id`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`entity_type`,`entities_id`,`cfg_name`,`deleted`),
  KEY `entity_type` (`entity_type`,`entities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_FIELD_VALUES`
--

DROP TABLE IF EXISTS `MCP_FIELD_VALUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_FIELD_VALUES` (
  `field_values_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fields_id` bigint(20) unsigned NOT NULL COMMENT 'Field foreign key',
  `rows_id` bigint(20) unsigned NOT NULL COMMENT 'Field Entity row id',
  `db_varchar` varchar(255) DEFAULT NULL COMMENT 'Variable length string under 256 characters',
  `db_text` longtext COMMENT 'Text entry longer than 255 characters',
  `db_int` bigint(20) DEFAULT NULL COMMENT 'Neg/Pos integer value',
  `db_price` decimal(20,2) DEFAULT NULL COMMENT 'Price value',
  `db_bool` tinyint(3) unsigned DEFAULT NULL COMMENT 'Boolean binary 0 or 1 representation',
  `db_timestamp` timestamp NULL DEFAULT NULL,
  `db_date` date DEFAULT NULL,
  `weight` mediumint(9) DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`field_values_id`),
  KEY `db_varchar` (`db_varchar`),
  KEY `db_int` (`db_int`),
  KEY `db_price` (`db_price`),
  KEY `db_bool` (`db_bool`),
  KEY `weight` (`weight`),
  KEY `db_timestamp` (`db_timestamp`),
  KEY `db_date` (`db_date`),
  KEY `fields_id_2` (`fields_id`,`rows_id`,`deleted`),
  KEY `fields_id` (`fields_id`),
  CONSTRAINT `mcp_field_values_ibfk_1` FOREIGN KEY (`fields_id`) REFERENCES `mcp_fields` (`fields_id`)
) ENGINE=InnoDB AUTO_INCREMENT=715 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MEDIA_FILES`
--

DROP TABLE IF EXISTS `MCP_MEDIA_FILES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MEDIA_FILES` (
  `files_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `file_label` varchar(128) NOT NULL,
  `file_mime` varchar(128) NOT NULL,
  `file_size` varchar(128) NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`files_id`),
  KEY `sites_id` (`sites_id`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_2` (`sites_id`,`creators_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `mcp_media_files_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_media_files_ibfk_2` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MEDIA_IMAGES`
--

DROP TABLE IF EXISTS `MCP_MEDIA_IMAGES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MEDIA_IMAGES` (
  `images_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `image_label` varchar(128) NOT NULL,
  `image_mime` varchar(128) NOT NULL,
  `image_size` varchar(128) NOT NULL,
  `image_width` mediumint(8) unsigned NOT NULL,
  `image_height` mediumint(8) unsigned NOT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `image_caption` text,
  `md5_checksum` longtext NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`images_id`),
  KEY `sites_id` (`sites_id`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_2` (`sites_id`,`creators_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `mcp_media_images_ibfk_1` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_media_images_ibfk_2` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`)
) ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MEDIA_VIDEO`
--

DROP TABLE IF EXISTS `MCP_MEDIA_VIDEO`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MEDIA_VIDEO` (
  `videos_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `video_label` varchar(128) NOT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`videos_id`),
  KEY `sites_id` (`sites_id`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_2` (`sites_id`,`creators_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `mcp_media_video_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_media_video_ibfk_2` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MEDIA_VIDEO_FORMATS`
--

DROP TABLE IF EXISTS `MCP_MEDIA_VIDEO_FORMATS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MEDIA_VIDEO_FORMATS` (
  `video_formats_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `videos_id` bigint(20) unsigned NOT NULL,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned NOT NULL,
  `codecs_id` varchar(17) NOT NULL COMMENT 'Video codec',
  `containers_id` varchar(17) NOT NULL COMMENT 'Video container type',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`video_formats_id`),
  UNIQUE KEY `videos_id_2` (`videos_id`,`codecs_id`,`containers_id`,`deleted`),
  KEY `sites_id` (`sites_id`),
  KEY `creators_id` (`creators_id`),
  KEY `videos_id` (`videos_id`),
  KEY `codecs_id` (`codecs_id`),
  KEY `containers_id` (`containers_id`),
  CONSTRAINT `mcp_media_video_formats_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_media_video_formats_ibfk_2` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_media_video_formats_ibfk_3` FOREIGN KEY (`videos_id`) REFERENCES `mcp_media_video` (`videos_id`),
  CONSTRAINT `mcp_media_video_formats_ibfk_4` FOREIGN KEY (`codecs_id`) REFERENCES `mcp_enum_video_codecs` (`codecs_id`),
  CONSTRAINT `mcp_media_video_formats_ibfk_5` FOREIGN KEY (`containers_id`) REFERENCES `mcp_enum_video_containers` (`containers_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MENUS`
--

DROP TABLE IF EXISTS `MCP_MENUS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MENUS` (
  `menus_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned DEFAULT NULL,
  `users_id` bigint(20) unsigned NOT NULL,
  `system_name` varchar(128) NOT NULL,
  `menu_title` varchar(128) NOT NULL,
  `display_title` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `menu_location` enum('top','left','bottom','right') NOT NULL DEFAULT 'left',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`menus_id`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`system_name`,`deleted`),
  KEY `menu_location` (`menu_location`),
  KEY `sites_id` (`sites_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MENU_LINKS`
--

DROP TABLE IF EXISTS `MCP_MENU_LINKS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MENU_LINKS` (
  `menu_links_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menus_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `display_title` varchar(128) NOT NULL,
  `browser_title` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `mod_path` varchar(255) DEFAULT NULL,
  `mod_tpl` varchar(255) DEFAULT NULL,
  `mod_args` blob,
  `mod_cfg` blob,
  `absolute_url` longtext,
  `content_header` longtext,
  `content_header_type` varchar(25) DEFAULT NULL,
  `content_footer` longtext,
  `content_footer_type` varchar(25) DEFAULT NULL,
  `global_data` blob,
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`menu_links_id`),
  KEY `menus_id` (`menus_id`),
  KEY `parent_id` (`parent_id`),
  KEY `content_header_type` (`content_header_type`),
  KEY `content_footer_type` (`content_footer_type`),
  KEY `path` (`path`),
  KEY `deleted` (`deleted`),
  KEY `creators_id` (`creators_id`),
  CONSTRAINT `mcp_menu_links_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `mcp_menu_links` (`menu_links_id`),
  CONSTRAINT `mcp_menu_links_ibfk_3` FOREIGN KEY (`content_header_type`) REFERENCES `mcp_enum_content_types` (`system_name`),
  CONSTRAINT `mcp_menu_links_ibfk_4` FOREIGN KEY (`content_footer_type`) REFERENCES `mcp_enum_content_types` (`system_name`),
  CONSTRAINT `mcp_menu_links_ibfk_5` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_menu_links_ibfk_6` FOREIGN KEY (`menus_id`) REFERENCES `mcp_menus` (`menus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='New version of navigation component';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_MENU_LINKS_DATASOURCES`
--

DROP TABLE IF EXISTS `MCP_MENU_LINKS_DATASOURCES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_MENU_LINKS_DATASOURCES` (
  `menu_links_id` bigint(20) unsigned NOT NULL,
  `dao` varchar(255) NOT NULL COMMENT 'Application pkg path to DAO',
  `method` varchar(255) NOT NULL COMMENT 'Name of method to call',
  `args` blob COMMENT 'Optional arguments to pass to method call to derive dynamic links',
  `description` longtext,
  PRIMARY KEY (`menu_links_id`),
  CONSTRAINT `mcp_menu_links_datasources_ibfk_1` FOREIGN KEY (`menu_links_id`) REFERENCES `mcp_menu_links` (`menu_links_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Menu link extension that represents a datasource to derive d';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_NODES`
--

DROP TABLE IF EXISTS `MCP_NODES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_NODES` (
  `nodes_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `authors_id` bigint(20) unsigned NOT NULL,
  `node_types_id` bigint(20) unsigned NOT NULL,
  `content_type` varchar(25) NOT NULL DEFAULT 'html',
  `intro_type` varchar(25) NOT NULL DEFAULT 'html',
  `node_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `node_url` varchar(128) NOT NULL,
  `node_title` varchar(128) NOT NULL,
  `node_subtitle` varchar(128) DEFAULT NULL,
  `node_content` longtext NOT NULL,
  `intro_content` longtext,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`nodes_id`),
  UNIQUE KEY `node_url` (`node_url`,`sites_id`,`node_types_id`,`deleted`),
  KEY `sites_id` (`sites_id`),
  KEY `node_published` (`node_published`),
  KEY `authors_id` (`authors_id`),
  KEY `deleted` (`deleted`),
  KEY `node_types_id` (`node_types_id`),
  KEY `content_type` (`content_type`),
  KEY `intro_type` (`intro_type`),
  KEY `node_types_id_2` (`node_types_id`,`deleted`),
  CONSTRAINT `mcp_nodes_ibfk_1` FOREIGN KEY (`content_type`) REFERENCES `mcp_enum_content_types` (`system_name`),
  CONSTRAINT `mcp_nodes_ibfk_2` FOREIGN KEY (`intro_type`) REFERENCES `mcp_enum_content_types` (`system_name`),
  CONSTRAINT `mcp_nodes_ibfk_3` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_nodes_ibfk_4` FOREIGN KEY (`authors_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_nodes_ibfk_5` FOREIGN KEY (`node_types_id`) REFERENCES `mcp_node_types` (`node_types_id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_NODE_TYPES`
--

DROP TABLE IF EXISTS `MCP_NODE_TYPES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_NODE_TYPES` (
  `node_types_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `creators_id` bigint(20) unsigned DEFAULT NULL COMMENT 'May be created by system',
  `pkg` varchar(128) NOT NULL DEFAULT '',
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `theme_tpl` varchar(255) DEFAULT NULL,
  `form_tpl` varchar(255) DEFAULT NULL,
  `description` longtext,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`node_types_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`pkg`,`system_name`,`deleted`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`pkg`,`human_name`,`deleted`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_3` (`sites_id`),
  CONSTRAINT `mcp_node_types_ibfk_1` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_node_types_ibfk_2` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_PERMISSIONS_ROLES`
--

DROP TABLE IF EXISTS `MCP_PERMISSIONS_ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_PERMISSIONS_ROLES` (
  `permissions_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `roles_id` bigint(20) unsigned NOT NULL,
  `item_type` varchar(56) NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `add` tinyint(3) unsigned DEFAULT NULL,
  `edit` tinyint(3) unsigned DEFAULT NULL,
  `delete` tinyint(3) unsigned DEFAULT NULL,
  `read` tinyint(3) unsigned DEFAULT NULL,
  `add_own` tinyint(3) unsigned DEFAULT NULL,
  `edit_own` tinyint(3) unsigned DEFAULT NULL,
  `delete_own` tinyint(3) unsigned DEFAULT NULL,
  `read_own` tinyint(3) unsigned DEFAULT NULL,
  `add_child` tinyint(3) unsigned DEFAULT NULL,
  `edit_child` tinyint(3) unsigned DEFAULT NULL,
  `delete_child` tinyint(3) unsigned DEFAULT NULL,
  `read_child` tinyint(3) unsigned DEFAULT NULL,
  `add_own_child` tinyint(3) unsigned DEFAULT NULL,
  `edit_own_child` tinyint(3) unsigned DEFAULT NULL,
  `delete_own_child` tinyint(3) unsigned DEFAULT NULL,
  `read_own_child` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`permissions_id`),
  UNIQUE KEY `item_type` (`item_type`,`item_id`,`roles_id`),
  KEY `roles_id` (`roles_id`),
  CONSTRAINT `mcp_permissions_roles_ibfk_1` FOREIGN KEY (`roles_id`) REFERENCES `mcp_roles` (`roles_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_PERMISSIONS_USERS`
--

DROP TABLE IF EXISTS `MCP_PERMISSIONS_USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_PERMISSIONS_USERS` (
  `permissions_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned NOT NULL,
  `item_type` varchar(56) NOT NULL,
  `item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `add` tinyint(3) unsigned DEFAULT NULL,
  `edit` tinyint(3) unsigned DEFAULT NULL,
  `delete` tinyint(3) unsigned DEFAULT NULL,
  `read` tinyint(3) unsigned DEFAULT NULL,
  `add_own` tinyint(3) unsigned DEFAULT NULL,
  `edit_own` tinyint(3) unsigned DEFAULT NULL,
  `delete_own` tinyint(3) unsigned DEFAULT NULL,
  `read_own` tinyint(3) unsigned DEFAULT NULL,
  `add_child` tinyint(3) unsigned DEFAULT NULL,
  `edit_child` tinyint(3) unsigned DEFAULT NULL,
  `delete_child` tinyint(3) unsigned DEFAULT NULL,
  `read_child` tinyint(3) unsigned DEFAULT NULL,
  `add_own_child` tinyint(3) unsigned DEFAULT NULL,
  `edit_own_child` tinyint(3) unsigned DEFAULT NULL,
  `delete_own_child` tinyint(3) unsigned DEFAULT NULL,
  `read_own_child` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`permissions_id`),
  UNIQUE KEY `item_type` (`item_type`,`item_id`,`users_id`),
  KEY `users_id` (`users_id`),
  CONSTRAINT `mcp_permissions_users_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_ROLES`
--

DROP TABLE IF EXISTS `MCP_ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_ROLES` (
  `roles_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL COMMENT 'site that the role belongs to. System created roles will be null.',
  `creators_id` bigint(20) unsigned DEFAULT NULL COMMENT 'user that created the role',
  `pkg` varchar(128) NOT NULL DEFAULT '' COMMENT 'package that the role belongs to',
  `system_name` varchar(128) NOT NULL COMMENT 'Unique name of role within site',
  `human_name` varchar(128) NOT NULL COMMENT 'Unique label/title of role within site',
  `description` longtext COMMENT 'descrption of role',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0' COMMENT '0 means role has not been deleted and NULL means that is has been deleted',
  PRIMARY KEY (`roles_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`pkg`,`system_name`,`deleted`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`pkg`,`human_name`,`deleted`),
  KEY `sites_id_3` (`sites_id`),
  KEY `roles_id` (`roles_id`,`deleted`),
  KEY `creators_id` (`creators_id`),
  CONSTRAINT `mcp_roles_ibfk_1` FOREIGN KEY (`sites_id`) REFERENCES `mcp_sites` (`sites_id`),
  CONSTRAINT `mcp_roles_ibfk_2` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_SESSIONS`
--

DROP TABLE IF EXISTS `MCP_SESSIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  UNIQUE KEY `sid` (`sid`),
  KEY `users_id` (`users_id`),
  CONSTRAINT `mcp_sessions_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1453 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_SITES`
--

DROP TABLE IF EXISTS `MCP_SITES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_SITES` (
  `sites_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `site_name` varchar(128) NOT NULL,
  `site_directory` varchar(128) NOT NULL,
  `site_module_prefix` varchar(64) NOT NULL DEFAULT '',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`sites_id`),
  UNIQUE KEY `site_name` (`site_name`,`deleted`),
  UNIQUE KEY `site_directory` (`site_directory`,`deleted`),
  UNIQUE KEY `site_module_prefix` (`site_module_prefix`,`deleted`),
  KEY `creators_id` (`creators_id`),
  CONSTRAINT `mcp_sites_ibfk_1` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_TERMS`
--

DROP TABLE IF EXISTS `MCP_TERMS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_TERMS` (
  `terms_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vocabulary_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `weight` tinyint(3) unsigned DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`terms_id`),
  KEY `creators_id` (`creators_id`),
  KEY `vocabulary_id` (`vocabulary_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `mcp_terms_ibfk_1` FOREIGN KEY (`vocabulary_id`) REFERENCES `mcp_vocabulary` (`vocabulary_id`),
  CONSTRAINT `mcp_terms_ibfk_2` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`),
  CONSTRAINT `mcp_terms_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `mcp_terms` (`terms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=401 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_USERS`
--

DROP TABLE IF EXISTS `MCP_USERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_USERS` (
  `users_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL,
  `username` varchar(24) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `pwd` char(40) NOT NULL,
  `uuid` char(40) DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `last_login_timestamp` timestamp NULL DEFAULT NULL,
  `banned_until_timestamp` timestamp NULL DEFAULT NULL,
  `user_data` blob,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`users_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`username`,`deleted`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`email_address`,`deleted`),
  KEY `sites_id_3` (`sites_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_USERS_ROLES`
--

DROP TABLE IF EXISTS `MCP_USERS_ROLES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_USERS_ROLES` (
  `users_roles_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` bigint(20) unsigned NOT NULL COMMENT 'mcp_user foreign key',
  `roles_id` bigint(20) unsigned NOT NULL COMMENT 'mcp_role foreign key',
  PRIMARY KEY (`users_roles_id`),
  UNIQUE KEY `users_id` (`users_id`,`roles_id`),
  KEY `roles_id` (`roles_id`),
  KEY `users_id_2` (`users_id`),
  CONSTRAINT `mcp_users_roles_ibfk_1` FOREIGN KEY (`roles_id`) REFERENCES `mcp_roles` (`roles_id`),
  CONSTRAINT `mcp_users_roles_ibfk_2` FOREIGN KEY (`users_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_ARGUMENTS`
--

DROP TABLE IF EXISTS `MCP_VIEW_ARGUMENTS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_ARGUMENTS` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL COMMENT 'foreign key to argument in which one argument overrides the value of another, but not the type or context',
  `creators_id` bigint(20) unsigned NOT NULL COMMENT 'user who initally created argument',
  `displays_id` bigint(20) unsigned NOT NULL COMMENT 'foreign key to view argument belongs to',
  `system_name` varchar(128) NOT NULL COMMENT 'Name of argument',
  `human_name` varchar(128) NOT NULL COMMENT 'title/label of argument',
  `value` longtext NOT NULL COMMENT 'This may be a class name, function name, get ref, post ref, view ref, static value, etc based on the context. The context will determine how the value is handled within the application.',
  `type` enum('string','int','bool','float') DEFAULT NULL COMMENT 'The value type to cast the string to on the application end',
  `context` enum('static','post','get','request','global_arg','module_arg','dao','function','class','view','cfg') NOT NULL,
  `context_routine` varchar(128) DEFAULT NULL COMMENT 'The function or method name to call to derive the true value for dao, class and function contexts',
  `context_args` longtext COMMENT 'Serialized array of arguments to pass to a method or function call for dao, function and class context',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'For context other than static whether the argument is required to build the view',
  `removed` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Determines whether argument has been removed for a overriding view',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `displays_id` (`displays_id`,`system_name`,`deleted`),
  UNIQUE KEY `displays_id_2` (`displays_id`,`human_name`,`deleted`),
  KEY `displays_id_3` (`displays_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='Contains arguments that may be referenced via select options';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_DISPLAYS`
--

DROP TABLE IF EXISTS `MCP_VIEW_DISPLAYS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_DISPLAYS` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) DEFAULT NULL,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `creators_id` bigint(20) unsigned NOT NULL,
  `base` varchar(128) NOT NULL,
  `base_id` bigint(20) unsigned DEFAULT NULL,
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `opt_paginate` tinyint(3) unsigned DEFAULT NULL,
  `opt_rows_per_page` tinyint(3) unsigned DEFAULT NULL,
  `opt_theme_wrap` varchar(255) DEFAULT NULL,
  `opt_theme_row` varchar(255) DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_type` (`parent_id`,`system_name`,`deleted`),
  UNIQUE KEY `parent_type_2` (`parent_id`,`human_name`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_FIELDS`
--

DROP TABLE IF EXISTS `MCP_VIEW_FIELDS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_FIELDS` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `displays_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `sortable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `editable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `removed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displays_id` (`displays_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_FIELD_OPTIONS`
--

DROP TABLE IF EXISTS `MCP_VIEW_FIELD_OPTIONS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_FIELD_OPTIONS` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `fields_id` bigint(20) unsigned NOT NULL,
  `option_name` varchar(128) NOT NULL,
  `value_static` text,
  `value_argument_id` bigint(20) unsigned DEFAULT NULL,
  `value_field_id` bigint(20) unsigned DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fields_id` (`fields_id`,`option_name`,`deleted`),
  KEY `fields_id_2` (`fields_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_FILTERS`
--

DROP TABLE IF EXISTS `MCP_VIEW_FILTERS`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_FILTERS` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `displays_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `comparision` enum('=','<','>','<=','>=','like','between','fulltext','regex') NOT NULL,
  `conditional` enum('one','all','none') NOT NULL,
  `wildcard` enum('%s%','%s','s%') DEFAULT NULL,
  `regex` varchar(255) DEFAULT NULL,
  `removed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displays_id` (`displays_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_FILTER_VALUES`
--

DROP TABLE IF EXISTS `MCP_VIEW_FILTER_VALUES`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_FILTER_VALUES` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `filters_id` bigint(20) unsigned NOT NULL,
  `value_static` text,
  `value_argument_id` bigint(20) unsigned DEFAULT NULL,
  `value_field_id` bigint(20) unsigned DEFAULT NULL,
  `wildcard` enum('%s%','%s','s%') DEFAULT NULL,
  `regex` varchar(255) DEFAULT NULL,
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `filters_id` (`filters_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_SORTING`
--

DROP TABLE IF EXISTS `MCP_VIEW_SORTING`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_SORTING` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `displays_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `ordering` enum('asc','desc','rand') NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `removed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `displays_id` (`displays_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VIEW_SORTING_PRIORITY`
--

DROP TABLE IF EXISTS `MCP_VIEW_SORTING_PRIORITY`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VIEW_SORTING_PRIORITY` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `creators_id` bigint(20) unsigned NOT NULL,
  `sorting_id` bigint(20) unsigned NOT NULL,
  `value_static` text,
  `value_argument_id` bigint(20) unsigned DEFAULT NULL,
  `value_field_id` bigint(20) unsigned DEFAULT NULL,
  `weight` tinyint(4) NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sorting_id` (`sorting_id`,`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `MCP_VOCABULARY`
--

DROP TABLE IF EXISTS `MCP_VOCABULARY`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MCP_VOCABULARY` (
  `vocabulary_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sites_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '0 specifies vocabulary globally available such as states or something.',
  `creators_id` bigint(20) unsigned DEFAULT NULL,
  `pkg` varchar(128) NOT NULL DEFAULT '',
  `system_name` varchar(128) NOT NULL,
  `human_name` varchar(128) NOT NULL,
  `description` longtext,
  `weight` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `updated_on_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted_on_timestamp` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`vocabulary_id`),
  UNIQUE KEY `sites_id` (`sites_id`,`pkg`,`system_name`,`deleted`),
  UNIQUE KEY `sites_id_2` (`sites_id`,`pkg`,`human_name`,`deleted`),
  KEY `creators_id` (`creators_id`),
  KEY `sites_id_3` (`sites_id`),
  KEY `creators_id_2` (`creators_id`),
  CONSTRAINT `mcp_vocabulary_ibfk_1` FOREIGN KEY (`creators_id`) REFERENCES `mcp_users` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-01-20  0:41:46
