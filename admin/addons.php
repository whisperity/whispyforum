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

function Addonmeret($addonsubdir)
{
	/* Méret kiszámítása */
	$meret = 0;
	$addonfajllista = file_get_contents("addons/" .$addonsubdir. "/files.lst");
	$fajllistasorok = explode("\r\n", $addonfajllista);
	foreach ($fajllistasorok as &$fsor)
	{
		$meret += filesize("addons/" .$addonsubdir. "/" .$fsor);
	}
	$meret += filesize("addons/" .$addonsubdir. "/files.lst");
	$meret += @filesize("addons/" .$addonsubdir. "/includes.php");
	$meret += @filesize("addons/" .$addonsubdir. "/install.php");
	
	return $meret;
}

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
	die("</td><td class='right' valign='top'>");
}
if ( $_GET['action'] == "install")
{
	/* Új addon telepítése */
	print("Kérlek az új addon telepítése előtt győződj meg róla, hogy van-e biztonsági mentésed. Az addonokat a fejlesztők nem ellenőrzik, ezért előfordulhat, hogy kártékony kódokat tartalmaznak. Csak megfelelő óvintézkedések végrehajtása után kezdj bele egy addon telepítésébe.<br>
	<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Kérlek írd be a telepítendő addon almappája (" .$cfg['phost']. "/addons/<i>addonmappa</i>) nevét. Ezután betöltődik az addon telepítőscriptje, mely bizonyos esetben kérhet egyéb adatokat!<br><br>
	Addon almappa neve: <input type='text' name='addonsubdir' size='50'><br>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='install_script'>
	<input type='submit' value='Telepítés'>
	</p></form>");
	
	/* A további kódok ne fussanak le */
	die("</td><td class='right' valign='top'>");
}
if ( ($_POST['action'] == "install_script") && ( $_POST['addonsubdir'] != $NULL) )
{
	/* Addon telepítése */
	print("<h3 class='header'><p class='header'>Addon telepítése: <span class='star'>/addons/" .$_POST['addonsubdir']. "</span></p></h3>");
	
	/* Megnézzük, hogy ez az addon telepítve van-e */
	$addonsor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE subdir='" .$_POST['addonsubdir']. "'"));
	if ( $addonsor['subdir'] != $NULL )
	{
		Hibauzenet("ERROR", "Ez az addon már telepítésre került");
		/* A további kódok ne fussanak le */
		die("</td><td class='right' valign='top'>");
	}
	
	if ( file_exists("addons/" .$_POST['addonsubdir']. "/install.php") ) // Ha megtalálható a szerveren az addon telepítőkódja
	{
		include("addons/" .$_POST['addonsubdir']. "/install.php"); // Betöltjük
		Install(); // Telepítőkód meghívása
	} else {
		Hibauzenet("ERROR", "Az addon telepítőfájla nem található", "Az addont kézileg kell telepíteni!"); // Hibaüzenet megjelenítése
	}
	
	/* A további kódok ne fussanak le */
	die("</td><td class='right' valign='top'>");
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
		
		$vanAddon = 0; // Alapból nem található addon
		
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$vanAddon = 1; // Van legalább 1 addon
			
			print("<tr>
				<td>" .$sor['subdir']. "</td>
				<td>" .$sor['name']. "</td>
				<td>" .DecodeSize(Addonmeret($sor['subdir'])). "</td>
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

		if ( $vanAddon == 0) // Ha nincs egy addon se telepítve, értesítést jelenítünk meg
			print("</table><table border='0' cellspacing='1' cellpadding='1'><tr><td><h3 class='postheader'><p class='header'>Nem található telepített addon</p></h3></td></tr>");
		
		
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