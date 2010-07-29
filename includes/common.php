<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/common.php
   minden weboldalról meghívandó betöltőscript
*/

function Fejlec()
{
	global $wf_debug;
	// Fejléc létrehozása
	print("<div class='headerbox'>"); // Blokknyitás
	$wf_debug->RegisterDLEvent("Fejléc létrehozása");
	
	// KÓD IDE //
	print("</div>"); // Blokkzárás
	
	/* A footer elcsúszás kiiktatása végett egy láthatatlan táblázatba kerül a középső rész */
	print("<table class='centerdiv'><tr>");
}

function FacebookLikeButton() // Facebook LIKE gomb megjelenítése
{
	global $cfg, $wf_debug;
	if ( FACEBOOK_LIKE == 1) // Megjelenítés csak akkor, ha ezt az admin menüből bekapcsoltuk
	{
		echo '<iframe src="http://www.facebook.com/widgets/like.php?href='; // Facebook like iFrame bevezetése
		echo "http://".$cfg['phost'].$_SERVER['REQUEST_URI']; // A like-oláshoz szükséges cím (http:// + domain (phost) + aktuális lap címe)
		echo '" scrolling="no" frameborder="0" style="border: none; width: 400px; height: 35px"></iframe>'; // Iframe zárása
		
		$wf_debug->RegisterDLEvent("Facebook LIKE button generálva");
	}
}

function GoogleAnalytics() // Google Analytics kód
{
	global $wf_debug;
	if ( GOOGLE_ANALYTICS == 1) // Megjelenítés csak akkor, ha ezt bekapcsoltuk
	{
		if ( GOOGLE_ALANYTICS_ID != $NULL ) // Megjelenítés csak akkor, ha van megadott Google Analytics ID
		{
			GenerateGoogleAnalyticsJS(); // Meghívjuk a másik fájlban lévő függvényt
			
			$wf_debug->RegisterDLEvent("Google Analytics JavaScript létrehozva, követési adatok elküldve");
		}
	}
}
function Lablec()
{
	global $cfg, $sql, $wf_debug;
	
	/* A footer elcsúszást védelem vége */
	print("</td></tr></table>");
	
	// Lábléc létrehozása
	print("<div class='footerbox'>"); // Blokknyitás
	$wf_debug->RegisterDLEvent("Lábléc létrehozva");
	/* Generálás vége, generálási idő kiszámítása */
	global $start_time;
	
    $mtime = microtime();
	$mtime = explode(' ',$mtime);
    $current_time = $mtime[1] + $mtime[0];
	
	$genIdo = substr(($current_time - $start_time), 0, 5);
	
	print("WhispyFórum " .RELEASE_TYPE. " " .VERSION. " • Kiadás dátuma: " .RELEASE_DATE. " • <a href='mailto:" .$cfg['webmaster_email']. "'>" .$cfg['webmaster']. "</a> • Az oldal generálása " .$genIdo. " másodpercig tartott<br>");
	FacebookLikeButton(); // Facebook LIKE gomb generálása
	GoogleAnalytics(); // Google Analytics kód generálása
	print("</div>"); // Blokkzárás
	
	$sql->Disconnect(); // Adatbáziskapcsolat lezárása
	$wf_debug->RegisterDLEvent("Oldal generálása befejezve");
	$wf_debug->GenerateFooterInf(); // Debug információk megjelnítése
}

