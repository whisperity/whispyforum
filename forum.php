<?php
 /**
 * WhispyForum script file - forum.php
 * 
 * Listing forums and managing forum-specific alter actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/forums_head", FALSE); // Header



$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
DoFooter();
?>