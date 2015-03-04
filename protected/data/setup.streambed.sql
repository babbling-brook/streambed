/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


CREATE PROCEDURE `generic_tree`(IN `edge_table` CHAR(64), IN `edge_id_col` CHAR(64), IN `edge_parent_id_col` CHAR(64), IN `ancestor_id` INT
)
BEGIN
  DECLARE r INT DEFAULT 0;
  DROP TABLE IF EXISTS _subtree;
  SET @sql = Concat( 'CREATE TABLE _subtree ENGINE=MyISAM SELECT ',
                     edge_id_col,' AS child_id, ',
                     edge_parent_id_col, ' AS parent_id,',
                     '0 AS level FROM ',
                     edge_table, ' WHERE ', edge_parent_id_col, '=', ancestor_id );
  PREPARE stmt FROM @sql;
  EXECUTE stmt;
  DROP PREPARE stmt;
  ALTER TABLE _subtree ADD PRIMARY KEY(child_id,parent_id);
  REPEAT
    SET @sql = Concat( 'INSERT IGNORE INTO _subtree SELECT DISTINCT a.', edge_id_col,
                       ',a.',edge_parent_id_col, ',b.level+1 FROM ',
                       edge_table, ' AS a JOIN _subtree AS b ON a.',edge_parent_id_col, '=b.child_id' );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    SET r=Row_Count();  -- save row_count() result before DROP PREPARE loses the value
    DROP PREPARE stmt;
  UNTIL r < 1 END REPEAT;
END;

