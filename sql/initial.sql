-- phpMyAdmin SQL Dump
-- version 4.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 07, 2013 at 11:25 PM
-- Server version: 5.5.20
-- PHP Version: 5.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `aperire`
--

-- --------------------------------------------------------

--
-- Table structure for table `apr_projects`
--

CREATE TABLE IF NOT EXISTS `apr_projects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `cdate` date NOT NULL,
  `udate` date NOT NULL,
  `description` varchar(1024) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `apr_relations`
--
CREATE TABLE IF NOT EXISTS `apr_relations` (
`tool_id` int(10) unsigned
,`tool_id_rel` int(10) unsigned
,`rel_kind` int(10) unsigned
,`value` int(11)
);
-- --------------------------------------------------------

--
-- Table structure for table `apr_tools`
--

CREATE TABLE IF NOT EXISTS `apr_tools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `cdate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `apr_tool_relations`
--

CREATE TABLE IF NOT EXISTS `apr_tool_relations` (
  `tool_id` int(10) unsigned NOT NULL,
  `tool_id_rel` int(10) unsigned NOT NULL,
  `rel_kind` int(10) unsigned NOT NULL,
  `value` int(11) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `cdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `apr_users`
--

CREATE TABLE IF NOT EXISTS `apr_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_czech_ci DEFAULT NULL,
  `real_name` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Structure for view `apr_effective`
--
DROP TABLE IF EXISTS `apr_effective`;

CREATE VIEW `apr_effective` AS select `r`.`tool_id` AS `tool_id`,`r`.`tool_id_rel` AS `tool_id_rel`,`r`.`rel_kind` AS `rel_kind`,`r`.`value` AS `value` from `apr_tool_relations` `r` where (`r`.`rel_kind` in (0,1)) order by `r`.`tool_id`;

-- --------------------------------------------------------

--
-- Structure for view `apr_relations`
--
DROP TABLE IF EXISTS `apr_relations`;

CREATE VIEW `apr_relations` AS select `r`.`tool_id` AS `tool_id`,`r`.`tool_id_rel` AS `tool_id_rel`,`r`.`rel_kind` AS `rel_kind`,`r`.`value` AS `value` from `apr_tool_relations` `r` where (`r`.`rel_kind` = 2);

-- --------------------------------------------------------

--
-- Structure for view `apr_view_rel1`
--
DROP TABLE IF EXISTS `apr_view_rel1`;

CREATE VIEW `apr_view_rel1` AS select `r`.`tool_id` AS `tool_id`,`r`.`tool_id_rel` AS `tool_id_rel`,`r`.`rel_kind` AS `rel_kind`,`r`.`value` AS `value` from `apr_tool_relations` `r` where ((`r`.`rel_kind` = 2) and (`r`.`value` in (1,2,3))) group by `r`.`tool_id`,`r`.`tool_id_rel`,`r`.`rel_kind`;
