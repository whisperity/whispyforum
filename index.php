<?php
/**
 * WhispyForum placeholder index file
 */

include("includes/load.php"); // Load webpage

$Ctemplate->useTemplate("example_template", array(
	'TITLE'	=>	"Page title", // Head title and center title of the page
	'SITENAME'	=>	"WhispyForum branch checkout", // Example sitename
	'WORKING_COLOR'	=>	"#49A835",	// The color of the example template's word "working"
	'WEIGHT'	=>	"bold" // font-weight of the "working" word. (normal/bold)
	), FALSE ); // Use the example template file
?>
