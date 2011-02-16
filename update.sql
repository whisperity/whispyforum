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
# If you have a databse installed with
# revision 98, and your current files requir
# revision 250, you must apply every update query
# like revision 105, revision 140, revision 180
# revision 198, revision 202, etc.

# Revision 460 (adding avatar support)
ALTER TABLE `users` ADD `avatar_filename` VARCHAR( 36 ) NOT NULL COMMENT 'avatar picture filename';

# Revision 491 (making root user's userlevel 4 instead of 5)
UPDATE `users` SET `userLevel` = 4 WHERE `userLevel` = 5;

# Revision 497 (adding menus table)
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