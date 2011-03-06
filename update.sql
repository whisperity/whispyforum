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
	`regdate` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'registration date',
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
	`side` ENUM('left', 'right') NOT NULL DEFAULT 'left' COMMENT 'sidebar choice',
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

INSERT INTO menu_entries(menu_id, label, href) VALUES (1, 'Homepage', 'index.php');


#
#
#
# FREE UNIVERSITY ORGANIZER SYSTEM
#
#
#

#
# adding class to users
#
ALTER TABLE `users` ADD `osztaly` VARCHAR( 10 ) NOT NULL COMMENT 'class of the user';

#
# performers table
#
CREATE TABLE IF NOT EXISTS fu_performers (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`pName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'name of performer',
	`email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'contact (e-mail address)',
	`telephone` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'contact (telephone number)',
	`comments` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`status` enum('unallocated', 'pending', 'agreed', 'refused') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'unallocated' COMMENT 'performer status',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'data for performers';

#
# relation table between users and performers
#
CREATE TABLE IF NOT EXISTS fu_perf_user_relation (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing relation ID',
	`user_id` int(10) NOT NULL COMMENT 'user id (users.id)',
	`performer_id` int(10) NOT NULL COMMENT 'performer id (fu_performers.id)',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'relational table between users and performers';

#
# Free Univeristy Phase 2
#

#
# lectures table
#
CREATE TABLE IF NOT EXISTS fu2_lectures (
	`id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto increasing ID',
	`lecture_name` varchar(255) NOT NULL COMMENT 'name of the lecture',
	`lecturer` varchar(255) NOT NULL COMMENT 'name of the lecturer',
	`hour1` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #1',
	`hour2` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #2',
	`hour3` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #3',
	`hour4` enum('yes', 'no') NOT NULL DEFAULT 'no' COMMENT 'lecture takes place in hour #4',
	`limit1` int(3) NULL COMMENT 'student limit for hour #1',
	`limit2` int(3) NULL COMMENT 'student limit for hour #2',
	`limit3` int(3) NULL COMMENT 'student limit for hour #3',
	`limit4` int(3) NULL COMMENT 'student limit for hour #4',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT 'lecture information';

#
# Relation between users and fu2_lectures
# for relating user to it's selected lecture
#
ALTER TABLE `users` ADD `hour1` INT(10) NULL COMMENT 'lecture id for hour #1 (fu2_lectures.id)';
ALTER TABLE `users` ADD `hour2` INT(10) NULL COMMENT 'lecture id for hour #2 (fu2_lectures.id)';
ALTER TABLE `users` ADD `hour3` INT(10) NULL COMMENT 'lecture id for hour #3 (fu2_lectures.id)';
ALTER TABLE `users` ADD `hour4` INT(10) NULL COMMENT 'lecture id for hour #4 (fu2_lectures.id)';