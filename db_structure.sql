-- MySQL dump 10.13  Distrib 5.6.25, for Linux (x86_64)
--
-- Host: localhost    Database: my_little_wallpaper
-- ------------------------------------------------------
-- Server version	5.6.25

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
-- Table structure for table `ban`
--

DROP TABLE IF EXISTS `ban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ban` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `stopforumspam_entrycount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `urlname` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `footerdescription` text COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `urlname` (`urlname`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `click_log`
--

DROP TABLE IF EXISTS `click_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `click_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `fileid` (`file_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=866892 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `contact` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `feedback` text COLLATE utf8_swedish_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_attempt`
--

DROP TABLE IF EXISTS `login_attempt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempt` (
  `id` char(36) COLLATE utf8_swedish_ci NOT NULL,
  `username` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_loadtime`
--

DROP TABLE IF EXISTS `page_loadtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_loadtime` (
  `id` char(36) COLLATE utf8_swedish_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `debug` text COLLATE utf8_swedish_ci,
  `load_time` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_loadtime_avg`
--

DROP TABLE IF EXISTS `page_loadtime_avg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_loadtime_avg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `load_time` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `load_time_max` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=42967 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageview_stats`
--

DROP TABLE IF EXISTS `pageview_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pageview_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21484 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `serverloadstats`
--

DROP TABLE IF EXISTS `serverloadstats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serverloadstats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `avg1` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `avg5` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `avg15` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `users_online` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=258063 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `alternate` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `type` varchar(16) COLLATE utf8_swedish_ci NOT NULL,
  `description` text COLLATE utf8_swedish_ci NOT NULL,
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `alternate` (`alternate`),
  KEY `series` (`series`)
) ENGINE=MyISAM AUTO_INCREMENT=501 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_artist`
--

DROP TABLE IF EXISTS `tag_artist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_artist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `oldname` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=2562 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_aspect`
--

DROP TABLE IF EXISTS `tag_aspect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_aspect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_platform`
--

DROP TABLE IF EXISTS `tag_platform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_platform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_searchstats`
--

DROP TABLE IF EXISTS `tag_searchstats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_searchstats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `type` varchar(16) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `type` (`type`),
  KEY `tag` (`tag`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=711354 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `password` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `token` char(36) COLLATE utf8_swedish_ci DEFAULT NULL,
  `admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `banned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_api_requests`
--

DROP TABLE IF EXISTS `user_api_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_api_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestId` varchar(64) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `userId` int(10) NOT NULL DEFAULT '0',
  `time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueReq` (`requestId`,`userId`),
  KEY `user` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1378 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_forgotpass`
--

DROP TABLE IF EXISTS `user_forgotpass`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_forgotpass` (
  `id` char(36) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `keyhash` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hash` (`keyhash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_session`
--

DROP TABLE IF EXISTS `user_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_session` (
  `id` char(36) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `useragent` text COLLATE utf8_swedish_ci NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `ip` (`ip`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visit_log`
--

DROP TABLE IF EXISTS `visit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  `user_agent` text COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=5588558 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visits`
--

DROP TABLE IF EXISTS `visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper`
--

DROP TABLE IF EXISTS `wallpaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `filename` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `file` char(23) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `no_aspect` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `no_resolution` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mime` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `chartags` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `timeadded` int(10) unsigned NOT NULL DEFAULT '0',
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `favs` int(10) unsigned NOT NULL DEFAULT '0',
  `direct_with_link` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status_check` varchar(4) COLLATE utf8_swedish_ci NOT NULL DEFAULT '200',
  `last_checked` datetime NOT NULL,
  `delete_reason` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`file`),
  KEY `width` (`width`),
  KEY `height` (`height`),
  KEY `clicks` (`clicks`),
  KEY `timeadded` (`timeadded`),
  KEY `submitter` (`submitter_id`),
  KEY `chartags` (`chartags`),
  KEY `checked` (`last_checked`),
  KEY `series` (`series`)
) ENGINE=MyISAM AUTO_INCREMENT=9322 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_edit`
--

DROP TABLE IF EXISTS `wallpaper_edit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_edit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `author` text COLLATE utf8_swedish_ci NOT NULL,
  `tags` text COLLATE utf8_swedish_ci NOT NULL,
  `platform` text COLLATE utf8_swedish_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `reason` text COLLATE utf8_swedish_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `discarded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wallpaper` (`wallpaper_id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_fav`
--

DROP TABLE IF EXISTS `wallpaper_fav`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_fav` (
  `user_id` int(10) unsigned NOT NULL,
  `wallpaper_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`wallpaper_id`),
  KEY `user` (`user_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_history`
--

DROP TABLE IF EXISTS `wallpaper_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  `data_before` text COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wp` (`wallpaper_id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3208 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_submit`
--

DROP TABLE IF EXISTS `wallpaper_submit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_submit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `author` text COLLATE utf8_swedish_ci NOT NULL,
  `tags` text COLLATE utf8_swedish_ci NOT NULL,
  `aspect` text COLLATE utf8_swedish_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `filename` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `file` char(23) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `mime` varchar(128) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `timeadded` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `type` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `mobile_type` varchar(32) COLLATE utf8_swedish_ci NOT NULL,
  `discarded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`file`),
  KEY `timeadded` (`timeadded`),
  KEY `user` (`user_id`),
  KEY `series` (`series`)
) ENGINE=MyISAM AUTO_INCREMENT=796 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_submit_rejected`
--

DROP TABLE IF EXISTS `wallpaper_submit_rejected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_submit_rejected` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `url` varchar(255) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `reason` text COLLATE utf8_swedish_ci NOT NULL,
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `series` (`series`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag`
--

DROP TABLE IF EXISTS `wallpaper_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag` (`tag_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56331 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag_artist`
--

DROP TABLE IF EXISTS `wallpaper_tag_artist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag_artist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_artist_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_artist` (`tag_artist_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24300 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag_aspect`
--

DROP TABLE IF EXISTS `wallpaper_tag_aspect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag_aspect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_aspect_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_aspect` (`tag_aspect_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9293 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag_colour`
--

DROP TABLE IF EXISTS `wallpaper_tag_colour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag_colour` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_r` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_g` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_b` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_colour` char(6) COLLATE utf8_swedish_ci NOT NULL,
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` decimal(5,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wallpaper` (`wallpaper_id`),
  KEY `tag_colour` (`tag_colour`),
  KEY `amount` (`amount`)
) ENGINE=InnoDB AUTO_INCREMENT=85119 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag_colour_similar`
--

DROP TABLE IF EXISTS `wallpaper_tag_colour_similar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag_colour_similar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `colour` char(6) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  `similar_colour` char(6) COLLATE utf8_swedish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `colour` (`colour`),
  KEY `similar_colour` (`similar_colour`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wallpaper_tag_platform`
--

DROP TABLE IF EXISTS `wallpaper_tag_platform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallpaper_tag_platform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_platform_id` int(10) unsigned DEFAULT '0',
  `wallpaper_id` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_platform` (`tag_platform_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17211 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `wallpaper_tag_search`
--

DROP TABLE IF EXISTS `wallpaper_tag_search`;
/*!50001 DROP VIEW IF EXISTS `wallpaper_tag_search`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `wallpaper_tag_search` AS SELECT 
 1 AS `wallpaper_id`,
 1 AS `tag_id`,
 1 AS `tag_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `wallpaper_tag_search`
--

/*!50001 DROP VIEW IF EXISTS `wallpaper_tag_search`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `wallpaper_tag_search` AS (select `wallpaper_tag`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag`.`tag_id` AS `tag_id`,'tag' AS `tag_type` from `wallpaper_tag`) union (select `wallpaper_tag_artist`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_artist`.`tag_artist_id` AS `tag_id`,'tag_artist' AS `tag_type` from `wallpaper_tag_artist`) union (select `wallpaper_tag_aspect`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_aspect`.`tag_aspect_id` AS `tag_id`,'tag_aspect' AS `tag_type` from `wallpaper_tag_aspect`) union (select `wallpaper_tag_platform`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_platform`.`tag_platform_id` AS `tag_id`,'tag_platform' AS `tag_type` from `wallpaper_tag_platform`) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-02 19:56:31
