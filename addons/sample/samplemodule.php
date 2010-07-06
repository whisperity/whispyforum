<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/samplemodule.php
   példaaddon példa modulja
*/
 global $cfg, $sq1;
 $addonconfig_sampleaddon = mysql_fetch_row($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addonsettings_sample WHERE variable='peldaaddon_sajatszoveg'"));
 $peldaaddon_sajatszoveg = $addonconfig_sampleaddon[1];
 
 print("<div class='menubox'><span class='menutitle'>Példaaddon</span><br>
	Üdvözöllek a weboldalon!<br>");
	SampleAddonFunction(); // Meghívjuk a sample.php functionjét
	print("<br>" .$peldaaddon_sajatszoveg); // Az adatbázis egy táblája tartalmazza a kívánt szöveget
 print("</div>"); // A doboz lezárása
?>