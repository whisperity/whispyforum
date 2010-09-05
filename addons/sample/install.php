<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/install.php
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
	if ( $_POST['addonsubdir'] != "sample" )
	{
		Hibauzenet("ERROR", "Érvénytelen addon almappa", "Az általad megadott addon telepítési almappája: " .$_POST['addonsubdir']. ", azonban ez az addon a <b>sample</b> almappából történő futtatást követeli meg.");
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
	}
	
	switch ( $position ) { // A pozíció alapján döntjük el, mi fusson le
		case 0:
			print("<dl>
				<dt>Az addon adatai</dt>
					<dd>Név: Példaaddon</dd>
					<dd>Almappa: sample</dd>
					<dd>Fájlméret: " .DecodeSize(Addonmeret("sample")). "</dd>
					<dd>Szerző: <a href='mailto:whisperity@gmail.com'>whisperity</a></dd>
					<dd>Külön állítható beállítások: <span style='color: darkgreen'><b>vannak</b></span></dd>
				<dt>Leírás:</dt>
					<dd>Példa addon az addonok működésének bemutatására</dd>
			</dl>
			<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='install_script'>
				<input type='hidden' name='addonsubdir' value='sample'>
				<input type='hidden' name='position' value=1>
				<input type='submit' value='Az addon telepítéséhez nyomd meg ezt a gombot'>
			</form>"); // Kiírjuk az addon adatait, valamint egy űrlapot a továbblépéshez
			
			break;
		case 1:
			/* Addon telepítése (sql-lekérdezések) */
			
			$addons->CreateAddonTable("sample");
			$addons->AddCFG("sample", "peldaaddon_sajatszoveg", "Hello World!");
			
			$addons->RegisterAddon("sample", "Példa addon", "Példa addon az addonok működésének bemutatására", "whisperity", "whisperity@gmail.com");
			$addons->InstallModule("sample/samplemodule.php", 2);
			
			print("<br>Az addon telepítése sikeres volt. <a href='admin.php?site=addons'>Visszatérés az addonok listájához</a>"); // A felhasználó értesítése a sikeres telepítésről
			
			break;
	}
 }
 
 function Uninstall() // Eltávolítási script
 {
	global $addons; // Szükséges változók betöltése
	
	print("Az addon törlésével a beállítások is törlődtek!");
	
	$addons->RemoveAddonTable("sample");
	$addons->UnregisterAddon("sample");
	$addons->RemoveModule("sample/samplemodule.php");
	
	print("<br>Az addon eltávolítása sikeres volt. Kérlek, amennyiben nem szeretnéd, hogy az addon újra telepíthető legyen, távolítsd el az <span class='star'>addons/sample</span> mappát. <a href='admin.php?site=addons'>Visszatérés az addonok listájához</a>"); // A felhasználó értesítése a sikeres törlésről
 }
?>