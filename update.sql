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