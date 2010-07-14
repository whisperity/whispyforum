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
function Addonbeallitasok($addonid)
{
	global $cfg, $sql;
	
	/* Addon beállítások megtekintése/állítása */
	$addonsor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE id='" .$addonid. "'")); // Addon adatai
	if ( file_exists("addons/" .$addonsor['subdir']. "/settings.php") ) // Ha megtalálható a szerveren az addon beállítási fájl
	{
		include("addons/" .$addonsor['subdir']. "/settings.php"); // Betöltjük
	}
	// Egyéb esetben sokminden nem történhet, mivel ha nincs beállítási fájl, ezt a függvényt meghívó gomb sem jelenik meg
	
	/* A további kódok ne fussanak le */
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

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
	$meret += @filesize("addons/" .$addonsubdir. "/index.php");
	$meret += @filesize("addons/" .$addonsubdir. "/includes.php");
	$meret += @filesize("addons/" .$addonsubdir. "/install.php");
	$meret += @filesize("addons/" .$addonsubdir. "/settings.php");
	$meret += @filesize("addons/" .$addonsubdir. "/settings.cfg");
	
	return $meret;
}

if ( ($_GET['action'] == "delete") && ($_GET['id'] != $NULL) )
{
	/* Addon törlése */
	$addonsor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE id='" .mysql_real_escape_string($_GET['id']). "'")); // Addon adatai
	if ( file_exists("addons/" .$addonsor['subdir']. "/install.php") ) // Ha megtalálható a szerveren az addon telepítőkódja
	{
		include("addons/" .$addonsor['subdir']. "/install.php"); // Betöltjük
		Uninstall(); // És meghívjuk a törlési kódot
	} else {
		Hibauzenet("ERROR", "Az addon telepítőfájla nem található", "Az addont kézileg kell eltávolítani!"); // Hibaüzenet megjelenítése
	}
	
	/* A további kódok ne fussanak le */
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

if ( $_GET['action'] == "install")
{
	/* Új addon telepítése */
	print("Kérlek az új addon telepítése előtt győződj meg róla, hogy van-e biztonsági mentésed. Az addonokat a fejlesztők nem ellenőrzik, ezért előfordulhat, hogy kártékony kódokat tartalmaznak. Csak megfelelő óvintézkedések végrehajtása után kezdj bele egy addon telepítésébe.<br>Az addonok telepítésének kétféle módja van: az első esetben egy, már kicsomagolt és a szerverre másolt (fájl-szinten telepített) mappából a telepítőscript futtatása:<br>
	<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Kérlek írd be a telepítendő addon almappája (" .$cfg['phost']. "/addons/<i>addonmappa</i>) nevét. Ezután betöltődik az addon telepítőscriptje, mely bizonyos esetben kérhet egyéb adatokat!<br><br>
	Addon almappa neve: <input type='text' name='addonsubdir' size='50'><br>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='install_script'>
	<input type='submit' value='Telepítés'>
	</p></form>vagy egy önkicsomagoló kötegelt addonfájl (.baa fájl) feltöltése a szerverre:
	<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<p class='formText'>Tallóz be egy kötegelt addonfájlt (.baa fájl), és töltsd fel a szerverre<br><input name='baafile' type='file' size='50' accept='application/octet-stream'><br>
	<input type='submit' value='Feltöltés'>
	<input type='hidden' name='action' value='install-batch'>
	<input type='hidden' name='site' value='addons'></p>
	</form>");
	
	/* A további kódok ne fussanak le */
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

if ( ($_POST['action'] == "install-batch") ) // Feltöltött BAA fájl kicsomagolása, telepítése
{
	print("Kérlek várj, amíg az általad feltöltött fájl végrehajtása befejeződik. Ha a weboldal teljesen betöltődött (ez jelzi a sikeres kicsomagolást), alul találsz egy <b>Telepítés megkezdése</b> gombot. Rákattintva a telepítés elkezdődik.<br>"); // Bevezetőszöveg
	
	if(move_uploaded_file($_FILES['baafile']['tmp_name'], "addons/install.baa")) // Feltöltött fájl mozgatása
	{
		print("<div class='messagebox'>Fájl (<b>" .$_FILES['baafile']['name']. "</b>) sikeresen feltöltve.<br>Fájl áthelyezése: /addons/install.baa <span style='color: darkgreen'><b>sikeres</b></span><br>Fájlméret: " .DecodeSize(filesize("addons/install.baa")). "<br><br>Kicsomagolás megkezdése...<br>"); // Bevezető szöveg
		
	$baafile = file_get_contents("addons/install.baa"); // Fájl betöltése stringbe
	$elemek = explode('@@@@@@@@@@@@@@@@@@', $baafile);
	
	if ($elemek[1] != "BATCHED ADDON ARCHIVE//////////////////\r\n") // Ha a fejléc nem stimmel
	{ // Hibaüzenet megjelenítése és telepítés leállítása
		Hibauzenet("ERROR", "Érvénytelen fájl", "A feltöltött fájl egy érvénytelen kötegelt addonfájl (.baa)<br>A fejléc-információk hamisak, vagy sérültek.");
		unlink("addons/install.baa"); // Ideiglenes fájl törlése
		print("<br>/addons/install.baa ideiglenes fájl törölve<br><span style='color: red; font-weight: bold'>telepítés sikertelen: érvénytelen fájl</span><br><a href='admin.php?site=addons'>Visszatérés az addon-listához</a></div>"); // Értesítés, kicsomagolás befejezése
		
		/* A további kódok ne fussanak le */
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
	}
	$mappa = explode("//////////////////\r\n", $elemek[2]);
	
	/* if ( is_dir("addons/" .$mappa[0]))
	{
		// Ha már létezik egy ilyen mappa, hibaüzenet megjelenítése és telepítés leállítása
		Hibauzenet("ERROR", "Már létező mappa", "Az addon telepítéshez szükséges mappája már létrehozásra került");
		unlink("addons/install.baa"); // Ideiglenes fájl törlése
		print("<br>/addons/install.baa ideiglenes fájl törölve<br><span style='color: red; font-weight: bold'>telepítés sikertelen: már létező célmappa</span><br><a href='admin.php?site=addons'>Visszatérés az addon-listához</a></div>"); // Értesítés, kicsomagolás befejezése
		/* A további kódok ne fussanak le */ /*
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
	} else { */
		@mkdir("addons/" .$mappa[0]); // Addon-mappa létrehozása
		print("Addonmappa (" .$mappa[0]. ") létrehozva<br>");
	//}
	$meret = 0; // 0 bájt adat került létrehozásra
	foreach ($elemek as &$elem )
	{
		$fajladat = explode("//////////////////\r\n", $elem);
		$cel = "addons/" .$mappa[0]. "/" .$fajladat[0];
		
		if ( ( $fajladat[0] != "BATCHED ADDON ARCHIVE" ) && ( $fajladat[0] != $mappa[0] ) && ( $fajladat[0] != $NULL) ) 
		{
			// Technikai okokból ellenőrizni kell
			// Ha a fájl neve nem a legelső sor (fejléc), vagy a második sor (mappa neve), akkor, és csakis akkor fájl létrehozása
			
			file_put_contents($cel, $fajladat[1]); // Létrehozás
			print("Fájl kicsomagolva: <b>" .$cel. "</b>, fájlméret: " .DecodeSize(filesize($cel)). " <br>"); // Kiírás
			$meret += filesize($cel); // Méret növelése
		}
	}
	print("Kicsomagolás befejezve<br>");
	
	unlink("addons/install.baa"); // Ideiglenes fájl törlése
	print("<br>/addons/install.baa ideiglenes fájl törölve<br>" .DecodeSize($meret). " tárterületnyi fájl került létrehozásra</div><br>
	<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
	<input type='hidden' name='addonsubdir' value='" .$mappa[0]. "'>
	<input type='hidden' name='site' value='addons'>
	<input type='hidden' name='action' value='install_script'>
	<input type='submit' value='Telepítés megkezdése'>
	</form>"); // Értesítés, kicsomagolás befejezése, telepítés megkezdésére mutató gomb készítése
	}
	
	/* A további kódok ne fussanak le */
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

if ( ($_POST['action'] == "install_script") && ( $_POST['addonsubdir'] != $NULL) )
{
	/* Addon telepítése */
	print("<h3 class='header'><p class='header'>Addon telepítése: <span class='star'>/addons/" .$_POST['addonsubdir']. "</span></p></h3>");
	
	/* Megnézzük, hogy ez az addon telepítve van-e */
	$addonsor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE subdir='" .mysql_real_escape_string($_POST['addonsubdir']). "'"));
	if ( $addonsor['subdir'] != $NULL )
	{
		Hibauzenet("ERROR", "Ez az addon már telepítésre került");
		/* A további kódok ne fussanak le */
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
	}
	
	if ( file_exists("addons/" .$_POST['addonsubdir']. "/install.php") ) // Ha megtalálható a szerveren az addon telepítőkódja
	{
		include("addons/" .$_POST['addonsubdir']. "/install.php"); // Betöltjük
		Install(); // Telepítőkód meghívása
	} else {
		Hibauzenet("ERROR", "Az addon telepítőfájla nem található", "Az addont kézileg kell telepíteni!"); // Hibaüzenet megjelenítése
	}
	
	/* A további kódok ne fussanak le */
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

/* Addon beállítások meghívása */
if ( ($_GET['action'] == "settings") && ($_GET['id'] != $NULL) ) // GET-tel érkező ID esetén (addon lista)
	AddonBeallitasok($_GET['id']);
if ( ($_POST['action'] == "settings") && ($_POST['id'] != $NULL) ) // POST-tal érkező ID esetén (addon beállítások weboldal)
	AddonBeallitasok($_POST['id']);

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
				<td>" .$sor['descr']. "</td>");
			
			if ( file_exists("addons/" .$sor['subdir']. "/settings.php") ) 
			{
				// Ha vannak az addonnak beállításai, megjelenítjük a hozzá tartozó gombot
				print("<td><form action='/admin.php' method='GET'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='settings'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Beállítások'>
			</form></td>");
			} else {
				print("<td></td>");
			}
			
			print("<td><form action='/admin.php' method='GET'>
				<input type='hidden' name='site' value='addons'>
				<input type='hidden' name='action' value='delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Eltávolítás'>
			</form></td></tr>");
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