function Inicialize ( $pagename )
{
 /* Generálás kezdete, idő eltárolása */
 global $start_time;
 $mtime = microtime();
 $mtime = explode(' ',$mtime);
 $start_time = $mtime[1] + $mtime[0];
 
 session_start(); // Elindítjuk a munkafolyamatot
 
 /* SZÜKSÉGES FÁJLOK BETÖLTÉSE */
 require('config.php'); // Konfigurációs állomány betöltése
 require('debug.php'); // Hibakeresési funkciót engedélyező/tiltó állomány betöltése
 include('analytics.php'); // Google analytics leírókód
 
 // Funkciótárak és osztályok betöltése
 require('includes/debug.php'); // Hibakereső
 require('includes/functions.php'); // Funkciótár
 require('includes/mysql.php'); // MySQL kezelési osztály ($sql)
 require('includes/user.php'); // Felhasználó és munkamenetfolyamat (session) kezelési osztály
 require('includes/sendmail.php'); // Levélküldési osztály
 require('includes/templates.php'); // Modulkezelő
 
 // Leírófájlok
 require('includes/versions.php'); // Verzióinformációk
 $wf_debug->RegisterDLEvent("Szükséges fájlok betöltve");
 
 // Témafájl betöltése
 print("<link rel='stylesheet' type='text/css' href='themes/" .$_SESSION['themeName']. "/style.css'>
");
 if ( $_SESSION['themeName'] == $NULL )
	print("<link rel='stylesheet' type='text/css' href='themes/default/style.css'>
");
 /* */
 
 /* Telepítettség ellenörzése */
 if ( !file_exists('install.lock') )
	Hibauzenet("BAN", "A portálrendszer nincs telepítve", "Kérlek futtatsd a telepítőscriptet <a href='install.php'>innen</a>!", __FILE__, __LINE__);
 
 /* INICIALIZÁLÁS */
 $sql->Connect(); // Csatlakozás az adatbázisszerverhez
 CheckIfIPBanned(); // Megnézzük, hogy a felhasználó IP-címe bannolva van-e
 $user->GetUserData(); // Felhasználó adatainak frissítése
 
  /* Portálmotor-beállítások bekérése */
 $siteconfig_allowReg = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='allow_registration'")); // Regisztráció engedélyezése
 $siteconfig_fbLike = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='facebook_like'")); // Facebook like gomb
 /* Bekért adatok mentése a portálrendszer számára 
	Itt maradunk a hagyományos DEFINE metódusnál, hogy ne kelljen az egész rendszerben a jelen változókat ellenörző
	sorokat átkódolni. */
 define('ALLOW_REGISTRATION', $siteconfig_allowReg[0]);
 define('FACEBOOK_LIKE', $siteconfig_fbLike[0]);
 $wf_debug->RegisterDLEvent("Rendszerváltozók bekérve az adatbázisból");
 
 /* Verzióadatok elleörzése */
 $adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."version"), MYSQL_ASSOC);
 if ( ($adat["RELEASE_TYPE"] != RELEASE_TYPE) || ($adat["VERSION"] != VERSION) || ($adat["RELEASE_DATE"] != RELEASE_DATE) )
	Hibauzenet("CRITICAL", "A futó verzió nem egyezik a telepített verzióval", "Futó verzió: <b>" .RELEASE_TYPE. " " .VERSION. " (" .RELEASE_DATE. ")</b><br>Telepített verzió: <b>" .$adat['RELEASE_TYPE']. " " .$adat['VERSION']. " (" .$adat['RELEASE_DATE']. ")</b><br>Bővebb információ: 
	<a href='' onClick=\"window.open('includes/help.php?cmd=Update', 'popupwindow', 'width=570,height=320'); return false;\">kattints ide</a>");
	

 if ($pagename != "admin.php") // Az admin.php-n ezeknek NEM kell megjelenniük
 {
	$addons->LoadAddons(); // Addonok betöltése
	Fejlec(); // Fejléc
	
	print("<td class='left' valign='top'>"); // Bal odali doboz
	$user->CheckIfLoggedIn($_SESSION['username']); // Megnézzük, hogy belépett-e már a user
	
	$templates->DoLeft(); // Bal oldali modulok
	
	print("</td>
    <td class='center' valign='top'>"); // Középső doboz
	
 }
}

function DoFooter() // Középső rész elküldése után
{
 print("</td><td class='right' valign='top'>"); // Jobb oldali doboz
 
 global $templates;
 $templates->DoRight(); // Jobb oldali modulok
 
 Lablec(); // Lábléc
}

function SetTitle( $fejlec ) // HTML fejléc létrehozása
{
global $cfg, $wf_debug; // Konfigurációs tömb

 if ($fejlec == '')
 {
	// Ha nincs fejléc paraméter megadva a hívókódban
	print("<title>" .$cfg['pname']. "</title>"); // Csak a weblap neve a fejléc
	$wf_debug->RegisterDLEvent("Fejléc beállítva: " .$cfg['pname']);
 } else {
	// Ellenkező esetben, ha van
	print("<title>" .$fejlec. " - " .$cfg['pname']. "</title>"); // Weblap neve - weblap címe
	$wf_debug->RegisterDLEvent("Fejléc beállítva:" .$fejlec. " - " .$cfg['pname']);
 }
}

function CheckIfIPBanned() // IP-cím ban ellenörzése
{
	global $sql, $cfg, $wf_debug;
	
	$ipbanok = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."bannedips WHERE ip='" .$_SERVER['REMOTE_ADDR']. "'"));
	$wf_debug->RegisterDLEvent("IP-cím " .$_SERVER['REMOTE_ADDR']. " bannoltságának ellenörzése");
	if ( $ipbanok['ip'] == $_SERVER['REMOTE_ADDR'] )
	{
		// Ha van IP tiltás az aktuális címen, akkor nem engedélyezzük a felhasználónak a hozzáférést
		$felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .$ipbanok['uId']. "'"));
		if ( ($felhasznalo['username'] != $NULL) || ($ipbanok['comment'] != $NULL) )
			$kitiltasuzenet = "<br>"; // Ha tudjuk a kiltitó felhasználó nevét, vagy van komment, már egy új sor kerül az üzenetbe
		
		if ( $felhasznalo['username'] != $NULL )
			$kitiltasuzenet .= "Kitiltó felhasználó neve: " .$felhasznalo['username']. "<br>"; // Felhasználó nevének kiírása
		
		if ( $ipbanok['comment'] != $NULL )
			$kitiltasuzenet .= "Hozzászólás: " .$ipbanok['comment'];
		
		print("<center><table><tr halign='center' valign='center'><td>");
		Hibauzenet("BAN", "Az IP címed (" .$_SERVER['REMOTE_ADDR']. ") ki lett tiltva a webhelyről!", "A kitiltás időpontja: " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $ipbanok['bandate']).$kitiltasuzenet); // Hibaüzenet megjelenítése (szükség esetén felhasználónévvel és/vagy kommenttel)
		print("</td></tr></table></center>");
		die();
	}
}
?>