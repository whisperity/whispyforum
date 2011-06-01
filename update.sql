# WhispyForum SQL update file
# 
# You can use this update file to modify database
# structure if you don't want full reinstall.
# 
# You must apply EVERY modification that was committed
# between your database and script revision.
# 
# You must apply the revision in order.
# 
# EXAMPLE:
# 
# If you have a database installed with
# revision 98, and your current files requir
# revision 250, you must apply every update query
# like revision 105, revision 140, revision 180
# revision 198, revision 202, etc.
#
# EXAMPLE 2:
# 
# If yo have a database installed with
# using a revision 421 system, you have
# the users table.
# However, to use the menu system, you need to 
# apply the changes of 
# revisions: 460, 491, 497 and 518.

#
# Revision 419 (creating database)
#
CREATE DATABASE IF NOT EXISTS `databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

#
# Revision 421 (adding users table)
#
CREATE TABLE IF NOT EXISTS users (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user loginname',
	`pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user password (md5 hashed)',
	`email` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'user e-mail address',
	`curr_ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0.0.0' COMMENT 'current session IP address',
	`curr_sessid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'current session ID',
	`regdate` int(16) NOT NULL COMMENT 'registration date',
	`loggedin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if user is currently logged in, 0 if not',
	`userLevel` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'clearance level',
	`avatar_filename` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'avatar picture filename',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'userdata';

#
# Revision 460 (adding avatar support)
#
ALTER TABLE `users` ADD `avatar_filename` VARCHAR( 36 ) NOT NULL COMMENT 'avatar picture filename';

#
# Revision 491 (making root user's userlevel 4 instead of 5)
#
UPDATE `users` SET `userLevel` = 4 WHERE `userLevel` = 5;

#
# Revision 497 (adding menus table)
#
CREATE TABLE IF NOT EXISTS menus (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`header` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'menu header',
	`align` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'sidebar vertical align',
	`side` enum('left', 'right') NOT NULL DEFAULT 'left' COMMENT 'sidebar choice',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu information';

CREATE TABLE IF NOT EXISTS menu_entries (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`menu_id` int(10) NOT NULL COMMENT 'menu id (menus.id)',
	`label` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'text to show',
	`href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'link data',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'menu entry information';

#
# Revision 518 (finished implementing menu system so we can add default menu data to install script)
#
INSERT INTO menus(header, align, side) VALUES ('Main menu', 0, 'left');

INSERT INTO menu_entries(menu_id, label, href) VALUES
	(1, 'Homepage', 'index.php'),
	(1, 'Forum', 'forum.php');

#
# Revision 537 (added multiple languages support) 
#
ALTER TABLE `users` ADD `language` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'english' COMMENT 'user preferred language';

#
# Revisin 581 (making users table's username and e-mail field unique)
#
ALTER TABLE `users` ADD UNIQUE (`username`);
ALTER TABLE `users` ADD UNIQUE (`email`);

#
# Revision 584 (adding forums table)
#
CREATE TABLE IF NOT EXISTS forums (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the forum',
	`info` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'little description appearing under forum title',
	`minLevel` enum('0', '1', '2', '3') NOT NULL DEFAULT '0' COMMENT 'minimal user level to list the forum (users.userLevel)',
	`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for forums';

#
# Revision 598 (adding topics table)
#
CREATE TABLE IF NOT EXISTS topics (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`forumid` int(10) NOT NULL COMMENT 'id of the forum the topic is in (forums.id)',
	`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the topic',
	`createuser` int(10) NOT NULL COMMENT 'the ID of the user who created the topic (users.id)',
	`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
	`locked` enum('0', '1') NOT NULL DEFAULT '0' COMMENT 'whether the topic is locked (no new posts allowed): 1 - locked, 0 - not locked',
	`highlighted` enum('0', '1') NOT NULL DEFAULT '0' COMMENT 'topic is highlighted at the top of the list if value is 1',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for topics';

#
# Revision 602 (adding posts table)
#
CREATE TABLE IF NOT EXISTS posts (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`topicid` int(10) NOT NULL COMMENT 'id of the topic the post is in (topics.id)',
	`forumid` int(10) NOT NULL COMMENT 'id of the forum the topic containing the post is in (forums.id)',
	`title` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'title for the post',
	`createuser` int(10) NOT NULL COMMENT 'the ID of the user who posted the post (users.id)',
	`createdate` int(16) NOT NULL DEFAULT '0' COMMENT 'creation date',
	`content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'text of the post',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for posts';

#
# Revision 610 (adding pager dynamic splitting)
#
ALTER TABLE `users` ADD `forum_topic_count_per_page` smallint(3) NOT NULL DEFAULT '15' COMMENT 'user preference: how many topics appear on one page';

#
# Revision 613 (adding pager dynamic splitting (posts))
#
ALTER TABLE `users` ADD `forum_post_count_per_page` smallint(3) NOT NULL DEFAULT '15' COMMENT 'user preference: how many posts appear on one page';

#
# Revision 618 (adding user's post count)
#
ALTER TABLE `users` ADD `post_count` int(6) NOT NULL DEFAULT '0' COMMENT 'number of posts from the user';

#
# Revision 627 (adding the badges (achievements) system)
#
CREATE TABLE IF NOT EXISTS badges (
	`userid` int(10) NOT NULL COMMENT 'id of the user who earned the badge',
	`badgename` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'name of the badge the user earned (refers badge class badge_array)',
	`earndate` int(16) NOT NULL DEFAULT '0' COMMENT 'timestamp when the user earned the badge',
	UNIQUE KEY `userid AND badgename` (`userid`,`badgename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'badge information';

#
# Revision 649 (adding theme setting)
#
ALTER TABLE `users` ADD `theme` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'winky' COMMENT 'user preferred theme' AFTER `language`;
