<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/versions.php
   verzióinformációk
*/

	/* Verzióinformációk beállítása */
	define('RELEASE_TYPE', '/trunk'); // Kiadás típusa (dev-fejlesztői, rc-kiadásra jelölt, pre-kiadás előtti, beta, stable-stabil, ...)
	define('VERSION', ''); // Verziószám
	define('RELEASE_DATE', "folyamatos"); // Kiadás dátuma (nem unix időben)
	
	global $wf_debug;
	$wf_debug->RegisterDLEvent("Verzióinformációk bekérése megtörtént (" .RELEASE_TYPE. " " .VERSION. " [" .RELEASE_DATE. "])");
?>