<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* /addons/sample/install.php
   addon telepítő/eltávolító scriptje
*/
 //$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."addons(subdir, name, descr, author, authoremail) VALUES ('sample', 'Példa addon', 'Példa addon az addonok működésének bemutatására', 'whisperity', 'whisperity@gmail.com')");
 
 function Uninstall() // Eltávolítási script
 {
	global $cfg, $sql; // Szükséges változók betöltése
	
	$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."modules WHERE name='sample/samplemodule.php'"); // Modul eltávolítása
	$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."addons WHERE subdir='sample'"); // Addon eltávolítása
	
	print("<br>Az addon eltávolítása sikeres volt. Kérlek, amennyiben nem szeretnéd, hogy az addon újra telepíthető legyen, távolítsd el az <span class='star'>addons/sample</span> mappát. <a href='admin.php?site=addons'>Visszatérés az addonok listájához</a>"); // A felhasználó értesítése a sikeres törlésről
 }
?>