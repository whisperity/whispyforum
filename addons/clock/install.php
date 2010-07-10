<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/clock/install.php
   addon telepítő/eltávolító scriptje
*/
 
 function Install() // Telepítési script
 {
	global $addons;
	
	/* Az addon telepítési pozícióját POST-ban kapjuk */
	$position = $_POST['position'];
	if ( $position == $NULL)
		$position = 0;
		
	/* Az addon telepítettségére történő ellenörzés a főkódban (admin/addons.php) már megtörtént */
	/* Addon almappa ellenörzése */
	if ( $_POST['addonsubdir'] != "clock" )
	{
		Hibauzenet("ERROR", "Érvénytelen addon almappa", "Az általad megadott addon telepítési almappája: " .$_POST['addonsubdir']. ", azonban ez az addon a <b>clock</b> almappából történő futtatást követeli meg.");
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
	}
	
	switch ( $position ) { // A pozíció alapján döntjük el, mi fusson le
		case 0:
			print("<dl>
				<dt>Az addon adatai</dt>
					<dd>Név: Óra</dd>
					<dd>Almappa: clock</dd>
					<dd>Fájlméret: " .DecodeSize(Addonmeret("clock")). "</dd>
					<dd>Szerző: <a href='mailto:whisperity@gmail.com'>whisperity</a></dd>
					<dd>Külön állítható beállítások: <span style='color: darkred'><b>nincsenek</b></span></dd>
				<dt>Leírás:</dt>
					<dd>Egy digitális órát jelenít meg</dd>
			</dl>
			<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='install_script'>
				<input type='hidden' name='addonsubdir' value='clock'>
				<input type='hidden' name='position' value=1>
				<input type='submit' value='Az addon telepítéséhez nyomd meg ezt a gombot'>
			</form>"); // Kiírjuk az addon adatait, valamint egy űrlapot a továbblépéshez
			
			break;
		case 1:
			/* Addon telepítése (sql-lekérdezések) */
			$addons->RegisterAddon("clock", "Óra", "Egy digitális órát jelenít meg", "whisperity", "whisperity@gmail.com");
			$addons->InstallModule("clock/module.php", 2);
			
			print("<br>Az addon telepítése sikeres volt. <a href='admin.php?site=addons'>Visszatérés az addonok listájához</a>"); // A felhasználó értesítése a sikeres telepítésről
			
			break;
	}
 }
 
 function Uninstall() // Eltávolítási script
 {
	global $addons; // Szükséges változók betöltése
	
	$addons->UnregisterAddon("clock");
	$addons->RemoveModule("clock/module.php");
	
	print("<br>Az addon eltávolítása sikeres volt. Kérlek, amennyiben nem szeretnéd, hogy az addon újra telepíthető legyen, távolítsd el az <span class='star'>addons/clock</span> mappát. <a href='admin.php?site=addons'>Visszatérés az addonok listájához</a>"); // A felhasználó értesítése a sikeres törlésről
 }
?>