<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/samplemodule.php
   példaaddon példa modulja
*/
 global $addons;
 
 print("<div class='menubox'><span class='menutitle'>Példaaddon</span><br>
	Üdvözöllek a weboldalon!<br>");
	SampleAddonFunction(); // Meghívjuk a sample.php functionjét
	print("<br>" .$addons->GetCFG("sample", "peldaaddon_sajatszoveg")); // Az adatbázis egy táblája tartalmazza a kívánt szöveget
 print("</div>"); // A doboz lezárása
?>