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
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .$_POST['allow_registration']. "' WHERE variable='allow_registration'");
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .$_POST['log_depth']. "' WHERE variable='log_depth'");
	
	die("<div class='messagebox'>A beállítások módosítása megtörtént!<br><a href='admin.php?site=configs'>Vissza</a></td><td class='right' valign='top'>");
}
print("
<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
<p class='formText'>Regisztráció engedélyezése: <input type='radio' name='allow_registration' value='0'");
		if ( ALLOW_REGISTRATION == 0)
			print(" checked ");
		print("> Nem <input type='radio' name='allow_registration' value='1'");
			if ( ALLOW_REGISTRATION == 1 )
			print(" checked ");
		print("> Igen<br>
		Naplómélység: <input type='radio' name='log_depth' value='0'");
			if ( LOG_DEPTH == 0 )
				print(" checked ");
		print("> Kikapcsolva<br><input type='radio' name='log_depth' value='1'");
			if ( LOG_DEPTH == 1)
				print(" checked ");
		print("> Alacsony (hibaüzenetek)<br><input type='radio' name='log_depth' value='2'");
			if ( LOG_DEPTH == 2 )
				print(" checked ");
		print("> Közepes (felhasználói aktivitás)<br><input type='radio' name='log_depth' value='3'");
			if ( LOG_DEPTH == 3 )
				print(" checked ");
		print("> Mély (lapmegtekintések)<br><input type='radio' name='log_depth' value='4'");
			if ( LOG_DEPTH == 4 )
				print(" checked ");
		print("> Mélyebb (SQL-kérések)</fieldset>");
		
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