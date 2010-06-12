<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/installlog.php
   telepítési napló
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Telepítési napló megtekintése</h2></center>
<?php

$data = @file_get_contents("logs/install.log"); // Fájl megnyitása
if ( $data == $NULL )
{
	print("A telepítési naplófájl nem található.");
} else {
	print("<textarea rows='25' cols='95' disabled>" .$data. "</textarea>"); // Szövegdoboz (lezárt)
}
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=installlog");
}
?>