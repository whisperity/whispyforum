<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install.php
   telepítőscript
*/
 
 /* Szükséges fájlok betöltése */
 include('config.php'); // Konfigurációs állomány
 include('includes/mysql.php'); // Adatbázis osztály
 include('includes/functions.php'); // Funkcióosztály
 include('includes/versions.php'); // Verzióadatok
 $sql->Connect(); // Kapcsolódás a szerverhez
 print("<link rel='stylesheet' type='text/css' href='themes/default/style.css'>"); // Témaaadatok
 print("<center><h2 class='header'>Telepítés</h2></center>"); // Címsor
 
 $instPos = 0; // Kezdeti lépés: 0
 $instPos = $_GET['pos']; // A lépés a beérkező lépés adat
 
 // Telepítettség ellenörzése
 if ( file_exists('install.lock') )
 {	
	print("A portálrendszer már telepítve van. Kérlek töröld az <i>install.lock</i> fájlt a rendszerből");
	die();
 }
 
 /* Felhasználjuk a fórumban használt postbox (bal oldali nagy) és postright (jobb oldali kicsi) div-eket */
 
 switch ($instPos) { // Lépés szám alapú váltás
	case '':
	case 0:
	case 1:
		// Ha kezdeti lépés
		// Kezdeti információkat, bevezetőt kiírjuk
		print("<div class='postbox'><h3 class='header'><p class='header'>1. Bevezetés</p></h3>");
		print("Köszönjük, hogy a portálmotorunkat használod! Igyekszünk a lehető legnagyobb hatásfokot és kompatibilistást, valamint fejlődést biztosítani, hogy a felhasználóközösségnek örömére váljon ezen rendszer használata.<br>A script, amit jelenleg futtatsz, a portálrendszer első lépésében, a telepítésben segít neked. Segítségével létrehozhatod az alapvető adatbázist és adatokat, valamint egy adminisztrátori felhasználót.");
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
		<input type='hidden' name='pos' value='2'>
		<input type='submit' value='Tovább >> (Konfigurációs adatok megadása)'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regThis'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 2:
		// Adatbázis adatainak megadása
		print("<div class='postbox'><h3 class='header'><p class='header'>2. Konfigurációs adatok megadása</p></h3>");
		print("
		<br>
		<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
			<p class='formText'>Adatbázisszerver címe<a class='feature-extra'><span class='hover'><span class='h3'>Adatbázisszerver címe</span>A szerver elérhetősége, a legtöbb esetben <i>localhost</i></span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='dbhost'><br>
			Belépési felhasználó<span class='star'>*</span>: <input type='text' name='dbuser'><br>
			Jelszó<span class='star'>*</span>: <input type='text' name='dbpass'><br>
			Adatbázis neve:<span class='star'>*</span>: <input type='text' name='dbname'><br>
			Táblanév prefixum<a class='feature-extra'><span class='hover'><span class='h3'>Táblanév prefixum</span>Ha nincs lehetőséged a portálrendszert külön adatbázisba telepíteni (pár szolgáltató egy regisztrációhoz egy adatbázist ad), megadhatsz egy táblanév prefixumot, mely minden táblát meg fog előzni.<br>Például, ha te beírod hogy <b>wf_</b>, a felhasználókat tartalmazó tábla neve <i>user</i> helyett <i><b>wf_</b>user</i> lesz, elkerülve ezzel, más, <i>user</i> nevű táblát használó rendszerekkel való ütközést.</span><sup>?</sup></a>: <input type='text' name='tbprf'></p>
			<p class='formText'>SMTP szerver címe: <input type='text' name='SMTP'><br>
			SMTP port száma<a class='feature-extra'><span class='hover'><span class='h3'>SMTP port</span>Kimenő levélszerver portszáma, alapértelmezésben <b>25</b></span><sup>?</sup></a>: <input type='text' name='smtp_port' value='25' size='5'><br>
			Feladó e-mail címe: <input type='text' name='sendmail_from'><br>
			HTML stílusú levél küldése: <input type='radio' value='0' name='sendmail_html' checked>Nem <input type='radio' name='sendmail_html' value='1'>Igen</p>
			
			<p class='formText'>Weboldal domain neve<span class='star'>*</span>: <input type='text' name='phost' value='" .$_SERVER['SERVER_ADDR']. "'><br>
			Weboldal neve<span class='star'>*</span>: <input type='text' name='pname' value='Új WhispyFórum portál'></p>
			
			<p class='formText'>Webmester neve<span class='star'>*</span>: <input type='text' name='webmaster'><br>
			Webmester e-mail címe<span class='star'>*</span>: <input type='text' name='webmaster_email' value='webmaster@" .$_SERVER['SERVER_ADDR']. "'></p>
			
			<p class='formText'>Kiválasztott fórumtéma: <select size='1' name='THEME_NAME'>
			<option>default</option>
			</select><br>
			Regisztráció engedélyezése: <input type='radio' value='0' name='ALLOW_REGISTRATION'>Nem <input type='radio' name='ALLOW_REGISTRATION' value='1' checked>Igen<br>
			Naplógenerálás: <input type='radio' value='0' name='DEBUG_LOG' checked>Nem <input type='radio' name='DEBUG_LOG' value='1'>Igen</p>
			
			<input type='submit' value='Tovább >> (Konfigurációs fájl létrehozása)'>
			<input type='hidden' name='pos' value='3'>
		</form>");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regThis'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 3:
		// Konfigurációs fájl létrehozása
		
		if ( ($_GET['dbhost'] == $NULL) || ($_GET['dbuser'] == $NULL) || ($_GET['dbpass'] == $NULL) || ($_GET['dbname'] == $NULL) || ($_GET['phost'] == $NULL) || ($_GET['pname'] == $NULL) || ($_GET['webmaster'] == $NULL) || ($_GET['webmaster_email'] == $NULL) )
		{
			// Ha bármelyik szükséges mező üres
			
		} else {
			// Konfigurációs fájl mentése
			
		}
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regThis'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 4:
		// Adatbáziskapcsolat tesztelése
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regThis'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 5:
		// Táblák létrehozása
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regThis'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 6:
		// Adminisztrátor létrehozása
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regThis'>6. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>7. Befejezés</span>
		</p></div>");
		break;
	case 7:
		// Befejezés
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Konfigurációs adatok megadása</span><br>
		<span class='regNem'>3. Konfigurációs fájl létrehozása</span><br>
		<span class='regNem'>4. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>5. Táblák létrehozása</span><br>
		<span class='regNem'>6. Adminisztrátor létrehozása</span><br>
		<span class='regThis'>7. Befejezés</span>
		</p></div>");
		break;
 }
 
 // Információdoboz
 print("<br><div class='menubox'><span class='menutitle'>Információk</span><br>
 <p class='formText'>
	<b>Motor:</b> WhispyForum<br>
	<b>Kiadás típus:</b> " .RELEASE_TYPE. "<br>
	<b>Verziószám:</b> " .VERSION. "<br>
	<b>Kiadás dátuma:</b> " .RELEASE_DATE. "</div>");
	
 print("</div>"); // Jobb oldali doboz (div class='rightbox') zárása
 //include('install/database.php'); // Táblák létrehozása
?>
