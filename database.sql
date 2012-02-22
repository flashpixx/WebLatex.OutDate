SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `directory`;
CREATE TABLE IF NOT EXISTS `directory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing directory structure' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `directory_document`;
CREATE TABLE IF NOT EXISTS `directory_document` (
  `document` bigint(20) unsigned NOT NULL,
  `directory` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`document`,`directory`),
  KEY `directory` (`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document-directory information';

DROP TABLE IF EXISTS `document`;
CREATE TABLE IF NOT EXISTS `document` (
  `did` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `uid` bigint(20) unsigned DEFAULT NULL,
  `draft` longtext COLLATE utf8_bin,
  `draftid` bigint(20) unsigned DEFAULT NULL,
  `canchange` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'true',
  `history` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'false',
  PRIMARY KEY (`did`),
  UNIQUE KEY `name` (`name`),
  KEY `uid` (`uid`),
  KEY `draftid` (`draftid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document header information' AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `documentpart`;
CREATE TABLE IF NOT EXISTS `documentpart` (
  `dpid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document` bigint(20) unsigned NOT NULL,
  `content` longtext COLLATE utf8_bin,
  `lastmodify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dpid`),
  KEY `document` (`document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document parts / chapter' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `document_rights`;
CREATE TABLE IF NOT EXISTS `document_rights` (
  `document` bigint(20) unsigned NOT NULL,
  `right` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`document`,`right`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing rights of the documents';

DROP TABLE IF EXISTS `domentpart_rights`;
CREATE TABLE IF NOT EXISTS `domentpart_rights` (
  `documentpartrt` bigint(20) unsigned NOT NULL,
  `right` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`documentpartrt`,`right`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `draft`;
CREATE TABLE IF NOT EXISTS `draft` (
  `did` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `user` bigint(20) unsigned DEFAULT NULL,
  `content` longtext COLLATE utf8_bin,
  PRIMARY KEY (`did`),
  UNIQUE KEY `name` (`name`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing documents drafts' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `draft_rights`;
CREATE TABLE IF NOT EXISTS `draft_rights` (
  `draft` bigint(20) unsigned NOT NULL,
  `right` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`draft`,`right`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing draft and right connection';

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `gid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin NOT NULL,
  `system` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'false',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing the user groups' AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `group_rights`;
CREATE TABLE IF NOT EXISTS `group_rights` (
  `group` bigint(20) unsigned NOT NULL,
  `right` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`group`,`right`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='tablefor storing group rights';

DROP TABLE IF EXISTS `media`;
CREATE TABLE IF NOT EXISTS `media` (
  `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `extension` varchar(32) COLLATE utf8_bin NOT NULL,
  `user` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`mid`),
  UNIQUE KEY `media` (`name`,`extension`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing information to the media' AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `media_documentpart`;
CREATE TABLE IF NOT EXISTS `media_documentpart` (
  `media` bigint(20) unsigned NOT NULL,
  `documentpart` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`media`,`documentpart`),
  KEY `documentpart` (`documentpart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table stores link between media and documentparts';

DROP TABLE IF EXISTS `rights`;
CREATE TABLE IF NOT EXISTS `rights` (
  `rid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin NOT NULL,
  `system` enum('true','false') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`rid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user / group rights' AUTO_INCREMENT=3 ;

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `hash` varchar(128) COLLATE utf8_bin NOT NULL,
  `loginenable` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'true',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user information' AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE IF NOT EXISTS `user_groups` (
  `user` bigint(20) unsigned NOT NULL,
  `groupid` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user`,`groupid`),
  KEY `groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing group-user connection';

DROP TABLE IF EXISTS `user_rights`;
CREATE TABLE IF NOT EXISTS `user_rights` (
  `user` bigint(20) unsigned NOT NULL,
  `right` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user`,`right`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user rights';


ALTER TABLE `directory`
  ADD CONSTRAINT `directory_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `directory_document`
  ADD CONSTRAINT `directory_document_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`did`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `directory_document_ibfk_2` FOREIGN KEY (`directory`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `document_ibfk_2` FOREIGN KEY (`draftid`) REFERENCES `draft` (`did`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `documentpart`
  ADD CONSTRAINT `documentpart_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`did`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `document_rights`
  ADD CONSTRAINT `document_rights_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`did`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_rights_ibfk_2` FOREIGN KEY (`right`) REFERENCES `rights` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `domentpart_rights`
  ADD CONSTRAINT `domentpart_rights_ibfk_1` FOREIGN KEY (`documentpartrt`) REFERENCES `documentpart` (`dpid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `domentpart_rights_ibfk_2` FOREIGN KEY (`right`) REFERENCES `rights` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `draft`
  ADD CONSTRAINT `draft_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `draft_rights`
  ADD CONSTRAINT `draft_rights_ibfk_1` FOREIGN KEY (`draft`) REFERENCES `draft` (`did`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `draft_rights_ibfk_2` FOREIGN KEY (`right`) REFERENCES `rights` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `group_rights`
  ADD CONSTRAINT `group_rights_ibfk_1` FOREIGN KEY (`group`) REFERENCES `groups` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_rights_ibfk_2` FOREIGN KEY (`right`) REFERENCES `rights` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `media_documentpart`
  ADD CONSTRAINT `media_documentpart_ibfk_1` FOREIGN KEY (`media`) REFERENCES `media` (`mid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `media_documentpart_ibfk_2` FOREIGN KEY (`documentpart`) REFERENCES `documentpart` (`dpid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_groups_ibfk_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`gid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user_rights`
  ADD CONSTRAINT `user_rights_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_rights_ibfk_2` FOREIGN KEY (`right`) REFERENCES `rights` (`rid`) ON DELETE CASCADE ON UPDATE CASCADE;
