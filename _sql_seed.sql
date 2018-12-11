-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 07, 2014 at 03:44 AM
-- Server version: 5.1.53
-- PHP Version: 5.3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `image_info`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `all_tags`
--
CREATE TABLE IF NOT EXISTS `all_tags` (
`tag` varchar(32)
);
-- --------------------------------------------------------

--
-- Table structure for table `dir_lib`
--

CREATE TABLE IF NOT EXISTS `dir_lib` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(256) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `group_names`
--

CREATE TABLE IF NOT EXISTS `group_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_group` varchar(54) NOT NULL,
  `name_description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `filename` varchar(256) NOT NULL,
  `file_type` varchar(4) NOT NULL,
  `display_name` varchar(128) DEFAULT NULL,
  `finger` binary(16) DEFAULT NULL,
  `thumb_dir` int(11) NOT NULL,
  `main_dir` int(11) NOT NULL,
  `img_group` int(11) DEFAULT NULL,
  `favorite` tinyint(1) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `size_hash` binary(16) NOT NULL,
  `size_bytes` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;


-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;


-- --------------------------------------------------------

--
-- Stand-in structure for view `tagsearch`
--
CREATE TABLE IF NOT EXISTS `tagsearch` (
`tag` varchar(32)
,`id` int(11)
,`level` bigint(20)
);
-- --------------------------------------------------------

--
-- Table structure for table `tags_1`
--

CREATE TABLE IF NOT EXISTS `tags_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;


-- --------------------------------------------------------

--
-- Table structure for table `tags_2`
--

CREATE TABLE IF NOT EXISTS `tags_2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;


-- --------------------------------------------------------

--
-- Table structure for table `tags_3`
--

CREATE TABLE IF NOT EXISTS `tags_3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags_4`
--

CREATE TABLE IF NOT EXISTS `tags_4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


-- --------------------------------------------------------

--
-- Table structure for table `tag_link`
--

CREATE TABLE IF NOT EXISTS `tag_link` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `linkimage` bigint(20) unsigned NOT NULL,
  `linktag` int(11) NOT NULL,
  `taglevel` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;


-- --------------------------------------------------------

--
-- Structure for view `all_tags`
--
DROP TABLE IF EXISTS `all_tags`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `all_tags` AS select `tags`.`tag` AS `tag` from `tags` union select `tags_1`.`tag` AS `tag` from `tags_1` union select `tags_2`.`tag` AS `tag` from `tags_2` union select `tags_3`.`tag` AS `tag` from `tags_3`;

-- --------------------------------------------------------

--
-- Structure for view `tagsearch`
--
DROP TABLE IF EXISTS `tagsearch`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `tagsearch` AS select distinct `tags_1`.`tag` AS `tag`,`tags_1`.`id` AS `id`,1 AS `level` from `tags_1` union select distinct `tags_2`.`tag` AS `tag`,`tags_2`.`id` AS `id`,2 AS `level` from `tags_2` union select distinct `tags_3`.`tag` AS `tag`,`tags_3`.`id` AS `id`,3 AS `level` from `tags_3`;
