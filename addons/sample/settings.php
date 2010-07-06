<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/settings.php
   példaaddon beállításfájla
*/
 global $cfg, $sql;
 if ( $_POST['setchange'] == "igaz" ) // Ha változtattunk az adatokon
 {
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."addonsettings_sample SET value='" .$_POST['sajatszoveg']. "' WHERE variable='peldaaddon_sajatszoveg'");
	/* Értesítés, további kódok lefutásának megakadályozása */
	print("<div class='messagebox'>Addon beállításai sikeresen módosítva!<br><a href='admin.php?site=addons'>Visszatérés az addonok listájához</a></div>");
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
 }
 
 $addonconfig_sampleaddon = mysql_fetch_row($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addonsettings_sample WHERE variable='peldaaddon_sajatszoveg'"));
 $peldaaddon_sajatszoveg = $addonconfig_sampleaddon[1];
 print("Példaaddon beálíltásainak módosítása:<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Saját megjelenítendő szöveg a példaaddonban:<br><textarea name='sajatszoveg' rows='10' cols='30'>" .$peldaaddon_sajatszoveg. "</textarea>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='settings'>
	<input type='hidden' name='id' value=" .$addonid. ">
	<input type='hidden' name='setchange' value='igaz'><br>
	<input type='submit' value='Beállítások módosítása'></p>
</form>"); // Űrlap
?>