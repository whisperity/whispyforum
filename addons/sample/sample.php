<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/sample.php
   példaaddon fő scriptje, includes.php-ból betöltve
*/
 function SampleAddonFunction()
 {
	// Ez egy példa function, melyet az addon modulja használ
	print("A pontos idő: " .Datum("normal", "kisbetu", "dL", "H", "i", "s")); // Használja a keretrendszer DATUM functionjét
 }
?>