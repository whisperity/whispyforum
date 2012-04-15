<?php
/* Individual file... just an example */
include('includes/load.php'); // Call the loader
// Now, the center box is started, creating individual content
$Ctemplate->useTemplate("wf_hello", array(
    'NAME'    =>    "Individual",
	'VERSION'	=>	"0.2",
    'CODENAME'    =>    "Tuvia"
), FALSE);
echo "<br>\n";
echo "Example wf_hello done.<br>";
echo "You should see the framework in front of you.";
// Generate the right box and the footer
DoFooter();
?>