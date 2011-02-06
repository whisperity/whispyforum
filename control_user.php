<?php
 /**
 * WhispyForum script file - control_user.php
 * 
 * User control panel. Usage: help individuals set user-specific properties.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage

$Ctemplate->useStaticTemplate("user/cp_head", FALSE);
echo '</td></tr></table>';

DoFooter();
?>