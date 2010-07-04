<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/settings.php
   példaaddon beállításfájla
*/
 include_once('addons/sample/settings.cfg'); // Szükséges az aktuális beálltások megtekintéséhez
 
 if ( $_POST['setchange'] == "igaz" ) // Ha változtattunk az adatokon
 {
	file_put_contents("addons/sample/settings.cfg", "<?php\r\n\$peldaaddon_sajatszoveg = '" .$_POST['sajatszoveg']. "';\r\n?>"); // Fájl írása
	/* Értesítés, további kódok lefutásának megakadályozása */
	print("<div class='messagebox'>Addon beállításai sikeresen módosítva!<br><a href='admin.php?site=addons'>Visszatérés az addonok listájához</a></div>");
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
 }
 
 print("Példaaddon beálíltásainak módosítása:<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Saját megjelenítendő szöveg a példaaddonban:<br><textarea rows='10' cols='30'>" .$peldaaddon_sajatszoveg. "</textarea>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='settings'>
	<input type='hidden' name='id' value=" .$addonid. ">
	<input type='hidden' name='setchange' value='igaz'><br>
	<input type='submit' value='Beállítások módosítása'></p>
</form>"); // Űrlap
?>