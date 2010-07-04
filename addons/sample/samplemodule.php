<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/samplemodule.php
   példaaddon példa modulja
*/
 include("settings.cfg"); // Addon konfigurációs fájl betöltése (szükséges)
 
 print("<div class='menubox'><span class='menutitle'>Példaaddon</span><br>
	Üdvözöllek a weboldalon!<br>");
	SampleAddonFunction(); // Meghívjuk a sample.php functionjét
	print("<br>" .$peldaaddon_sajatszoveg); // addons/sample/settings.cfg -vel létrehozott változó tartalmazza a kívánt szöveget
 print("</div>"); // A doboz lezárása
?>