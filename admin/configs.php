<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/configs.php
   a portálrendszer konfigurációjának állítása
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Beállítások</h2></center>
<?php

if ( $_POST['cmd'] == "setup" )
{
	global $cfg, $sql;
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .mysql_real_escape_string($_POST['allow_registration']). "' WHERE variable='allow_registration'");
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .mysql_real_escape_string($_POST['log_depth']). "' WHERE variable='log_depth'");
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .mysql_real_escape_string($_POST['facebook_like']). "' WHERE variable='facebook_like'");
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .mysql_real_escape_string($_POST['download_minlvl']). "' WHERE variable='download_minlvl'");
	
	ReturnTo("A beállítások módosítása megtörtént!", "admin.php?site=configs", "Vissza", TRUE);
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}
print("Itt a portálrendszer beállításait tudod módosítani. Az addonok beállításaihoz lásd az addon menüt.
<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
<p class='formText'>Regisztráció engedélyezése: <input type='radio' name='allow_registration' value='0'");
		if ( ALLOW_REGISTRATION == 0)
			print(" checked ");
		print("> Nem <input type='radio' name='allow_registration' value='1'");
			if ( ALLOW_REGISTRATION == 1 )
			print(" checked ");
		print("> Igen");
		
print("<br>
	Hibakeresési információk megjelenítése: ");
		
		if ( SHOWDEBUG == 0 )
		{
			print("Nem");
		} else if ( SHOWDEBUG == 1 ) {
			print("Igen");
		}
	
	print(" <a class='feature-extra'><span class='hover'><span class='h3'>Hibakeresés informcáiók</span>A hibakeresési információk ki- vagy bekapcsolásához lásd a <b>/debug.php</b> fájlt a szerveren. A módosításhoz szükséges lépések oda vannak leírva.</span><sup>?</sup></a>");
	print("<br>Facebook tetszik gomb (Like button): <input type='radio' name='facebook_like' value='0'");
		if ( FACEBOOK_LIKE == 0)
			print(" checked ");
		print("> Letiltva <input type='radio' name='facebook_like' value='1'");
			if ( FACEBOOK_LIKE == 1 )
			print(" checked ");
		print("> Engedélyezve");
	
	print("<br>
	Google Analytics követés: ");
		
		if ( GOOGLE_ANALYTICS == 0 )
		{
			print("Kikapcsolva");
		} else if ( GOOGLE_ANALYTICS == 1 ) {
			print("Bekapcsolva<br>Google Analytics követőkód: " .GOOGLE_ANALYTICS_ID);
		}
	
	print(" <a class='feature-extra'><span class='hover'><span class='h3'>Google Analytics követés</span>A Google Analytics ki- vagy bekapcsolásához lásd az <b>/analytics.php</b> fájlt a szerveren. A követőkód módosításához szükséges lépések oda vannak leírva.</span><sup>?</sup></a>");
	
	print("<br>A <a href='download.php'>Letöltések</a> megtekintéséhez szükséges minimális szint: <input type='radio' name='download_minlvl' value='0'");
		if ( DOWNLOAD_MINLVL == 0)
			print(" checked ");
		print("> Midenki (nincs bejelentkezve - level 0) <input type='radio' name='download_minlvl' value='1'");
			if ( DOWNLOAD_MINLVL == 1 )
			print(" checked ");
		print("> Felhasználó (level 1)");
	
	print("</p><input type='hidden' name='cmd' value='setup'>
<input type='hidden' name='site' value='configs'>
<input type='submit' value='Beállítások módosítása'>
</form>"); // Információ, űrlap

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=configs");
}
?>