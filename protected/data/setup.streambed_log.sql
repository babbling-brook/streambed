
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `action_log` (
  `action_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'The id of the user that called the action.',
  `controller_thing_id` int(10) unsigned NOT NULL COMMENT 'The id of the controller in the thing table.',
  `action_thing_id` int(10) unsigned NOT NULL COMMENT 'The id of the action in the thing table.',
  `response_size` int(10) unsigned NOT NULL COMMENT 'The number of characters in the repsonse.',
  `response_time` int(10) unsigned NOT NULL COMMENT 'How long the controller spent on theis action - in milliseconds.',
  `subdomain_id` int(10) unsigned NOT NULL COMMENT 'The id of the sub domain that this action took place in.',
  PRIMARY KEY (`action_log_id`),
  KEY `date_created` (`date_created`),
  KEY `user_id` (`user_id`),
  KEY `controller_thing_id` (`controller_thing_id`),
  KEY `action_thing_id` (`action_thing_id`),
  KEY `subdomain_id` (`subdomain_id`),
  KEY `response_size` (`response_size`),
  KEY `response_time` (`response_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='A log of actions a user has called';

CREATE TABLE `longest_action_time_aggregate` (
	`total_time` DECIMAL(32,0) NULL,
	`count` BIGINT(21) NOT NULL,
	`public` INT(1) NOT NULL,
	`controller` VARCHAR(100) NOT NULL COMMENT 'The content that is stored in this thing' COLLATE 'utf8_unicode_ci',
	`action` VARCHAR(100) NOT NULL COMMENT 'The content that is stored in this thing' COLLATE 'utf8_unicode_ci'
) ENGINE=MyISAM;

CREATE TABLE `most_popular_actions` (
	`count` BIGINT(21) NOT NULL,
	`public` INT(1) NOT NULL,
	`controller` VARCHAR(100) NOT NULL COMMENT 'The content that is stored in this thing' COLLATE 'utf8_unicode_ci',
	`action` VARCHAR(100) NOT NULL COMMENT 'The content that is stored in this thing' COLLATE 'utf8_unicode_ci'
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `param_log` (
  `param_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action_log_id` int(10) unsigned NOT NULL COMMENT 'The id of the action that these paramaters where used in.',
  `name_thing_id` int(10) unsigned NOT NULL COMMENT 'The id of the name of this parameter in the thing table.',
  `content_thing_id` int(10) unsigned NOT NULL COMMENT 'The id of the content of this parameter in the thing table.',
  PRIMARY KEY (`param_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The paramaters that were used in an action';

CREATE TABLE IF NOT EXISTS `subdomain` (
  `subdomain_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subdomain` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The subdomain',
  PRIMARY KEY (`subdomain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of subdomains that are logged.';

CREATE TABLE IF NOT EXISTS `thing` (
  `thing_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The content that is stored in this thing',
  PRIMARY KEY (`thing_id`),
  UNIQUE KEY `name` (`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The names of things that are being logged.\r\nIncludes action names, paramater names, paramater contents\r\nIf the content is too long, then it will be truncated.';

DROP TABLE IF EXISTS `longest_action_time_aggregate`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` VIEW `longest_action_time_aggregate` AS SELECT
	SUM(action_log.response_time) AS total_time
	,COUNT(action_log.action_log_id) AS count
	,ISNULL(action_log.user_id) AS public
	,controller_thing.content AS controller
	,action_thing.content AS action
FROM
	action_log
	INNER JOIN thing AS action_thing ON action_log.action_thing_id = action_thing.thing_id
	INNER JOIN thing AS controller_thing ON action_log.controller_thing_id = controller_thing.thing_id
GROUP BY
    public
	,controller_thing.content
	,action_thing.content
ORDER BY total_time DESC, count DESC ;

DROP TABLE IF EXISTS `most_popular_actions`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` VIEW `most_popular_actions` AS SELECT
	 COUNT(action_log.action_log_id) AS count
	,ISNULL(action_log.user_id) AS public
	,controller_thing.content AS controller
	,action_thing.content AS action
FROM
	action_log
	INNER JOIN thing AS action_thing ON action_log.action_thing_id = action_thing.thing_id
	INNER JOIN thing AS controller_thing ON action_log.controller_thing_id = controller_thing.thing_id
GROUP BY
    public
	,controller_thing.content
	,action_thing.content
ORDER BY count DESC ;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