CREATE TABLE IF NOT EXISTS `invitation` (
  `invitation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int(10) unsigned NOT NULL,
  `to_user_id` int(10) unsigned NOT NULL,
  `ring_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL COMMENT 'see lookup table',
  PRIMARY KEY (`invitation_id`),
  KEY `from_user_id` (`from_user_id`),
  KEY `to_user_id` (`to_user_id`),
  KEY `ring_id` (`ring_id`),
  KEY `type` (`type`),
  CONSTRAINT `FK_invitation_from_user` FOREIGN KEY (`from_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_invitation_lookup` FOREIGN KEY (`type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_invitation_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_invitation_to_user` FOREIGN KEY (`to_user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='stores invitations from users';

CREATE TABLE IF NOT EXISTS `js_error` (
  `js_error_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `location` int(10) unsigned NOT NULL COMMENT 'see lookup table for options',
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'see js_error_codes table for options',
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`js_error_id`),
  KEY `location` (`location`),
  KEY `type` (`type`),
  KEY `create_date` (`create_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Log of javascript errors.';

CREATE TABLE IF NOT EXISTS `js_error_code` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A js error code.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'The description for this error code.',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='A list of the types of error codes reported from javascript.';

CREATE TABLE IF NOT EXISTS `kindred` (
  `kindred_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key.',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user whose kindred are indexed.',
  `kindred_user_id` int(10) unsigned NOT NULL COMMENT 'The id of the kindred user.',
  `score` int(10) NOT NULL COMMENT 'The total score of this kindred relaitonship.',
  `user_rhythm_id` int(10) unsigned NOT NULL COMMENT 'The rhythm that generated this score. Links to user_rhythm becaus it may be a partial version.',
  PRIMARY KEY (`kindred_id`),
  KEY `user_id` (`user_id`),
  KEY `rhythm_extra_id` (`user_rhythm_id`),
  KEY `kindred_user_id` (`kindred_user_id`),
  CONSTRAINT `FK_kindred_kindred_user` FOREIGN KEY (`kindred_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_kindred_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_kindred_user_rhythm` FOREIGN KEY (`user_rhythm_id`) REFERENCES `user_rhythm` (`user_rhythm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Records all the kindred scores for  users';

CREATE TABLE IF NOT EXISTS `login` (
  `login_id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `domain` text COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `return_location` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `secret` (`secret`),
  KEY `create_date` (`create_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores sessions data on the user before redirecting them to their data store to enter their password. Data is temporary.';

CREATE TABLE IF NOT EXISTS `lookup` (
  `lookup_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `column_name` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Column name for lookup in format table.column, if table is missing then many tables are using it',
  `value` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A valid value for this column',
  `description` text COLLATE utf8_unicode_ci,
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT 'Sort order for the values in the defined column',
  PRIMARY KEY (`lookup_id`),
  UNIQUE KEY `lookup_id` (`lookup_id`,`column_name`),
  KEY `column` (`column_name`,`value`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='generic lookup table for valid column values';



CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique identity for all posts stored here. It is the site_post_id for posts stored on this site',
  `site_id` int(10) unsigned NOT NULL COMMENT 'The site that owns this post',
  `site_post_id` int(10) unsigned DEFAULT NULL COMMENT 'The post_id on the site that owns this post - which is the domain of the stream that it is in. For new posts in the site, this is initially set to NULL and then imediatly updated to reflect the post_id',
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'The stream that the post is placed in.',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user making the post',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent` int(10) unsigned DEFAULT NULL COMMENT 'If this post has a parent post_id, list it here. This is the post_id of the parent post in the domain of the stream that owns this post: If this is a differnet domain then it will connect to site_post_id.',
  `top_parent` int(10) unsigned DEFAULT NULL COMMENT 'If this post has a top parent post_id, list it here.  This is the post_id of the top parent post in the domain of the stream that owns this post: If this is a differnet domain then it will connect to site_post_id.',
  `block` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Which stream block number does this post belong to. 0 = undefined',
  `block_tree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Which tree block number does this post belong to. 0 = undefined',
  `status` int(10) unsigned NOT NULL DEFAULT '143' COMMENT 'What is the permission status of this post. See lookup table for valid values.',
  `child_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'A count of all public child posts.',
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `site_index` (`site_id`,`site_post_id`),
  KEY `post_master_id` (`stream_extra_id`),
  KEY `date_created` (`date_created`),
  KEY `block` (`block`),
  KEY `top_parent` (`top_parent`),
  KEY `block_tree` (`block_tree`),
  KEY `FK_parent_post` (`parent`),
  KEY `FK_post_user` (`user_id`),
  KEY `FK_post_lookup` (`status`),
  CONSTRAINT `FK_parent_post` FOREIGN KEY (`parent`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_post_lookup` FOREIGN KEY (`status`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_post_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_post_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_post_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_top_parent_post` FOREIGN KEY (`top_parent`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contains all revisions of posts by users';



CREATE TABLE IF NOT EXISTS `post_content` (
  `post_content_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_id` int(10) unsigned NOT NULL,
  `revision` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Highest revision is current',
  `display_order` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The display order of this field',
  `text` text COLLATE utf8_unicode_ci COMMENT 'The text in a text field',
  `link` text COLLATE utf8_unicode_ci COMMENT 'The link in a link field',
  `link_thumbnail_url` text COLLATE utf8_unicode_ci COMMENT 'The original url for a links thumbnail. ''data'' If the original image was embded as a data string rather than an url.',
  `link_title` text COLLATE utf8_unicode_ci COMMENT 'The link_title in a link field',
  `link_content` text COLLATE utf8_unicode_ci COMMENT 'The link to the original image used to generate a thumbnail in link fields',
  `checked` tinyint(1) DEFAULT NULL COMMENT 'A checkbox value',
  `selected` text COLLATE utf8_unicode_ci COMMENT 'An array of comma sperated selected values for a list',
  `value_max` int(10) DEFAULT NULL COMMENT 'The maximum value of this post if it is set on the post',
  `value_min` int(10) DEFAULT NULL COMMENT 'The minimum value of this post if it is set on the post',
  PRIMARY KEY (`post_content_id`),
  UNIQUE KEY `display_order` (`display_order`,`revision`,`post_id`),
  KEY `post_id` (`post_id`,`revision`),
  FULLTEXT KEY `text` (`text`),
  CONSTRAINT `FK_post_content_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Contains a JSON object detailing the post';



CREATE TABLE IF NOT EXISTS `post_descendent` (
  `post_descendent_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ancestor_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `descendent_post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_descendent_id`),
  UNIQUE KEY `integrity_id` (`ancestor_post_id`,`descendent_post_id`),
  KEY `parent_post_id` (`ancestor_post_id`),
  KEY `FK_post_descendent_descendent_post` (`descendent_post_id`),
  CONSTRAINT `FK_post_descendent_ancestor_post` FOREIGN KEY (`ancestor_post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_post_descendent_descendent_post` FOREIGN KEY (`descendent_post_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Indexes the decendents of all all nodes';


CREATE TABLE IF NOT EXISTS `post_popular` (
  `post_popular_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL COMMENT 'see lookup table for details',
  PRIMARY KEY (`post_popular_id`),
  KEY `FK_post_popular_post` (`post_id`),
  KEY `FK_post_popular_lookup` (`type`),
  CONSTRAINT `FK_post_popular_lookup` FOREIGN KEY (`type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_post_popular_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Global popularity for a post';



CREATE TABLE IF NOT EXISTS `post_private_recipient` (
  `post_private_recipient_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL COMMENT 'The id of the private post that this user has access to',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user that has access to this post.',
  `deleted` tinyint(10) unsigned NOT NULL COMMENT 'Has the recipient of this message deleted this message.',
  PRIMARY KEY (`post_private_recipient_id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `FK_post_private_recipient_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_post_private_recipient_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Link table for users who have access to private posts.';


CREATE TABLE IF NOT EXISTS `post_user` (
  `post_user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL COMMENT 'The ID of the post that has been made against a user',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user ID of the user who has an post against them.',
  PRIMARY KEY (`post_user_id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `FK_post_user_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_post_user_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user_id for posts made with a stream.kind of user';


CREATE TABLE IF NOT EXISTS `rhythm` (
  `rhythm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`rhythm_id`),
  KEY `FK_rhythm_user` (`user_id`),
  CONSTRAINT `FK_rhythm_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores all Rhythms for processing store data';


CREATE TABLE IF NOT EXISTS `rhythm_cat` (
  `rhythm_cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`rhythm_cat_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Rhythm categories';


CREATE TABLE IF NOT EXISTS `rhythm_extra` (
  `rhythm_extra_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rhythm_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Textual description of the Rhythm',
  `mini` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'minified version of the Rhythm',
  `full` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'original full version with preserved spacing',
  `status_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'see status table for valid values.',
  `version_id` int(10) unsigned NOT NULL COMMENT 'Link to version table',
  `rhythm_cat_id` int(10) unsigned NOT NULL COMMENT 'Which category does this Rhythm belong to',
  `meta_post_id` int(10) unsigned DEFAULT NULL COMMENT 'The id of the post that enables meta disscussion about this Rhythm.',
  PRIMARY KEY (`rhythm_extra_id`),
  UNIQUE KEY `version_id` (`version_id`),
  KEY `rhythm_id` (`rhythm_id`,`status_id`,`rhythm_cat_id`),
  KEY `FK_rhythm_extra_status` (`status_id`),
  KEY `FK_rhythm_extra_rhythm_cat` (`rhythm_cat_id`),
  KEY `FK_rhythm_extra_post` (`meta_post_id`),
  CONSTRAINT `FK_rhythm_extra_post` FOREIGN KEY (`meta_post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_rhythm_extra_rhythm` FOREIGN KEY (`rhythm_id`) REFERENCES `rhythm` (`rhythm_id`),
  CONSTRAINT `FK_rhythm_extra_rhythm_cat` FOREIGN KEY (`rhythm_cat_id`) REFERENCES `rhythm_cat` (`rhythm_cat_id`),
  CONSTRAINT `FK_rhythm_extra_status` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  CONSTRAINT `FK_rhythm_extra_version` FOREIGN KEY (`version_id`) REFERENCES `version` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `rhythm_param` (
  `rhythm_param_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key for this table',
  `rhythm_extra_id` int(10) unsigned NOT NULL COMMENT 'The extra id of the rhythm that this parameter belongs to.',
  `name` varchar(63) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of this parameter.',
  `hint` text COLLATE utf8_unicode_ci COMMENT 'The hint text for this parameter.',
  `display_order` int(10) unsigned NOT NULL COMMENT 'The display order of this parameter in this rhythm.',
  PRIMARY KEY (`rhythm_param_id`),
  UNIQUE KEY `rhythm_extra_id` (`rhythm_extra_id`,`name`),
  CONSTRAINT `FK_rhythm_param_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Details on client paramaters used by rhythms';


CREATE TABLE IF NOT EXISTS `rhythm_user_data` (
  `rhythm_user_data_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rhythm_extra_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'A JSON string, representing data a rhythm has stored for a user.',
  PRIMARY KEY (`rhythm_user_data_id`),
  KEY `rhythm_extra_id` (`rhythm_extra_id`,`user_id`),
  KEY `FK_rhythm_user_data_user` (`user_id`),
  CONSTRAINT `FK_rhythm_user_data_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_rhythm_user_data_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores temporary data for a user who has subscribed to a rhythm. Data is stored in the users data store, not the rhythms data store.';


CREATE TABLE IF NOT EXISTS `ring` (
  `ring_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user_id that represents this ring',
  `membership_type` int(10) unsigned NOT NULL COMMENT 'see lookup table',
  `membership_rhythm_id` int(10) unsigned DEFAULT NULL COMMENT 'The Rhythm that admins run to grant access to new members. Connects to rhythm_extra_id',
  `membership_rhythm_version_type` int(10) unsigned DEFAULT NULL COMMENT 'The version type for the membership Rhythm. See lookup table - version_type',
  `membership_super_ring_user_id` int(10) unsigned DEFAULT NULL COMMENT 'links to user.user_id Only used if membership_type is set to super_ring',
  `admin_type` int(10) unsigned NOT NULL COMMENT 'see lookup table',
  `admin_super_ring_user_id` int(10) unsigned DEFAULT NULL COMMENT 'links to user.user_id, not ring.ring_id',
  `ring_rhythm_id` int(10) unsigned DEFAULT NULL COMMENT 'The Rhythm that all members run. connects to rhythm_extra_id',
  `ring_rhythm_version_type` int(10) unsigned DEFAULT NULL COMMENT 'The version type of the Rhythm that all members run. See lookup table - version_type',
  PRIMARY KEY (`ring_id`),
  KEY `FK_ring_user` (`user_id`),
  KEY `FK_ring_lookup_mebership_type` (`membership_type`),
  KEY `FK_ring_super_ring_user` (`membership_super_ring_user_id`),
  KEY `FK_ring_lookup_admin_type` (`admin_type`),
  KEY `FK_ring_admin_super_ring_user` (`admin_super_ring_user_id`),
  KEY `FK_ring_membership_rhythm_extra` (`membership_rhythm_id`),
  KEY `FK_ring_rhythm_extra` (`ring_rhythm_id`),
  KEY `FK_ring_lookup_membership_rhythm_version_type` (`membership_rhythm_version_type`),
  KEY `FK_ring_lookup_rhythm_version_type` (`ring_rhythm_version_type`),
  CONSTRAINT `FK_ring_admin_super_ring_user` FOREIGN KEY (`admin_super_ring_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_ring_lookup_admin_type` FOREIGN KEY (`admin_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_ring_lookup_mebership_type` FOREIGN KEY (`membership_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_ring_lookup_membership_rhythm_version_type` FOREIGN KEY (`membership_rhythm_version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_ring_lookup_rhythm_version_type` FOREIGN KEY (`ring_rhythm_version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_ring_membership_rhythm_extra` FOREIGN KEY (`membership_rhythm_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_ring_rhythm_extra` FOREIGN KEY (`ring_rhythm_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_ring_super_ring_user` FOREIGN KEY (`membership_super_ring_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_ring_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `ring_application` (
  `ring_application_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key.',
  `ring_id` int(10) unsigned DEFAULT NULL COMMENT 'The ring that a user is applying to join.',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'The user who is applying to join this ring.',
  PRIMARY KEY (`ring_application_id`),
  UNIQUE KEY `ring_id` (`ring_id`,`user_id`),
  KEY `FK_ring_application_user` (`user_id`),
  CONSTRAINT `FK_ring_application_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_ring_application_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds records of users applying for membership of a ring.';


CREATE TABLE IF NOT EXISTS `ring_rhythm_data` (
  `ring_rhythm_data_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary key for the table.',
  `ring_id` int(10) unsigned DEFAULT NULL COMMENT 'Id of the ring this data belongs to.',
  `user_id` int(10) unsigned DEFAULT NULL COMMENT 'Id of the user who is a member of the ring that generated this data.',
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Date the data was created or updated.',
  `type_id` int(10) unsigned DEFAULT NULL COMMENT 'The type of rhythm that was running. See lookup table for values.',
  `data` text COLLATE utf8_unicode_ci COMMENT 'The data being stored.',
  PRIMARY KEY (`ring_rhythm_data_id`),
  KEY `FK_ring_rhythm_data_ring` (`ring_id`),
  KEY `FK_ring_rhythm_data_user` (`user_id`),
  KEY `FK_ring_rhythm_data_lookup` (`type_id`),
  CONSTRAINT `FK_ring_rhythm_data_lookup` FOREIGN KEY (`type_id`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_ring_rhythm_data_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_ring_rhythm_data_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Data stored by a rings rhythms after its admins and members have run them.';


CREATE TABLE IF NOT EXISTS `ring_take_name` (
  `ring_take_name_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ring_id` int(10) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name of the take',
  `amount` bigint(10) NOT NULL,
  `stream_id` int(10) unsigned DEFAULT NULL COMMENT 'The stream that this take can be used on.',
  `stream_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The version of the stream. Recorded separately so that it can include ''latest'' versions @todo this needs merging into the version_type in the lookup table.',
  `field_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The field_id in the stream that is taken for this ring. (really the display order @fixme))',
  PRIMARY KEY (`ring_take_name_id`),
  KEY `ring_id` (`ring_id`,`name`),
  KEY `FK_ring_take_name_stream_field` (`stream_id`,`field_id`),
  CONSTRAINT `FK_ring_take_name_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_ring_take_name_stream_extra` FOREIGN KEY (`stream_id`) REFERENCES `stream` (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='take names for rings';


CREATE TABLE IF NOT EXISTS `ring_user_take` (
  `ring_user_take_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `ring_take_name_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ring_user_take_id`),
  KEY `user_id` (`user_id`,`ring_take_name_id`,`post_id`),
  KEY `FK_ring_user_take_ring_take_name` (`ring_take_name_id`),
  KEY `FK_ring_user_take_post` (`post_id`),
  CONSTRAINT `FK_ring_user_take_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_ring_user_take_ring_take_name` FOREIGN KEY (`ring_take_name_id`) REFERENCES `ring_take_name` (`ring_take_name_id`),
  CONSTRAINT `FK_ring_user_take_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='users who have taken in the name of a ring';


CREATE TABLE IF NOT EXISTS `signup_code` (
  `signup_code_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The uuid that is required for a user to sign up.',
  `primary_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A category to identify the source of this code.',
  `secondary_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A second category to identify the source of this code.',
  `used_user_id` int(10) unsigned DEFAULT NULL COMMENT 'The user_id of the user who signed up with this code.',
  `hold_for_domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If the user is logging in for the first time this is their domain name.',
  `hold_for_username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'If the user is logging in for the first time this is their username.',
  PRIMARY KEY (`signup_code_id`),
  UNIQUE KEY `code` (`code`),
  KEY `primary_category` (`primary_category`),
  KEY `secondary_category` (`secondary_category`),
  KEY `FK_signup_code_user` (`used_user_id`),
  CONSTRAINT `FK_signup_code_user` FOREIGN KEY (`used_user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Signup validation codes that allow a user to signup (If option is turned on in the config.)';


CREATE TABLE IF NOT EXISTS `site` (
  `site_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`site_id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Link table for website urls';


CREATE TABLE IF NOT EXISTS `site_access` (
  `site_access_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL COMMENT 'The domain that is logged in',
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `login_expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Time at which the login expires',
  `session_id` varchar(255) CHARACTER SET latin1 NOT NULL COMMENT 'Expiry date is not checked if the session_id is stil valid',
  PRIMARY KEY (`site_access_id`),
  KEY `user_id` (`user_id`),
  KEY `FK_site_access_site` (`site_id`),
  CONSTRAINT `FK_site_access_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_site_access_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Sites that a user has given permission to login to';


CREATE TABLE IF NOT EXISTS `status` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_order` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`status_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stream` (
  `stream_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT 'User who created this stream',
  `kind` int(10) unsigned NOT NULL COMMENT 'See lookup table for options. The kind of stream. See prototype description for details',
  PRIMARY KEY (`stream_id`),
  KEY `cat_id` (`user_id`),
  KEY `FK_stream_lookup` (`kind`),
  CONSTRAINT `FK_stream_lookup` FOREIGN KEY (`kind`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Describes different posts that can be made';


CREATE TABLE IF NOT EXISTS `stream_block` (
  `stream_block_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `block_number` int(10) unsigned NOT NULL,
  PRIMARY KEY (`stream_block_id`),
  KEY `stream_extra_id` (`stream_extra_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `block_number` (`block_number`),
  CONSTRAINT `FK_stream_block_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stream_block_tree` (
  `stream_block_tree_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(10) unsigned NOT NULL COMMENT 'The top post id for this tree',
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `block_number` int(10) unsigned NOT NULL,
  PRIMARY KEY (`stream_block_tree_id`),
  KEY `stream_extra_id` (`post_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `block_number` (`block_number`),
  CONSTRAINT `FK_stream_block_tree_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stream_child` (
  `stream_child_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL COMMENT 'parent stream_extra_id',
  `child_id` int(10) unsigned NOT NULL COMMENT 'child stream_extra_id',
  `version_type` int(10) unsigned NOT NULL COMMENT 'see version_type in the lookup table. Defaults to major.minor.patch',
  `sort_order` int(10) unsigned NOT NULL COMMENT 'The order that the child streams appear in. 1 is high.',
  PRIMARY KEY (`stream_child_id`),
  UNIQUE KEY `unique_row` (`parent_id`,`child_id`,`version_type`),
  KEY `parent_id` (`parent_id`,`child_id`),
  KEY `FK_stream_child_child_stream_extra` (`child_id`),
  KEY `FK_stream_child_lookup` (`version_type`),
  CONSTRAINT `FK_stream_child_child_stream_extra` FOREIGN KEY (`child_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_stream_child_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_child_parent_stream_extra` FOREIGN KEY (`parent_id`) REFERENCES `stream_extra` (`stream_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores relations of child streams to parents';


CREATE TABLE IF NOT EXISTS `stream_default_rhythm` (
  `stream_default_rhythm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'The extra id of the stream that has a default rhythm.',
  `rhythm_extra_id` int(10) unsigned NOT NULL COMMENT 'The extra of of the rhythm that is a default for this stream.',
  `version_type` int(10) unsigned NOT NULL COMMENT 'The type of version that is used for this rhythm. See lookup table for details.',
  `sort_order` int(10) unsigned NOT NULL COMMENT 'The order these defaults appear in.  1 is high.',
  PRIMARY KEY (`stream_default_rhythm_id`),
  UNIQUE KEY `stream_extra_id` (`stream_extra_id`,`rhythm_extra_id`,`version_type`),
  UNIQUE KEY `sort_order` (`stream_extra_id`,`sort_order`),
  KEY `FK_stream_default_rhythm_rhythm_extra` (`rhythm_extra_id`),
  KEY `FK_stream_default_rhythm_lookup` (`version_type`),
  CONSTRAINT `FK_stream_default_rhythm_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_default_rhythm_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_stream_default_rhythm_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores the default rhythm subscriptions for a stream.';


CREATE TABLE IF NOT EXISTS `stream_default_ring` (
  `stream_default_ring_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL,
  `ring_user_id` int(10) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL COMMENT 'The order that the default rings appear in. 1 is high.',
  PRIMARY KEY (`stream_default_ring_id`),
  KEY `FK_stream_default_ring_stream_extra` (`stream_extra_id`),
  KEY `FK_stream_default_ring_user` (`ring_user_id`),
  CONSTRAINT `FK_stream_default_ring_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_stream_default_ring_user` FOREIGN KEY (`ring_user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stream_extra` (
  `stream_extra_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(10) unsigned NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8_unicode_ci,
  `version_id` int(10) unsigned NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `group_period` int(10) unsigned DEFAULT '45' COMMENT 'The time range for post results. See lookup table for valid values',
  `post_mode` int(10) unsigned NOT NULL DEFAULT '140' COMMENT 'Who can submit posts to this stream. See lookup table for valid values',
  `meta_post_id` int(10) unsigned DEFAULT NULL COMMENT 'links to an post where people can vote and comment on this stream.',
  `edit_mode` int(10) unsigned NOT NULL DEFAULT '146' COMMENT 'Who can edit this post. See lookup table for details.',
  PRIMARY KEY (`stream_extra_id`),
  KEY `stream_id` (`stream_id`),
  KEY `status_id` (`status_id`),
  KEY `version_id` (`version_id`),
  KEY `FK_stream_extra_lookup_group_period` (`group_period`),
  KEY `FK_stream_extra_lookup_post_mode` (`post_mode`),
  KEY `FK_stream_extra_lookup_edit_mode` (`edit_mode`),
  KEY `FK_stream_extra_meta_post_id` (`meta_post_id`),
  CONSTRAINT `FK_stream_extra_lookup_edit_mode` FOREIGN KEY (`edit_mode`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_extra_lookup_group_period` FOREIGN KEY (`group_period`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_extra_lookup_post_mode` FOREIGN KEY (`post_mode`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_extra_meta_post_id` FOREIGN KEY (`meta_post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_stream_extra_status` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  CONSTRAINT `FK_stream_extra_stream` FOREIGN KEY (`stream_id`) REFERENCES `stream` (`stream_id`),
  CONSTRAINT `FK_stream_extra_version` FOREIGN KEY (`version_id`) REFERENCES `version` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stream details that vary by version';


CREATE TABLE IF NOT EXISTS `stream_field` (
  `stream_field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL,
  `field_type` int(10) unsigned NOT NULL COMMENT 'Which data type to use for this field. See lookup table for valid types',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The label for the field',
  `max_size` int(10) unsigned DEFAULT '200' COMMENT 'maximum length for text fields',
  `required` tinyint(10) NOT NULL DEFAULT '0',
  `regex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'A regular expression used to validate the contents of a field',
  `regex_error` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'An error message to display if the regex is not matched',
  `checkbox_default` tinyint(10) NOT NULL DEFAULT '0' COMMENT '0 = unchecked. 1 = checked',
  `taken_records` tinyint(10) NOT NULL COMMENT 'When someone takes up this post, do they by default record this value in their own data store.',
  `display_order` int(10) unsigned NOT NULL,
  `value_min` bigint(10) DEFAULT NULL COMMENT 'Minimum value if this is a value field',
  `value_max` bigint(10) DEFAULT NULL COMMENT 'maximum value if this is a value field',
  `value_type` int(10) unsigned DEFAULT NULL COMMENT 'The type of value if this is a value field. See lookup table',
  `value_options` int(10) unsigned DEFAULT NULL COMMENT 'See lookup table for valid values',
  `select_qty_max` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'How many items the user can select if this is a select list',
  `select_qty_min` int(10) unsigned DEFAULT '0' COMMENT 'Minimum number of items the user can select if this is a select list',
  `rhythm_check_url` varchar(1023) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The url of the Rhythm that is used to check posts made are accepted',
  `who_can_take` int(10) unsigned DEFAULT '149' COMMENT 'Who can make a take on posts in this stream field. See lookup table for options',
  `text_type_id` int(10) unsigned NOT NULL DEFAULT '208' COMMENT 'The type of text that a text box uses. See lookup table.',
  PRIMARY KEY (`stream_field_id`),
  UNIQUE KEY `stream_extra_id` (`stream_extra_id`,`display_order`),
  KEY `stream_id` (`stream_extra_id`),
  KEY `display_order` (`display_order`),
  KEY `FK_stream_field_lookup_field_type` (`field_type`),
  KEY `FK_stream_field_lookup_value_type` (`value_type`),
  KEY `FK_stream_field_lookup_value_options` (`value_options`),
  KEY `FK_stream_field_lookup_who_can_take` (`who_can_take`),
  KEY `FK_stream_field_lookup_text_type` (`text_type_id`),
  CONSTRAINT `FK_stream_field_lookup_field_type` FOREIGN KEY (`field_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_field_lookup_text_type` FOREIGN KEY (`text_type_id`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_field_lookup_value_options` FOREIGN KEY (`value_options`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_field_lookup_value_type` FOREIGN KEY (`value_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_field_lookup_who_can_take` FOREIGN KEY (`who_can_take`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_stream_field_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stream_list` (
  `stream_list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_field_id` int(10) unsigned NOT NULL,
  `name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`stream_list_id`),
  KEY `stream_field_id` (`stream_field_id`),
  CONSTRAINT `FK_stream_list_stream_field` FOREIGN KEY (`stream_field_id`) REFERENCES `stream_field` (`stream_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List items for a stream field';


CREATE TABLE IF NOT EXISTS `stream_open_list_item` (
  `stream_open_list_item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'The extra id of the stream that the open list is in.',
  `field_id` int(10) unsigned NOT NULL COMMENT 'The display_order of the field in the stream that the open list is in.',
  `item` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The item entered into the open list.',
  `count` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'A count of the number of times this item has been used.',
  PRIMARY KEY (`stream_open_list_item_id`),
  KEY `stream_extra_id` (`stream_extra_id`,`field_id`),
  KEY `item` (`item`),
  KEY `FK_stream_open_list_item_stream_field` (`field_id`),
  CONSTRAINT `FK_stream_open_list_item_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_stream_open_list_item_stream_field` FOREIGN KEY (`stream_extra_id`, `field_id`) REFERENCES `stream_field` (`stream_extra_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Keeps a record of all items entered into an open list in a stream. Used to generate suggestions.';


CREATE TABLE IF NOT EXISTS `stream_public` (
  `stream_public_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_cached` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stream_extra_id` int(10) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `score` bigint(10) NOT NULL COMMENT 'The assigned score for this post, as displayed to the public',
  `rhythm_extra_id` int(10) unsigned DEFAULT NULL COMMENT 'The rhythm that was used to generate these results. Null is for generic results.',
  `top_parent_id` int(10) unsigned DEFAULT NULL COMMENT 'If this is a tree sort then this is the top parent id for the tree.',
  PRIMARY KEY (`stream_public_id`),
  UNIQUE KEY `stream_extra_id` (`stream_extra_id`,`post_id`,`rhythm_extra_id`,`top_parent_id`),
  KEY `score` (`score`),
  KEY `FK_stream_public_rhythm_extra` (`rhythm_extra_id`),
  KEY `FK_stream_public_post` (`post_id`),
  KEY `FK_stream_public_top_parent_post` (`top_parent_id`),
  CONSTRAINT `FK_stream_public_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_stream_public_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_stream_public_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_stream_public_top_parent_post` FOREIGN KEY (`top_parent_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='An index of top stream results for public and search engine consumption.';


CREATE TABLE IF NOT EXISTS `stream_public_rhythm` (
  `stream_public_rhythm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'The extra id of the stream that results have been generated for.',
  `rhythm_extra_id` int(10) unsigned DEFAULT NULL COMMENT 'The extra id of the  rhythm that was used to generate results. Null is for generic results.',
  `top_parent_id` int(10) unsigned DEFAULT NULL COMMENT 'The top parent of the rhythm that was used to generate tree results. Null indicates a stream',
  `date_generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp for when the results were generated.',
  PRIMARY KEY (`stream_public_rhythm_id`),
  KEY `FK_stream_public_rhythm_stream_extra` (`stream_extra_id`),
  KEY `FK_stream_public_rhythm_rhythm_extra` (`rhythm_extra_id`),
  KEY `FK_stream_public_rhythm_post` (`top_parent_id`),
  CONSTRAINT `FK_stream_public_rhythm_post` FOREIGN KEY (`top_parent_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_stream_public_rhythm_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_stream_public_rhythm_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Lists stream rhythms that have public results stored for them.';


CREATE TABLE IF NOT EXISTS `stream_regex` (
  `stream_regex_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `regex` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `error` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Error message to display for this regEx',
  `display_order` int(10) unsigned NOT NULL DEFAULT '1000',
  PRIMARY KEY (`stream_regex_id`),
  KEY `display_order` (`display_order`),
  KEY `regex` (`regex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Default regular expressions for streams';


CREATE TABLE IF NOT EXISTS `suggestions_declined` (
  `suggestions_declined_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user who declined the suggestion.',
  `site_id` int(10) unsigned NOT NULL COMMENT 'The id of the client site that the suggestion was declined on.',
  `rhythm_cat_id` int(10) unsigned NOT NULL COMMENT 'The type of suggestion that was declined. This decides which of the following fileds are used.',
  `declined_rhythm_extra_id` int(10) unsigned DEFAULT NULL COMMENT 'The id of the rhythm that was declined.',
  `declined_stream_extra_id` int(10) unsigned DEFAULT NULL COMMENT 'The id of the stream that was declined.',
  `declined_user_id` int(10) unsigned DEFAULT NULL COMMENT 'The id of the user that was declined.',
  `version_type` int(10) unsigned DEFAULT NULL COMMENT 'The type of version that was declined. See lookup table for valid values.',
  `date_declined` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The time that the suggestion was declined.',
  PRIMARY KEY (`suggestions_declined_id`),
  KEY `user_id` (`user_id`),
  KEY `FK_suggestions_declined_site` (`site_id`),
  KEY `FK_suggestions_declined_rhythm_cat` (`rhythm_cat_id`),
  KEY `FK_suggestions_declined_rhythm_extra` (`declined_rhythm_extra_id`),
  KEY `FK_suggestions_declined_stream_extra` (`declined_stream_extra_id`),
  KEY `FK_suggestions_declined_user_2` (`declined_user_id`),
  KEY `FK_suggestions_declined_lookup` (`version_type`),
  CONSTRAINT `FK_suggestions_declined_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_suggestions_declined_rhythm_cat` FOREIGN KEY (`rhythm_cat_id`) REFERENCES `rhythm_cat` (`rhythm_cat_id`),
  CONSTRAINT `FK_suggestions_declined_rhythm_extra` FOREIGN KEY (`declined_rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_suggestions_declined_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_suggestions_declined_stream_extra` FOREIGN KEY (`declined_stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_suggestions_declined_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_suggestions_declined_user_2` FOREIGN KEY (`declined_user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='A list of suggestions that a user has declined for a client website';


CREATE TABLE IF NOT EXISTS `take` (
  `take_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_taken` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `value` bigint(10) NOT NULL,
  `field_id` int(10) unsigned NOT NULL COMMENT 'Which field in the stream does this take reffer to. This is the display order, not the stream_field_id',
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'Prevents having to link to the post table to look up',
  `block_id` int(10) NOT NULL DEFAULT '0' COMMENT 'The block of user takes that this is a part of',
  `stream_block_id` int(10) NOT NULL DEFAULT '0' COMMENT 'The block of stream user takes that this is a part of',
  `tree_block_id` int(10) NOT NULL DEFAULT '0' COMMENT 'The block of tree user takes that this is a part of',
  PRIMARY KEY (`take_id`),
  UNIQUE KEY `user_id` (`user_id`,`post_id`,`field_id`),
  KEY `block_id` (`block_id`),
  KEY `stream_block_id` (`stream_block_id`),
  KEY `tree_block_id` (`tree_block_id`),
  KEY `stream_extra_id` (`stream_extra_id`),
  KEY `date_taken` (`date_taken`),
  KEY `FK_take_post` (`post_id`),
  KEY `FK_take_stream_field` (`field_id`),
  CONSTRAINT `FK_take_post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_take_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_take_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Records all posts that have been taken.';


CREATE TABLE IF NOT EXISTS `take_kindred` (
  `take_kindred_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_processed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `take_id` int(10) unsigned NOT NULL COMMENT 'The take that has been processed',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user who owns this rhythm process.',
  `scored_user_id` int(10) unsigned NOT NULL COMMENT 'The user who this score is for',
  `user_rhythm_id` int(10) unsigned NOT NULL COMMENT 'The kindred rhythm that is being used to process takes. May be a partial version - hence the link to user_rhythm_id rather than rhythm_extra_id',
  `score` bigint(10) NOT NULL DEFAULT '0' COMMENT 'The score given by this rhythm',
  PRIMARY KEY (`take_kindred_id`),
  KEY `user_id` (`user_id`),
  KEY `rhythm_id` (`user_rhythm_id`),
  KEY `take_id` (`take_id`),
  KEY `FK_take_kindred_scored_user` (`scored_user_id`),
  CONSTRAINT `FK_take_kindred_scored_user` FOREIGN KEY (`scored_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_take_kindred_take` FOREIGN KEY (`take_id`) REFERENCES `take` (`take_id`),
  CONSTRAINT `FK_take_kindred_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_take_kindred_user_rhythm` FOREIGN KEY (`user_rhythm_id`) REFERENCES `user_rhythm` (`user_rhythm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Takes that have been processed by users kindred rhythms';


CREATE TABLE IF NOT EXISTS `take_value_list` (
  `take_value_list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stream_field_id` int(10) unsigned NOT NULL COMMENT 'The id of the field in the stream that the take list belongs to.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The list item name.',
  `value` int(10) NOT NULL COMMENT 'The value that this item has.',
  PRIMARY KEY (`take_value_list_id`),
  KEY `stream_field_id` (`stream_field_id`),
  KEY `value` (`value`),
  CONSTRAINT `FK_take_value_list_stream_field` FOREIGN KEY (`stream_field_id`) REFERENCES `stream_field` (`stream_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List items associated with a take field with a value_type of list';


CREATE TABLE IF NOT EXISTS `test_data` (
  `test_data_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `row` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'json data : {column_name : data,...}',
  `display_order` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '1 = first, 0 = turned off. ',
  PRIMARY KEY (`test_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Restore this data for tests to complete successfully.';


CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `site_id` int(10) unsigned NOT NULL COMMENT 'Users without the local site id are stored for lookup purposes',
  `password` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salt` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(127) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role` int(10) unsigned NOT NULL DEFAULT '22' COMMENT 'See lookup table for valid values',
  `is_ring` tinyint(10) NOT NULL DEFAULT '0' COMMENT 'Is this user really a ring',
  `test_user` tinyint(10) NOT NULL DEFAULT '0' COMMENT 'If set to ''1'' then this is a test user. All test user data is regularly deleted and reset.',
  `meta_post_id` int(10) unsigned DEFAULT NULL COMMENT 'The meta post id for this user/ring. Used for disscussion about this user.',
  `reset_secret` varchar(63) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'When a user requests a password reset, this secret is sent in the email and must match to reset the password.',
  `reset_time` datetime DEFAULT NULL COMMENT 'Time for when a reset_secret is made.',
  `csfr` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Cross site forgery request token. Changed on each login.',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`,`site_id`),
  KEY `FK_user_site` (`site_id`),
  KEY `FK_user_lookup` (`role`),
  KEY `FK_user_post` (`meta_post_id`),
  CONSTRAINT `FK_user_lookup` FOREIGN KEY (`role`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_post` FOREIGN KEY (`meta_post_id`) REFERENCES `post` (`post_id`),
  CONSTRAINT `FK_user_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='This table store all users on the Babbling Brook network that have a r';


CREATE TABLE IF NOT EXISTS `user_client_data` (
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user data is being stored for.',
  `site_id` int(10) unsigned NOT NULL COMMENT 'The id of the client site that data is being stored for.',
  `client_key` varchar(63) COLLATE utf8_unicode_ci NOT NULL COMMENT 'A key given to the data by the client website',
  `depth_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The key for this item in the tree.',
  `data_type` varchar(63) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The type of data that is stored on this row.',
  `data` text COLLATE utf8_unicode_ci COMMENT 'The string of stored data. Null if this is a container.',
  `lft` int(10) NOT NULL COMMENT 'The left hand boundary of this sub group (Nested Set tree).',
  `rgt` int(10) NOT NULL COMMENT 'The right hand boundary of this sub group (Nested Set tree).',
  PRIMARY KEY (`user_id`,`site_id`,`client_key`,`depth_key`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  KEY `FK_user_client_data_site` (`site_id`),
  CONSTRAINT `FK_user_client_data_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_user_client_data_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Data stored by a client website in the users account.';


CREATE TABLE IF NOT EXISTS `user_config` (
  `user_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `code` varchar(127) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_config_id`),
  KEY `user_id` (`user_id`),
  KEY `code` (`code`),
  CONSTRAINT `FK_user_config_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores config options for each user';


CREATE TABLE IF NOT EXISTS `user_config_default` (
  `user_config_default_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `display_order` int(10) unsigned NOT NULL COMMENT 'The order this config item appears on the setup page.',
  `code` varchar(127) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The code that is used to identify this config item in the codebase.',
  `name` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The name displayed on the config page.',
  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The description, shown in the help popup on the config page.',
  `type_id` int(10) unsigned NOT NULL COMMENT 'The type of config option. Used to define which action is used to update it on the config page. See lookup table for options.',
  `default_value` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The value a user gets if they don''t have a value set in user_config.',
  `extra_data` text COLLATE utf8_unicode_ci COMMENT 'A blob of json data that is needed by the config page to edit this option.',
  PRIMARY KEY (`user_config_default_id`),
  UNIQUE KEY `display_order` (`display_order`),
  UNIQUE KEY `code` (`code`),
  KEY `FK_user_config_default_lookup_type` (`type_id`),
  CONSTRAINT `FK_user_config_default_lookup_type` FOREIGN KEY (`type_id`) REFERENCES `lookup` (`lookup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The default config options that are used when a user signs up, or does not have an option set.';



CREATE TABLE IF NOT EXISTS `user_feature_usage` (
  `user_feature_usage_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_used` date NOT NULL COMMENT 'date only, no time',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user who owns this',
  `qty` int(10) unsigned NOT NULL COMMENT 'Qty of times used',
  `feature` int(10) unsigned NOT NULL COMMENT 'See lookup table',
  `extra_id` int(10) unsigned NOT NULL COMMENT 'eg rhythm_extra_id or stream_extra_id',
  PRIMARY KEY (`user_feature_usage_id`),
  KEY `user_id` (`user_id`),
  KEY `FK_user_feature_usage_lookup` (`feature`),
  CONSTRAINT `FK_user_feature_usage_lookup` FOREIGN KEY (`feature`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_feature_usage_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Records the number of times  a feature is used by a user';


CREATE TABLE IF NOT EXISTS `user_level` (
  `level_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key.',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user.',
  `tutorial_set` int(10) unsigned NOT NULL DEFAULT '166' COMMENT 'The name of the tutorial set being followed (See lookup table).',
  `level_name` int(10) unsigned NOT NULL DEFAULT '167' COMMENT 'The name of the level currently on (See lookup table).',
  `enabled` tinyint(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Are tutorials enabled for this user.',
  PRIMARY KEY (`level_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `FK_user_level_lookup_tutorial_set` (`tutorial_set`),
  KEY `FK_user_level_lookup` (`level_name`),
  CONSTRAINT `FK_user_level_lookup` FOREIGN KEY (`level_name`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_level_lookup_tutorial_set` FOREIGN KEY (`tutorial_set`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_level_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores info relating to the level of the current user.';


CREATE TABLE IF NOT EXISTS `user_profile` (
  `user_profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `real_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `about` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`user_profile_id`),
  KEY `FK_user_profile_user` (`user_id`),
  CONSTRAINT `FK_user_profile_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='@todo this is needs removing. use user_profile';


CREATE TABLE IF NOT EXISTS `user_rhythm` (
  `user_rhythm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `rhythm_extra_id` int(10) unsigned NOT NULL,
  `version_type` int(10) unsigned NOT NULL,
  `order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_rhythm_id`),
  KEY `user_id` (`user_id`,`rhythm_extra_id`,`version_type`,`order`),
  KEY `FK_user_rhythm_rhythm_extra` (`rhythm_extra_id`),
  KEY `FK_user_rhythm_lookup` (`version_type`),
  CONSTRAINT `FK_user_rhythm_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_rhythm_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_user_rhythm_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Default Rhythms being used by this user. See link to rhythm_extra.rhythm_type to see which kind of Rhythm.';


CREATE TABLE IF NOT EXISTS `user_ring` (
  `user_ring_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ring_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT 'the id of the user that is a member of this group',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'This users password to access this ring',
  `admin` tinyint(10) unsigned DEFAULT NULL COMMENT 'Does this user have admin rights to this ring',
  `member` tinyint(10) unsigned DEFAULT NULL COMMENT 'Is this user a member of this ring',
  `ban` tinyint(10) unsigned DEFAULT NULL COMMENT 'Is this user banned',
  PRIMARY KEY (`user_ring_id`),
  KEY `user_id` (`user_id`),
  KEY `FK_user_ring_ring` (`ring_id`),
  CONSTRAINT `FK_user_ring_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_user_ring_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='members and admins of a local ring. This should be named ring_user as it is owned by the ring domain not the user domain.';


CREATE TABLE IF NOT EXISTS `user_ring_password` (
  `user_ring_password_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'A local user',
  `ring_user_id` int(10) unsigned NOT NULL COMMENT 'The user id of the ring',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'password needed for this user to gain access to the ring',
  PRIMARY KEY (`user_ring_password_id`),
  KEY `FK_user_ring_password_user` (`user_id`),
  KEY `FK_user_ring_password_ring_user` (`ring_user_id`),
  CONSTRAINT `FK_user_ring_password_ring_user` FOREIGN KEY (`ring_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_user_ring_password_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The passwords for users access to rings both local and remote';



CREATE TABLE IF NOT EXISTS `user_secret` (
  `user_secret_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user that the secret is for.',
  `secret` tinytext COLLATE utf8_unicode_ci NOT NULL COMMENT 'The secret',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation date.',
  PRIMARY KEY (`user_secret_id`),
  KEY `FK_user_secret_user` (`user_id`),
  CONSTRAINT `FK_user_secret_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores secrets for users. Used by other stores to verify that a user generated a secret.';


CREATE TABLE IF NOT EXISTS `user_stream_count` (
  `user_stream_count_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `stream_extra_id` int(10) unsigned NOT NULL,
  `total` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_stream_count_id`),
  KEY `total` (`total`),
  KEY `user_id` (`user_id`),
  KEY `stream_extra_id` (`stream_extra_id`),
  CONSTRAINT `FK_user_stream_count_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_user_stream_count_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The number of times a user has taken stream. kind=user';


CREATE TABLE IF NOT EXISTS `user_stream_subscription` (
  `user_stream_subscription_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `site_id` int(10) unsigned NOT NULL COMMENT 'The id of the client website that these subscriptions are for.',
  `stream_extra_id` int(10) unsigned NOT NULL,
  `version_type` int(10) unsigned NOT NULL COMMENT 'The version type of the stream or post_group',
  `display_order` int(10) unsigned NOT NULL COMMENT 'items with the same display order are displayed together',
  `locked` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Can the user unsubscribe from this stream.',
  PRIMARY KEY (`user_stream_subscription_id`),
  UNIQUE KEY `user_id` (`user_id`,`display_order`,`site_id`),
  KEY `stream_extra_id` (`stream_extra_id`),
  KEY `FK_user_stream_subscription_site` (`site_id`),
  KEY `FK_user_stream_subscription_lookup` (`version_type`),
  CONSTRAINT `FK_user_stream_subscription_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_stream_subscription_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_user_stream_subscription_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_user_stream_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='The streams that a user is subscribed to';


CREATE TABLE IF NOT EXISTS `user_stream_subscription_filter` (
  `user_stream_subscription_filter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_stream_subscription_id` int(10) unsigned NOT NULL,
  `rhythm_extra_id` int(10) unsigned NOT NULL,
  `version_type` int(10) unsigned NOT NULL COMMENT 'version type for the Rhythm (see lookup table)',
  `display_order` int(10) unsigned NOT NULL,
  `locked` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Can the user unsubscribe from this filter.',
  PRIMARY KEY (`user_stream_subscription_filter_id`),
  UNIQUE KEY `user_post_stream_id` (`user_stream_subscription_id`,`display_order`),
  KEY `FK_user_stream_subscription_filter_rhythm_extra` (`rhythm_extra_id`),
  KEY `FK_user_stream_subscription_filter_lookup` (`version_type`),
  CONSTRAINT `FK_user_stream_subscription_filter_lookup` FOREIGN KEY (`version_type`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_user_stream_subscription_filter_rhythm_extra` FOREIGN KEY (`rhythm_extra_id`) REFERENCES `rhythm_extra` (`rhythm_extra_id`),
  CONSTRAINT `FK_user_stream_subscription_filter_user_stream_subscription` FOREIGN KEY (`user_stream_subscription_id`) REFERENCES `user_stream_subscription` (`user_stream_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Lists the filters that a user has subscribed to for a stream subscription.';


CREATE TABLE IF NOT EXISTS `user_stream_subscription_ring` (
  `user_stream_subscription_ring_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The primary key of the table.',
  `user_stream_subscription_id` int(10) unsigned NOT NULL COMMENT 'The id of the ring that is being used.',
  `ring_id` int(10) unsigned NOT NULL COMMENT 'The id of the ring that is being used.',
  `locked` bit(1) NOT NULL DEFAULT b'0' COMMENT 'Is the subscription locked.',
  `display_order` int(10) NOT NULL COMMENT 'The display order of the rings.',
  PRIMARY KEY (`user_stream_subscription_ring_id`),
  KEY `user_post_stream_id` (`user_stream_subscription_id`),
  KEY `ring_id` (`ring_id`),
  CONSTRAINT `FK_user_stream_subscription_ring_ring` FOREIGN KEY (`ring_id`) REFERENCES `ring` (`ring_id`),
  CONSTRAINT `FK_user_stream_subscription_ring_user_stream_subscription` FOREIGN KEY (`user_stream_subscription_id`) REFERENCES `user_stream_subscription` (`user_stream_subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Moderation rings for a users stream subscriptions.';


CREATE TABLE IF NOT EXISTS `user_take` (
  `user_take_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'The user who has been taken',
  `stream_extra_id` int(10) unsigned NOT NULL COMMENT 'The stream used to take the user',
  `take_id` int(10) unsigned NOT NULL COMMENT 'The id of the take. This will not be local if the stream is not local.',
  `post_id` int(10) unsigned NOT NULL COMMENT 'The id of the post. This will not be local if the stream is not local.',
  `take_user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user who has taken this post',
  PRIMARY KEY (`user_take_id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  KEY `take_user_id` (`take_user_id`),
  KEY `take_id` (`take_id`),
  KEY `FK_user_take_stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_user_take_stream_extra` FOREIGN KEY (`stream_extra_id`) REFERENCES `stream_extra` (`stream_extra_id`),
  CONSTRAINT `FK_user_take_take` FOREIGN KEY (`take_id`) REFERENCES `take` (`take_id`),
  CONSTRAINT `FK_user_take_take_user` FOREIGN KEY (`take_user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `FK_user_take_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `post` FOREIGN KEY (`post_id`) REFERENCES `post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Records all posts takes, where stream.kind = user';


CREATE TABLE IF NOT EXISTS `version` (
  `version_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int(10) unsigned NOT NULL COMMENT 'The table holds versions for multiple things.  This associaiates all versions of the same thing together',
  `type` int(10) unsigned NOT NULL COMMENT 'The thing that is versioned. See lookup table for valid values',
  `major` int(10) unsigned NOT NULL,
  `minor` int(10) unsigned NOT NULL,
  `patch` int(10) unsigned NOT NULL,
  PRIMARY KEY (`version_id`),
  KEY `version_master` (`family_id`,`major`,`minor`,`patch`),
  KEY `type` (`type`),
  CONSTRAINT `FK_version_lookup` FOREIGN KEY (`type`) REFERENCES `lookup` (`lookup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Version information for any other table that requires versio';


CREATE TABLE IF NOT EXISTS `waiting_post_time` (
  `waiting_post_time_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'The primary key of this table.',
  `user_id` int(10) unsigned NOT NULL COMMENT 'The id of the user whos message count is recorded.',
  `site_id` int(10) unsigned DEFAULT NULL COMMENT 'The site id of the local inbox. If null then this is the global inbox.',
  `time_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp for the last time this user accessed this inbox.',
  `type_id` int(10) unsigned NOT NULL DEFAULT '164' COMMENT 'The type of time wait. See Lookup table for valid values.',
  PRIMARY KEY (`waiting_post_time_id`),
  UNIQUE KEY `user_id` (`user_id`,`site_id`,`type_id`),
  KEY `FK_waiting_post_time_site` (`site_id`),
  KEY `FK_waiting_post_time_lookup` (`type_id`),
  CONSTRAINT `FK_waiting_post_time_lookup` FOREIGN KEY (`type_id`) REFERENCES `lookup` (`lookup_id`),
  CONSTRAINT `FK_waiting_post_time_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`site_id`),
  CONSTRAINT `FK_waiting_post_time_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Povides the last time a user accessed their inbox.';


CREATE TABLE IF NOT EXISTS `_subtree` (
  `child_id` int(10) unsigned NOT NULL COMMENT 'child stream_extra_id',
  `parent_id` int(10) unsigned NOT NULL COMMENT 'parent stream_extra_id',
  `level` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`child_id`,`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
