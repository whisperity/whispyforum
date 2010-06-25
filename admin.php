<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin.php
   adminisztrációs vezérlőpanel
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
	die();
 }
 
 function MenuItem($modulnev, $szoveg)
 {
	// Menüelem létrehozása
	// (ha az aktuálisan megnyitott modul a kívánt elem, nem csináljuk linnké, hanem egy pöttyöt (•) teszünk elé
	//  ha nem, linket készítünk)
	global $website;
	
	if ( $website == $modulnev )
	{
		print("<a class='menuItem'>• " .$szoveg. "</a><br>");
	} else {
		print("<a class='menuItem' href='admin.php?site=" .$modulnev. "'>" .$szoveg. "</a><br>");
	}
 }
 
 /* Admin menü linkek */
 print("<div class='menubox'>
		<span class='menutitle'><a class='menuitem' href='admin.php'>Adminisztrátori vezérlőpult</a></span>
		
		<h3 class='postheader'><p class='header'>Fórum</p></h3>");
	MenuItem("addforum", "Fórum hozzáadása");
		
		print("<h3 class='postheader'><p class='header'>Naplózás</p></h3>");
	MenuItem("log", "Webhelynapló megtekintése");
	MenuItem("installlog", "Telepítési napló megtekintése");
	print("
	<br><a class='menuItem' href='includes/help.php' onClick=\"window.open('includes/help.php?cmd=adminTools', 'popupwindow', 'width=800,height=600,resize=no,scrollbars=yes'); return false;\">Súgó megjelenítése</a><br>
		<a class='menuItem' href='index.php'>Visszatérés a kezdőlapra</a>
		</div>");
 
 print("</td>
 <td class='center' valign='top'>"); // Bal oldali doboz lezárása, középső doboz nyitása
 switch ( $website ) // Az érkező SITE paraméter alapján megválogatjuk a beillesztendő weboldalat
 {
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
	default:
		print("<center><h2 class='header'>Adminisztrátori vezérlőpult</h2></center>
		<br>
		Üdvözöllek az adminisztrátori vezérlőpultban.<br>Ez az a hely, ahol a portálrendszer vezetői, az adminisztrátorok elérhetnek bizonyos, csak az ő jogkörükkel elérhető eszközt. Kérlek, válassz a baloldali menüből.</div><div class='rightbox'></div>");
		print("</td><td class='right' valign='top'>");
		break;
 }
 Lablec(); // Lábléc
?>