SET NAMES utf8mb4;

--
-- Table structure for table `ban`
--

DROP TABLE IF EXISTS `ban`;
CREATE TABLE `ban` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `stopforumspam_entrycount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `urlname` varchar(64) NOT NULL,
  `footerdescription` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `urlname` (`urlname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `click_log`
--

DROP TABLE IF EXISTS `click_log`;
CREATE TABLE `click_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(128) NOT NULL DEFAULT '',
  `file_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `fileid` (`file_id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `feedback` text NOT NULL,
  `ip` varchar(255) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `login_attempt`
--

DROP TABLE IF EXISTS `login_attempt`;
CREATE TABLE `login_attempt` (
  `id` char(36) NOT NULL,
  `username` varchar(128) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `page_loadtime`
--

DROP TABLE IF EXISTS `page_loadtime`;
CREATE TABLE `page_loadtime` (
  `id` char(36) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `debug` text,
  `load_time` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `page_loadtime_avg`
--

DROP TABLE IF EXISTS `page_loadtime_avg`;
CREATE TABLE `page_loadtime_avg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `load_time` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `load_time_max` decimal(7,4) unsigned NOT NULL DEFAULT '0.0000',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `pageview_stats`
--

DROP TABLE IF EXISTS `pageview_stats`;
CREATE TABLE `pageview_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `serverloadstats`
--

DROP TABLE IF EXISTS `serverloadstats`;
CREATE TABLE `serverloadstats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `avg1` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `avg5` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `avg15` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `users_online` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alternate` varchar(64) NOT NULL,
  `type` varchar(16) NOT NULL,
  `description` text NOT NULL,
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `type` (`type`),
  KEY `alternate` (`alternate`),
  KEY `series` (`series`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `tag_artist`
--

DROP TABLE IF EXISTS `tag_artist`;
CREATE TABLE `tag_artist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `oldname` varchar(64) NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `tag_aspect`
--

DROP TABLE IF EXISTS `tag_aspect`;
CREATE TABLE `tag_aspect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `tag_platform`
--

DROP TABLE IF EXISTS `tag_platform`;
CREATE TABLE `tag_platform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `tag_searchstats`
--

DROP TABLE IF EXISTS `tag_searchstats`;
CREATE TABLE `tag_searchstats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(128) NOT NULL DEFAULT '',
  `type` varchar(16) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `type` (`type`),
  KEY `tag` (`tag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `token` char(36) DEFAULT NULL,
  `admin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `banned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `user_api_requests`
--

DROP TABLE IF EXISTS `user_api_requests`;
CREATE TABLE `user_api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requestId` varchar(64) NOT NULL DEFAULT '',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueReq` (`requestId`,`userId`),
  KEY `user` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `user_forgotpass`
--

DROP TABLE IF EXISTS `user_forgotpass`;
CREATE TABLE `user_forgotpass` (
  `id` char(36) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `keyhash` varchar(128) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hash` (`keyhash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `user_session`
--

DROP TABLE IF EXISTS `user_session`;
CREATE TABLE `user_session` (
  `id` char(36) NOT NULL DEFAULT '',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(250) NOT NULL,
  `useragent` text NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `ip` (`ip`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `visit_log`
--

DROP TABLE IF EXISTS `visit_log`;
CREATE TABLE `visit_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  `user_agent` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `visits`
--

DROP TABLE IF EXISTS `visits`;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper`
--

DROP TABLE IF EXISTS `wallpaper`;
CREATE TABLE `wallpaper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submitter_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `file` char(23) NOT NULL DEFAULT '',
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `no_aspect` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `no_resolution` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mime` varchar(128) NOT NULL DEFAULT '',
  `chartags` varchar(128) NOT NULL DEFAULT '',
  `timeadded` int(10) unsigned NOT NULL DEFAULT '0',
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `favs` int(10) unsigned NOT NULL DEFAULT '0',
  `direct_with_link` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status_check` varchar(4) NOT NULL DEFAULT '200',
  `last_checked` datetime NOT NULL,
  `delete_reason` varchar(64) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_edit`
--

DROP TABLE IF EXISTS `wallpaper_edit`;
CREATE TABLE `wallpaper_edit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `author` text NOT NULL,
  `tags` text NOT NULL,
  `platform` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `discarded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `wallpaper` (`wallpaper_id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_fav`
--

DROP TABLE IF EXISTS `wallpaper_fav`;
CREATE TABLE `wallpaper_fav` (
  `user_id` int(10) unsigned NOT NULL,
  `wallpaper_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`wallpaper_id`),
  KEY `user` (`user_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_history`
--

DROP TABLE IF EXISTS `wallpaper_history`;
CREATE TABLE `wallpaper_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  `data_before` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wp` (`wallpaper_id`),
  KEY `user` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_submit`
--

DROP TABLE IF EXISTS `wallpaper_submit`;
CREATE TABLE `wallpaper_submit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `author` text NOT NULL,
  `tags` text NOT NULL,
  `aspect` text NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `file` char(23) NOT NULL DEFAULT '',
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `mime` varchar(128) NOT NULL DEFAULT '',
  `timeadded` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL,
  `mobile_type` varchar(32) NOT NULL,
  `discarded` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`file`),
  KEY `timeadded` (`timeadded`),
  KEY `user` (`user_id`),
  KEY `series` (`series`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_submit_rejected`
--

DROP TABLE IF EXISTS `wallpaper_submit_rejected`;
CREATE TABLE `wallpaper_submit_rejected` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `reason` text NOT NULL,
  `width` int(5) unsigned NOT NULL DEFAULT '0',
  `height` int(5) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `series` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `series` (`series`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_tag`
--

DROP TABLE IF EXISTS `wallpaper_tag`;
CREATE TABLE `wallpaper_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag` (`tag_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_tag_artist`
--

DROP TABLE IF EXISTS `wallpaper_tag_artist`;
CREATE TABLE `wallpaper_tag_artist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_artist_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_artist` (`tag_artist_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_tag_aspect`
--

DROP TABLE IF EXISTS `wallpaper_tag_aspect`;
CREATE TABLE `wallpaper_tag_aspect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_aspect_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_aspect` (`tag_aspect_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
INSERT INTO tag_aspect (name) VALUES ('16:9');
INSERT INTO tag_aspect (name) VALUES ('16:10');
INSERT INTO tag_aspect (name) VALUES ('4:3');
INSERT INTO tag_aspect (name) VALUES ('21:9');
INSERT INTO tag_aspect (name) VALUES ('32:9');

--
-- Table structure for table `wallpaper_tag_colour`
--

DROP TABLE IF EXISTS `wallpaper_tag_colour`;
CREATE TABLE `wallpaper_tag_colour` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_r` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_g` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_b` int(3) unsigned NOT NULL DEFAULT '0',
  `tag_colour` char(6) NOT NULL,
  `wallpaper_id` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` decimal(5,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wallpaper` (`wallpaper_id`),
  KEY `tag_colour` (`tag_colour`),
  KEY `amount` (`amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_tag_colour_similar`
--

DROP TABLE IF EXISTS `wallpaper_tag_colour_similar`;
CREATE TABLE `wallpaper_tag_colour_similar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `colour` char(6) NOT NULL DEFAULT '',
  `similar_colour` char(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `colour` (`colour`),
  KEY `similar_colour` (`similar_colour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;

--
-- Table structure for table `wallpaper_tag_platform`
--

DROP TABLE IF EXISTS `wallpaper_tag_platform`;
CREATE TABLE `wallpaper_tag_platform` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_platform_id` int(10) unsigned DEFAULT '0',
  `wallpaper_id` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_platform` (`tag_platform_id`),
  KEY `wallpaper` (`wallpaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci;
INSERT INTO tag_platform (name) VALUES ('Desktop');
INSERT INTO tag_platform (name) VALUES ('Mobile');
INSERT INTO tag_platform (name) VALUES ('Android Live Wallpaper');
INSERT INTO tag_platform (name) VALUES ('iPhone');
INSERT INTO tag_platform (name) VALUES ('Android');
INSERT INTO tag_platform (name) VALUES ('Windows Phone');
INSERT INTO tag_platform (name) VALUES ('Android Theme');
INSERT INTO tag_platform (name) VALUES ('iPad');
INSERT INTO tag_platform (name) VALUES ('Dual screen');

--
-- Final view structure for view `wallpaper_tag_search`
--

DROP VIEW IF EXISTS `wallpaper_tag_search`;
CREATE VIEW `wallpaper_tag_search` AS (
  select `wallpaper_tag`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag`.`tag_id` AS `tag_id`,'tag' AS `tag_type` from `wallpaper_tag` 
) union (
  select `wallpaper_tag_artist`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_artist`.`tag_artist_id` AS `tag_id`,'tag_artist' AS `tag_type` from `wallpaper_tag_artist`
) union (
  select `wallpaper_tag_aspect`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_aspect`.`tag_aspect_id` AS `tag_id`,'tag_aspect' AS `tag_type` from `wallpaper_tag_aspect`
) union (
  select `wallpaper_tag_platform`.`wallpaper_id` AS `wallpaper_id`,`wallpaper_tag_platform`.`tag_platform_id` AS `tag_id`,'tag_platform' AS `tag_type` from `wallpaper_tag_platform`
);
