<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin.php
   adminisztrátori vezérlőpult
*/
 
 /* Kiválasztjuk a beérkező paraméterekből a beépítendő modult */
 global $website; // A függvényen belüli meghívódáshoz szükséges global-lá tenni a változót
 if ( $_POST['site'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$website = $_POST['site'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['site'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$website = $_GET['site'];
	} else {
		// Sehogy nem érkezett adat
		$website = $NULL;
	}
 }
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('admin.php');
 SetTitle("Adminisztrátori vezérlőpult");
 Fejlec(); // Fejléc generálása
 print("<td class='left' valign='top'>"); // Bal oldali doboz
 $session->CheckSession(session_id(), $_SERVER['REMOTE_ADDR']); // Ellenörzés, hogy a felhasználó be van-e jelentkezve
 $user->GetUserData(); // Felhasználó szintjének ellenörzése
 
 if ( $_SESSION['userLevel'] != 3) { // Ha a felhasználó nem admin, nem jelenítjük meg a menüt neki
  Hibauzenet("ERROR","Az admin menü nem érhető el","A menü használatához MINIMUM Adminisztrátori (level 3) jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel']." ).");
	DoFooter();
	die();
 }
 
 function MenuItem($modulnev, $szoveg, $tipus = 'HREF')
 {
	// Menüelem létrehozása
	// (ha az aktuálisan megnyitott modul a kívánt elem, nem csináljuk linnké, hanem egy pöttyöt (•) teszünk elé
	//  ha nem, linket készítünk)
	global $website;
	
	switch ($tipus)
	{
		case 'HREF':
			if ( $website == $modulnev )
			{
				print("<a class='menuItem'>• " .$szoveg. "</a><br>");
			} else {
				print("<a class='menuItem' href='admin.php?site=" .$modulnev. "'>" .$szoveg. "</a><br>");
			}
			
			break;
		case 'TITLE':
			print("<h3 class='postheader'><p class='header'>" .$szoveg. "</p></h3>");
			break;
	}
 }
 
 /* Admin menü linkek */
 print("<div class='menubox'>
		<span class='menutitle'><a class='menuitem' href='admin.php'>Adminisztrátori vezérlőpult</a></span><br><br>");
	
	MenuItem("", "Felhasználók", 'TITLE');
		MenuItem("banip", "IP-cím alapú kitiltások kezelése");
		MenuItem("banuser", "Felhasználók kitiltása");
	
	MenuItem("", "Weboldal beállításai", 'TITLE');
		MenuItem("configs", "Beállítások");
		MenuItem("menueditor", "Menüszerkesztő");
		MenuItem("addons", "Addonok kezelése");
	
	MenuItem("", "Naplózás", 'TITLE');
		MenuItem("log", "Webhelynapló megtekintése");
		MenuItem("installlog", "Telepítési napló megtekintése");
	
	MenuItem("", "Tartalmak kezelése", 'TITLE');
		MenuItem("addforum", "Fórum hozzáadása");
		MenuItem("plain", "Statikus tartalmak");
	
	MenuItem("", "", 'TITLE');
		MenuItem("checks", "Ellenörzés");
	
	print("
	<br><a class='menuItem' href='includes/help.php' onClick=\"window.open('includes/help.php?cmd=adminTools', 'popupwindow', 'width=800,height=600,resize=no,scrollbars=yes'); return false;\">Súgó megjelenítése</a><br>
		<a class='menuItem' href='index.php'>Visszatérés a kezdőlapra</a>
		</div>");
 
 print("</td>
 <td class='center' valign='top'>"); // Bal oldali doboz lezárása, középső doboz nyitása
 switch ( $website ) // Az érkező SITE paraméter alapján megválogatjuk a beillesztendő weboldalat
 {
	case 'menueditor':
		$admin = TRUE;
		include("admin/menueditor.php");
		break;
	case 'addforum':
		$admin = TRUE;
		include("admin/addforum.php");
		break;
	case 'log':
		$admin = TRUE;
		include("admin/log.php");
		break;
	case 'installlog':
		$admin = TRUE;
		include("admin/installlog.php");
		break;
	case 'banip':
		$admin = TRUE;
		include("admin/banip.php");
		break;
	case 'addons':
		$admin = TRUE;
		include("admin/addons.php");
		break;
	case 'checks':
		$admin = TRUE;
		include("admin/checks.php");
		break;
	case 'configs':
		$admin = TRUE;
		include("admin/configs.php");
		break;
	case 'banuser':
		print("<center><h2 class='header'>Felhasználók kitiltása</h2></center>
		<br>A felhasználók kitiltásához használd a kitiltani kívánt felhasználó profilján található segédeszközt. <a href='profile.php?id=" .$_SESSION['userID']. "'>Saját profilod megtekintése</a>");
		print("</td><td class='right' valign='top'>");
		break;
	case 'plain':
		$admin = TRUE;
		include("admin/plain.php");
		break;
	default:
		/* Telepítési információk bekérése */
		$installock = file_get_contents("install.lock");
		$instimestamp = explode("INSTALL_LOCK\nINSTALL_TS,", $installock);
		$instip = explode("\nINSTALL_IP,", $instimestamp[1]);
		/* $instip[0] = telepítési timestamp, $instip[1] = telepítési IP */
		
		print("<center><h2 class='header'>Adminisztrátori vezérlőpult</h2></center>
		<br>
		Az adminisztrátori vezérlőpultot csak <i>Adminisztrátor</i> (level 3) jogkörű felhasználók láthatják, és az adott eszközöket is csak ők használhatják.
		<br style='clear: both'><br><div class='menubox'><span class='menutitle'>Információk</span><br>
		<p class='formText'>
			<b>Motor:</b> WhispyForum<br>
			<b>Kiadás típus:</b> " .RELEASE_TYPE. "<br>
			<b>Verziószám:</b> " .VERSION. "<br>
			<b>Kiadás dátuma:</b> " .RELEASE_DATE. "<br>
			<b>Telepítés időpontja:</b> " .Datum("normal", "kisbetu", "dl", "H", "i", "s", $instip[0]). "<br>
			<b>Telepítő IP-címe:</b> " .$instip[1]);
		if ( $_SERVER['REMOTE_ADDR'] == $instip[1] )
			print(" <span style='color: darkgreen'><b>(Tied)</b></span>");
		
		print("</p></div>
			<br style='clear: both'>");
		print("</td><td class='right' valign='top'>");
		break;
 }
 Lablec(); // Lábléc
?>