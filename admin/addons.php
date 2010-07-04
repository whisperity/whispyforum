<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/addons.php
   addonok kezelése
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Addonok</h2></center>
<?php

if ( ($_GET['action'] == "delete") && ($_GET['id'] != $NULL) )
{
	/* Addon törlése */
	$addonsor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE id='" .$_GET['id']. "'")); // Addon adatai
	if ( file_exists("addons/" .$addonsor['subdir']. "/install.php") ) // Ha megtalálható a szerveren az addon telepítőkódja
	{
		include("addons/" .$addonsor['subdir']. "/install.php"); // Betöltjük
		Uninstall(); // És meghívjuk az törlési kódot
	} else {
		Hibauzenet("ERROR", "Az addon telepítőfájla nem található", "Az addont kézileg kell eltávolítani!"); // Hibaüzenet megjelenítése
	}
	
	/* A további kódok ne fussanak le */
	DoFooter();
	die();
}

print("Alább megtalálható a portálrendszerbe jelenleg <b>telepített</b> addonok listája. <a href=includes/help.php' onClick=\"window.open('includes/help.php?cmd=Addons-admin', 'popupwindow', 'width=800,height=600,resize=no,scrollbars=yes'); return false;\">Súgó megjelenítése</a>");

$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons");
		
		print("<table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Almappa</th>
				<th>Név</th>
				<th>Méret</th>
				<th>Szerző</th>
				<th>Leírás</th>
			</tr>");
		
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			/* Méret kiszámítása */
			$meret = 0;
			$addonfajllista = file_get_contents("addons/" .$sor['subdir']. "/files.lst");
			$fajllistasorok = explode("\r\n", $addonfajllista);
			foreach ($fajllistasorok as &$fsor)
			{
				$meret += filesize("addons/" .$sor['subdir']. "/" .$fsor);
			}
			$meret += filesize("addons/" .$sor['subdir']. "/files.lst");
			$meret += @filesize("addons/" .$sor['subdir']. "/includes.php");
			$meret += @filesize("addons/" .$sor['subdir']. "/install.php");
			
			print("<tr>
				<td>" .$sor['subdir']. "</td>
				<td>" .$sor['name']. "</td>
				<td>" .DecodeSize($meret). "</td>
				<td><a href='mailto:" .$sor['authoremail']. "'>" .$sor['author']. "</a></td>
				<td>" .$sor['descr']. "</td>
				<td><form action='/admin.php' method='GET'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Eltávolítás'>
			</form></td>
			</tr>");
		}
		
		print("</table><form action='/admin.php' method='GET'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='install'>
				<input type='submit' value='Új addon telepítése'>
			</form>");
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=addons");
}
?>