SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

DROP TABLE IF EXISTS `chat`;
CREATE TABLE IF NOT EXISTS `chat` (
  `from` bigint(20) unsigned NOT NULL,
  `session` varchar(255) COLLATE utf8_bin NOT NULL,
  `activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `docid` bigint(20) unsigned NOT NULL,
  `doctype` enum('draft','document') COLLATE utf8_bin NOT NULL,
  `message` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`session`,`activity`,`doctype`,`docid`),
  KEY `from` (`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing the chat data';

DROP TABLE IF EXISTS `directory`;
CREATE TABLE IF NOT EXISTS `directory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `owner` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`parent`,`name`),
  KEY `parent` (`parent`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing directory structure';

DROP TABLE IF EXISTS `directory_document`;
CREATE TABLE IF NOT EXISTS `directory_document` (
  `document` bigint(20) unsigned NOT NULL,
  `directory` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`document`,`directory`),
  KEY `directory` (`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document-directory information';

DROP TABLE IF EXISTS `directory_draft`;
CREATE TABLE IF NOT EXISTS `directory_draft` (
  `draft` bigint(20) unsigned NOT NULL,
  `directory` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`draft`,`directory`),
  KEY `directory` (`directory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing draft-directory information';

DROP TABLE IF EXISTS `directory_rights`;
CREATE TABLE IF NOT EXISTS `directory_rights` (
  `directory` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`directory`,`rights`),
  KEY `right` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing directory-right information';

DROP TABLE IF EXISTS `document`;
CREATE TABLE IF NOT EXISTS `document` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `owner` bigint(20) unsigned DEFAULT NULL,
  `latexmk` longtext COLLATE utf8_bin,
  `draft` longtext COLLATE utf8_bin,
  `draftid` bigint(20) unsigned DEFAULT NULL,
  `modifiable` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'true',
  `archivable` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'false',
  PRIMARY KEY (`id`),
  KEY `uid` (`owner`),
  KEY `draftid` (`draftid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document header information';

DROP TABLE IF EXISTS `documentpart`;
CREATE TABLE IF NOT EXISTS `documentpart` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `document` bigint(20) unsigned NOT NULL,
  `description` varchar(250) COLLATE utf8_bin DEFAULT NULL,
  `position` bigint(20) unsigned DEFAULT NULL,
  `content` longtext COLLATE utf8_bin,
  `lastmodify` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `document` (`document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing document parts / chapter';

DROP TABLE IF EXISTS `documentpart_rights`;
CREATE TABLE IF NOT EXISTS `documentpart_rights` (
  `documentpart` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`documentpart`,`rights`),
  KEY `rights` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `document_rights`;
CREATE TABLE IF NOT EXISTS `document_rights` (
  `document` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`document`,`rights`),
  KEY `right` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing rights of the documents';

DROP TABLE IF EXISTS `draft`;
CREATE TABLE IF NOT EXISTS `draft` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `owner` bigint(20) unsigned DEFAULT NULL,
  `archivable` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'false',
  `content` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `user` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing documents drafts';

DROP TABLE IF EXISTS `draft_history`;
CREATE TABLE IF NOT EXISTS `draft_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `draftid` bigint(20) unsigned NOT NULL,
  `backuptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `draftid` (`draftid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing draft history';

DROP TABLE IF EXISTS `draft_lock`;
CREATE TABLE IF NOT EXISTS `draft_lock` (
  `draft` bigint(20) unsigned NOT NULL,
  `user` bigint(20) unsigned NOT NULL,
  `session` varchar(255) COLLATE utf8_bin NOT NULL,
  `lastactivity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`draft`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing the draft locks';

DROP TABLE IF EXISTS `draft_rights`;
CREATE TABLE IF NOT EXISTS `draft_rights` (
  `draft` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`draft`,`rights`),
  KEY `right` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing draft and right connection';

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin NOT NULL,
  `owner` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing the user groups';

DROP TABLE IF EXISTS `group_rights`;
CREATE TABLE IF NOT EXISTS `group_rights` (
  `group` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`group`,`rights`),
  KEY `right` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='tablefor storing group rights';

DROP TABLE IF EXISTS `media`;
CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `extension` varchar(32) COLLATE utf8_bin NOT NULL,
  `user` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media` (`name`,`extension`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing information to the media';

DROP TABLE IF EXISTS `media_documentpart`;
CREATE TABLE IF NOT EXISTS `media_documentpart` (
  `media` bigint(20) unsigned NOT NULL,
  `documentpart` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`media`,`documentpart`),
  KEY `documentpart` (`documentpart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table stores link between media and documentparts';

DROP TABLE IF EXISTS `media_rights`;
CREATE TABLE IF NOT EXISTS `media_rights` (
  `media` bigint(20) unsigned NOT NULL,
  `rights` bigint(20) unsigned NOT NULL,
  `access` enum('read','write') COLLATE utf8_bin NOT NULL DEFAULT 'read',
  PRIMARY KEY (`media`,`rights`),
  KEY `rights` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing media right connection';

DROP TABLE IF EXISTS `rights`;
CREATE TABLE IF NOT EXISTS `rights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_bin NOT NULL,
  `owner` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user / group rights';

DROP TABLE IF EXISTS `substitution`;
CREATE TABLE IF NOT EXISTS `substitution` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner` bigint(20) unsigned DEFAULT NULL,
  `command` varchar(64) COLLATE utf8_bin NOT NULL,
  `data` longtext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for setting newcommands';

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `hash` varchar(128) COLLATE utf8_bin NOT NULL,
  `loginenable` enum('true','false') COLLATE utf8_bin NOT NULL DEFAULT 'true',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user information';

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
  `rights` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user`,`rights`),
  KEY `right` (`rights`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='table for storing user rights';


ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`from`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `directory`
  ADD CONSTRAINT `directory_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `directory_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `directory_document`
  ADD CONSTRAINT `directory_document_ibfk_1` FOREIGN KEY (`directory`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `directory_document_ibfk_2` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `directory_draft`
  ADD CONSTRAINT `directory_draft_ibfk_1` FOREIGN KEY (`draft`) REFERENCES `draft` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `directory_draft_ibfk_2` FOREIGN KEY (`directory`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `directory_rights`
  ADD CONSTRAINT `directory_rights_ibfk_1` FOREIGN KEY (`directory`) REFERENCES `directory` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `directory_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `documentpart`
  ADD CONSTRAINT `documentpart_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `documentpart_rights`
  ADD CONSTRAINT `documentpart_rights_ibfk_1` FOREIGN KEY (`documentpart`) REFERENCES `documentpart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documentpart_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `document_rights`
  ADD CONSTRAINT `document_rights_ibfk_1` FOREIGN KEY (`document`) REFERENCES `document` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `document_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `draft`
  ADD CONSTRAINT `draft_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `draft_history`
  ADD CONSTRAINT `draft_history_ibfk_1` FOREIGN KEY (`draftid`) REFERENCES `draft` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `draft_lock`
  ADD CONSTRAINT `draft_lock_ibfk_1` FOREIGN KEY (`draft`) REFERENCES `draft` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `draft_lock_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `draft_rights`
  ADD CONSTRAINT `draft_rights_ibfk_1` FOREIGN KEY (`draft`) REFERENCES `draft` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `draft_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`);

ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `group_rights`
  ADD CONSTRAINT `group_rights_ibfk_1` FOREIGN KEY (`group`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `media_documentpart`
  ADD CONSTRAINT `media_documentpart_ibfk_1` FOREIGN KEY (`media`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `media_documentpart_ibfk_2` FOREIGN KEY (`documentpart`) REFERENCES `documentpart` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `media_rights`
  ADD CONSTRAINT `media_rights_ibfk_1` FOREIGN KEY (`media`) REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `media_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `rights`
  ADD CONSTRAINT `rights_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `substitution`
  ADD CONSTRAINT `substitution_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `user_groups`
  ADD CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_groups_ibfk_2` FOREIGN KEY (`groupid`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user_rights`
  ADD CONSTRAINT `user_rights_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_rights_ibfk_2` FOREIGN KEY (`rights`) REFERENCES `rights` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
