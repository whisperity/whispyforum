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
  Hibauzenet("ERROR","Az admin menü nem érhető el","A menü használatához MINIMUM Adminisztrátori (level 3) jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel'].").");
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
		MenuItem("banip", "IP-kitiltások");
		MenuItem("banuser", "Felhasználók kitiltása");
	
	MenuItem("", $cfg['pname'], 'TITLE');
		MenuItem("configs", "Beállítások");
		MenuItem("addons", "Addonok kezelése");
		MenuItem("checks", "Ellenörzés");
	
	MenuItem("", "Adatbázis", 'TITLE');
		MenuItem("dbtables", "Részletek");
		MenuItem("dboptimize", "Optimalizáció");
		MenuItem("dbbackup", "Biztonsági mentés");
	
	MenuItem("", "Naplózás", 'TITLE');
		MenuItem("installlog", "Telepítési napló megtekintése");
		MenuItem("statistics", "Statisztika");
	
	MenuItem("", "Tartalmak", 'TITLE');
		MenuItem("moduleeditor", "Modulszerkesztő");
		MenuItem("addforum", "Fórum hozzáadása");
		MenuItem("plain", "Statikus tartalmak");
		MenuItem("gallery", "Galéria");
		MenuItem("downloads", "Letöltések");
		MenuItem("polls", "Szavazások");
		MenuItem("chat", "Chat");
	
	print("
	<br><a class='menuItem' href='includes/help.php' onClick=\"window.open('includes/help.php?cmd=adminTools', 'popupwindow', 'width=800,height=600,resize=no,scrollbars=yes'); return false;\">Súgó megjelenítése</a><br>
		<a class='menuItem' href='index.php'>Visszatérés a kezdőlapra</a>
		</div>");
 
 print("</td>
 <td class='center' valign='top'>"); // Bal oldali doboz lezárása, középső doboz nyitása
 switch ( $website ) // Az érkező SITE paraméter alapján megválogatjuk a beillesztendő weboldalat
 {
	case 'banip':
		$admin = TRUE;
		include("admin/banip.php");
		break;
	case 'banuser':
		print("<center><h2 class='header'>Felhasználók kitiltása</h2></center>
		<br>A felhasználók kitiltásához használd a kitiltani kívánt felhasználó profilján található segédeszközt. <a href='profile.php?id=" .$_SESSION['userID']. "'>Saját profilod megtekintése</a>");
		print("</td><td class='right' valign='top'>");
		break;
	case 'configs':
		$admin = TRUE;
		include("admin/configs.php");
		break;
	case 'addons':
		$admin = TRUE;
		include("admin/addons.php");
		break;
	case 'checks':
		$admin = TRUE;
		include("admin/checks.php");
		break;
	case 'dbtables':
		$admin = TRUE;
		include("admin/dbtables.php");
		break;
	case 'dboptimize':
		$admin = TRUE;
		include("admin/dboptimize.php");
		break;
	case 'dbbackup':
		$admin = TRUE;
		include("admin/dbbackup.php");
		break;
	case 'installlog':
		$admin = TRUE;
		include("admin/installlog.php");
		break;
	case 'statistics':
		$admin = TRUE;
		include("admin/statistics.php");
		break;
	case 'moduleeditor':
		$admin = TRUE;
		include("admin/moduleeditor.php");
		break;
	case 'addforum':
		$admin = TRUE;
		include("admin/addforum.php");
		break;
	case 'plain':
		$admin = TRUE;
		include("admin/plain.php");
		break;
	case 'gallery':
		$admin = TRUE;
		include("admin/gallery.php");
		break;
	case 'downloads':
		$admin = TRUE;
		include("admin/downloads.php");
		break;
	case 'polls':
		$admin = TRUE;
		include("admin/polls.php");
		break;
	case 'chat':
		$admin = TRUE;
		include("admin/chat.php");
		break;
	default:
		/* Telepítési információk bekérése */
		$installock = file_get_contents("install.lock");
		$instimestamp = explode("INSTALL_LOCK\nINSTALL_TS,", $installock);
		$instip = explode("\nINSTALL_IP,", $instimestamp[1]);
		$instnaplo = explode("\nIL,", $instip[1]);
		/* A telepítési zárolófájl szétvágása
		$instip[0] = telepítési timestamp
		$instnaplo[0] = telepítő IP-címe
		$instnaplo[1] = telepítési naplófájl neve
		*/
		
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
			<b>Telepítő IP-címe:</b> " .$instnaplo[0]);
		if ( $_SERVER['REMOTE_ADDR'] == $instnaplo[0] )
			print(" <span style='color: darkgreen'><b>(Tied)</b></span>");
		
		print("</p></div>");
		
		$lastoptimizeT = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='db_lastoptimize'"));
		$lastopt = $lastoptimizeT[0];
		$lastbackupT = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='db_lastbackup'"));
		$lastbck = $lastbackupT[0];
		
		// Táblaméret kiszámítása
		$tablameret = $sql->Lekerdezes("SELECT DATA_LENGTH, INDEX_LENGTH, DATA_FREE FROM information_schema.tables WHERE TABLE_SCHEMA='" .$cfg['dbname']. "'");
		$adatmeret = 0;
		$indexmeret = 0;
		$osszmeret = 0;
		$feluliras = 0;
		
		while ( $sor = mysql_fetch_assoc($tablameret)) {
			$adatmeret = $adatmeret + $sor['DATA_LENGTH'];
			$indexmeret = $indexmeret + $sor['INDEX_LENGTH'];
			$feluliras = $feluliras + $sor['DATA_FREE'];
		}
		$osszmeret = $adatmeret + $indexmeret;
		
		print("<br style='clear: both'><div class='menubox'><span class='menutitle'>Adatbázis</span><br>
		<p class='formText'>
			<b>Típus:</b> MySQL<br>
			<b>Táblák összmérete: </b> " .DecodeSize($osszmeret). " (" .DecodeSize($adatmeret). " adat, " .DecodeSize($indexmeret). " index) <small><a href='admin.php?site=dbtables'>(részletek)</a></small><br>");
			
			if ( $feluliras > 0 ) {
				print("<span style='color: red'><b>Felülírás:</b> " .DecodeSize($feluliras). "</span><br>");
			} elseif ( $feluliras == 0 ) {
				print("<b>Felülírás:</b> " .DecodeSize($feluliras). "<br>");
			}
			print("<b>Utoljára optimalizálva:</b> " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $lastopt). " <small><a href='admin.php?site=dboptimize'>(optimalizálás)</a></small><br>
			<b>Utoljára biztonsági mentés készítve:</b> " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $lastbck). " <small><a href='admin.php?site=dbbackup'>(biztonsági mentés)</a></small><br>
			</p></div>");
			
			print("<br style='clear: both'><div class='menubox'><span class='menutitle'>Tárterület</span>
			<p class='formText'>
			<b>Meghajtó: </b> Szabad / Összes (Foglalt) [Szabad %]<br>");
			
			$osszszabad = 0;
			$osszossz = 0;
			$osszfoglalt = 0;
			for ($i = 67; $i <= 90; $i++)
			{
				$drive = chr($i);
				if (is_dir($drive.':'))
				{
					$freespace             = disk_free_space($drive.':');
					$total_space         = disk_total_space($drive.':');
					$used_space			 = $total_space - $freespace;
					$percentage_free     = $freespace ? @round($freespace / $total_space, 2) * 100 : 0;
					
					$osszszabad = $osszszabad + $freespace;
					$osszossz = $osszossz + $total_space;
					$osszfoglalt = $osszfoglalt + $used_space;
					
					print("<b>" .$drive. "</b>: 
						 " .DecodeSize($freespace). " / 
						 " .DecodeSize($total_space). "
						(" .DecodeSize($used_space). ")
						[" .$percentage_free. "%]
					<br>");
				}
			}
			
			if (is_dir('/'))
			{
				$freespace             = disk_free_space('/');
				$total_space         = disk_total_space('/');
				$used_space			 = $total_space - $freespace;
				$percentage_free     = $freespace ? @round($freespace / $total_space, 2) * 100 : 0;
				
				$osszszabad = $osszszabad + $freespace;
				$osszossz = $osszossz + $total_space;
				$osszfoglalt = $osszfoglalt + $used_space;
				
				print("<b>/</b>: 
					 " .DecodeSize($freespace). " / 
					 " .DecodeSize($total_space). "
					(" .DecodeSize($used_space). ")
					[" .$percentage_free. "%]
				<br>");
			}
			
			$osszszabadarany = $osszszabad ? @round($osszszabad / $osszossz, 2) * 100 : 0;
			
			print("<br>
				<b>Összesen:</b>
				" .DecodeSize($osszszabad). " /
				" .DecodeSize($osszossz). "
				(" .DecodeSize($osszfoglalt). ") 
				[" .$osszszabadarany. "%]
			</p></div>");
		
		// Pár kisebb statisztikai adat kiírása
		$honapok = array(  // Létrehozzuk a hónapok neveit
  1 => "Január",
  2 => "Február",
  3 => "Március",
  4 => "Április",
  5 => "Május",
  6 => "Június",
  7 => "Július",
  8 => "Augusztus",
  9 => "Szeptember",
  10 => "Október",
  11 => "November",
  12 => "December"
 );
		
		$gd = getdate(); // GetDate
		
		// Összes látogatás
		$osszes = 0;
		for ($ev = 1999; $ev <= 2020; $ev++) {
			$result = $sql->Lekerdezes("SELECT year FROM " .$cfg['tbprf']."statistics WHERE year=" .$ev);
			
			$osszes = $osszes + mysql_num_rows($result);
			mysql_free_result($result);
		}
		
		// Idei év
		$EVosszes = 0;
		$result = $sql->Lekerdezes("SELECT year FROM " .$cfg['tbprf']."statistics WHERE year=" .$gd['year']);
		$EVosszes = mysql_num_rows($result);
		mysql_free_result($result);
		
		// Aktuális hónap
		$HOosszes = 0;
		$result = $sql->Lekerdezes("SELECT year, month FROM " .$cfg['tbprf']."statistics WHERE year=" .$gd['year']. " AND month=" .$gd['mon']);
		$HOosszes = mysql_num_rows($result);
		mysql_free_result($result);
		
		// Mai nap
		$NAPosszes = 0;
		$result = $sql->Lekerdezes("SELECT year, month, day FROM " .$cfg['tbprf']."statistics WHERE year=" .$gd['year']. " AND month=" .$gd['mon']. " AND day=" .$gd['mday']);
		$NAPosszes = mysql_num_rows($result);
		mysql_free_result($result);
		
		print("<br style='clear: both'><div class='menubox'><span class='menutitle'><a href='admin.php?site=statistics'>Statisztika</a></span>
			<p class='formText'>
				<b>Eddigi összes látogatás:</b> " .$osszes. "<br>
				<br>
				<b><a href='admin.php?site=statistics&mode=months&year=" .$gd['year']. "' alt='Az idei év részletei'>Idei</a> látogatók:</b> " .$EVosszes. "<br>
				<b><a href='admin.php?site=statistics&mode=days&year=" .$gd['year']. "&month=" .$gd['mon']. "' alt='Az aktuális hónap részletei'>" .$honapok[$gd['mon']]. "i</a> látogatók:</b> " .$HOosszes. "<br>
				<b><a href='admin.php?site=statistics&mode=ip_days&year=" .$gd['year']. "&month=" .$gd['mon']. "&day=" .$gd['mday']. "' alt='A mai nap információinak megjelenítése'>Mai</a> látogatók: </b>" .$NAPosszes. "<br>
			</p></div>
			<br style='clear: both'>");
		
		print("<div class='menubox'><span class='menutitle'>Tartalmak számossága</span>
			<p class='formText'>
				<b>Addonok száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."addons")). "<br>
				<b>Modulok száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."modules")). "<br>
				<b>Menüelemek száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."menuitems")). "<br>
				<b>Fórumok száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."forum")). "<br>
				<b>Fórumtopikok száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."topics")). "<br>
				<b>Hozzászólások száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."posts")). "<br>
				<b>Hírek száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."news")). "<br>
				<b>Hírhozzászólások száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."news_comments")). "<br>
				<b>Statikus tartalmak száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."plain")). "<br>
				<b>Galériák száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."galleries")). "<br>
				<b>Feltöltött képek száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."gallery_pictures")). "<br>");
				//<b>Galériahozzászólások száma:</b> " .@mysql_num_rows(@$sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."")). "<br>
				//<b>Képhozzászólások száma:</b> " .@mysql_num_rows(@$sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."")). "<br>
				print("
				<b>Letöltés kategóriák száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."download_categ")). "<br>
				<b>Letöltések száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."downloads")). "<br>
				<b>Szavazások száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."polls")). "<br>
				<b>Szavazati lehetőségek száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."poll_opinions")). "<br>
				<b>Leadott szavazatok száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."votes_cast")). "<br>
				<b>Felhasználók száma:</b> " .mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."user")). "<br>
			</p></div>
			<br style='clear: both'>");
		print("</td><td class='right' valign='top'>");
		break;
 }
 Lablec(); // Lábléc
?>