<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/settings.php
   példaaddon beállításfájla
*/
 global $addons;
 if ( $_POST['setchange'] == "igaz" ) // Ha változtattunk az adatokon
 {
	$addons->SetCFG("sample", "peldaaddon_sajatszoveg", $_POST['sajatszoveg']);
	/* Értesítés, további kódok lefutásának megakadályozása */
	ReturnTo("Az addon beállításai sikeresen módosítva!", "admin.php?site=addons", "Vissza az addonok listájához", TRUE);
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
 }
 
 print("Példaaddon beálíltásainak módosítása:<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Saját megjelenítendő szöveg a példaaddonban:<br><textarea name='sajatszoveg' rows='10' cols='30'>" .$addons->GetCFG("sample", "peldaaddon_sajatszoveg"). "</textarea>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='settings'>
	<input type='hidden' name='id' value=" .$addonid. ">
	<input type='hidden' name='setchange' value='igaz'><br>
	<input type='submit' value='Beállítások módosítása'></p>
</form>"); // Űrlap
?>