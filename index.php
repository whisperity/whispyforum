<?php
/**
 * WhispyForum placeholder index file
 */

//include("includes/load.php"); // Load webpage
define('WHISPYFORUM', TRUE);
require("config.php");
require("includes/functions.php");
require("includes/mysql.php");
require("includes/module.php");
require("includes/template.php");
require("includes/user.php");

global $template, $sql, $user;
$template = new template();
$sql = new mysql( $cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'], $cfg['dbname'] );
$user = new user(0, FALSE);




prettyVar($user);
prettyVar($sql);
prettyVar($template);

unset($user);
unset($sql);
unset($template);
//DoFooter();
?>
