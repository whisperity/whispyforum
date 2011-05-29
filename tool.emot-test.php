<?php
 /**
 * WhispyForum tool - emoticon list
 * 
 * Lists out the available emoticons
 *
 * Even though this file is marked as a TOOL, DO NOT DELETE IT!
 * 
 * WhispyForum
 */

include('includes/safeload.php'); // We load the engine, but not the framework

$Ctemplate->useTemplate("emoticons", array(
	'SMILE1'	=>	bbDecode(":-)"),
	'SAD'	=>	bbDecode(":-("),
	'GRIN'	=>	bbDecode(":-D"),
	'TONGUE'	=>	bbDecode(":-P"),
	'WINK'	=>	bbDecode(";-)"),
	'CONFUSED'	=>	bbDecode(":-S"),
	'OHMY'	=>	bbDecode(":-O"),
	'COOL'	=>	bbDecode(":-H"),
	'CRY'	=>	bbDecode(":cry:"),
	'WAVE'	=>	bbDecode(":wave:"),
	'SPAM'	=>	bbDecode(":spam:"),
	'OFFTOPIC'	=>	bbDecode(":offtopic:")
), FALSE);

DoFooter();
?>
