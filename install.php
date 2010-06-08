<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* install.php
   telepítőscript
*/
 
 /* Szükséges fájlok betöltése */
 include('includes/mysql.php'); // Adatbázis osztály
 include('includes/functions.php'); // Funkcióosztály
 include('includes/versions.php'); // Verzióadatok
 print("<link rel='stylesheet' type='text/css' href='themes/default/style.css'>"); // Témaaadatok
 print("<center><h2 class='header'>Telepítés</h2></center>"); // Címsor
 print("<title>WhispyFórum - Telepítés</title>"); // HTML-címsor
 $instPos = 0; // Kezdeti lépés: 0
 $instPos = $_POST['pos']; // A lépés a beérkező lépés adat
 
 function Naplo ( $szoveg, $ido = FALSE )
 {
	// Napló írása
	if ( $ido == FALSE )
	{
		file_put_contents('logs/install.log', "\r\n" .$szoveg, FILE_APPEND); // Időérték nélkül
	} else {
		file_put_contents('logs/install.log', "\r\n" .$szoveg. ": " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )", FILE_APPEND); // Időértékkel
	}
 }
 
 // Telepítettség ellenörzése
 if ( file_exists('install.lock') )
 {	
	print("A portálrendszer már telepítve van. Kérlek töröld az <i>install.lock</i> fájlt a rendszerből");
	file_put_contents('logs/install.log', "Telepítés megkezdve: " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )");
	Naplo("A portálrendszer már telepítve van, kérlek töröld az install.lock fájlt!");
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
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
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
		file_put_contents('logs/install.log', "Telepítés megkezdve: " .Datum("normal","nagybetu","dL","H","i","s"). " ( " .time(). " )");
		break;
	case 2:
		// Adatbázis adatainak megadása
		print("<div class='postbox'><h3 class='header'><p class='header'>2. Konfigurációs adatok megadása</p></h3>");
		print("A portálrendszer használatához szükséges néhány fontos adat, melyet egy fájlban tárolunk majd a webszerveren. Kérlek töltsd ki az alábbi űrlapot a megfelelő adatokkal (néhány esetben beírtunk alapértelmezett értékeket, ezeket csak indokolt esetben módosítsd!
		<br>
		<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<p class='formText'>Adatbázisszerver címe<a class='feature-extra'><span class='hover'><span class='h3'>Adatbázisszerver címe</span>A szerver elérhetősége, a legtöbb esetben <i>localhost</i></span><sup>?</sup></a><a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='dbhost' value='localhost'><br>
			Belépési felhasználó<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a> <input type='text' name='dbuser'><br>
			Jelszó<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='password' name='dbpass'><br>
			Adatbázis neve:<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='dbname'><br>
			Táblanév prefixum<a class='feature-extra'><span class='hover'><span class='h3'>Táblanév prefixum</span>Ha nincs lehetőséged a portálrendszert külön adatbázisba telepíteni (pár szolgáltató egy regisztrációhoz egy adatbázist ad), megadhatsz egy táblanév prefixumot, mely minden táblát meg fog előzni.<br>Például, ha te beírod hogy <b>wf_</b>, a felhasználókat tartalmazó tábla neve <i>user</i> helyett <i><b>wf_</b>user</i> lesz, elkerülve ezzel, más, <i>user</i> nevű táblát használó rendszerekkel való ütközést.</span><sup>?</sup></a>: <input type='text' name='tbprf'></p>
			<p class='formText'>SMTP szerver címe: <input type='text' name='SMTP'><br>
			SMTP port száma<a class='feature-extra'><span class='hover'><span class='h3'>SMTP port</span>Kimenő levélszerver portszáma, alapértelmezésben <b>25</b></span><sup>?</sup></a>: <input type='text' name='smtp_port' value='25' size='5'><br>
			Feladó e-mail címe: <input type='text' name='sendmail_from'><br>
			HTML stílusú levél küldése: <input type='radio' value='0' name='sendmail_html' checked>Nem <input type='radio' name='sendmail_html' value='1'>Igen</p>
			
			<p class='formText'>Weboldal domain neve<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='phost' value='" .$_SERVER['SERVER_ADDR']. "'><br>
			Weboldal neve<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='pname' value='Új WhispyFórum portál'></p>
			
			<p class='formText'>Webmester neve<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='webmaster'><br>
			Webmester e-mail címe<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='webmaster_email' value='webmaster@" .$_SERVER['SERVER_ADDR']. "'></p>
			
			<p class='formText'>Kiválasztott téma: <select size='1' name='THEME_NAME'>
				<option>default</option>
				</select><br>
			Regisztráció engedélyezése: <input type='radio' value='0' name='ALLOW_REGISTRATION'>Nem <input type='radio' name='ALLOW_REGISTRATION' value='1' checked>Igen<br>
			Napló mélysége<a class='feature-extra'><span class='hover'><span class='h3'>Naplómélység</span>A listában lefelé görgetve mélyebbre jut, minden szint tartalmazza a felette lévő szintek összes elemét.</span><sup>?</sup></a>: <select size='1' name='LOG_DEPTH'>
				<option value='0'>semmi (napló kikapcsolása)</option>
				<option value='1'>közepes (hibaüzenetek)</option>
				<option value='2'>mély (felhasználók)</option>
				<option value='2'>mélyebb (lapmegtekintések)</option>
				</select><br>
			
			<input type='submit' value='Tovább >> (Konfigurációs fájl létrehozása)'>
			<input type='hidden' name='pos' value='3'>
		</form>");
		
		Naplo("Konfigurációs adatok bevitele megkezdve", TRUE);
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
		print("<div class='postbox'><h3 class='header'><p class='header'>3. Konfigurációs fájl létrehozása</p></h3>"); // Fejléc
		Naplo("Konfigurációs fájl létrehozása megkezdve", TRUE);
		if ( ($_POST['dbhost'] == $NULL) || ($_POST['dbuser'] == $NULL) || ($_POST['dbpass'] == $NULL) || ($_POST['dbname'] == $NULL) || ($_POST['phost'] == $NULL) || ($_POST['pname'] == $NULL) || ($_POST['webmaster'] == $NULL) || ($_POST['webmaster_email'] == $NULL) )
		{
			// Ha bármelyik szükséges mező üres
			print("Nem töltöttél ki minden szükséges mezőt a folytatáshoz.<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<input type='hidden' name='pos' value='2'>
				<input type='submit' value='<< Vissza (Konfigurációs adatok megadása)'>
			</form>"); // Visszatérési űrlap
			Naplo("Szükséges mezők hiányoztak", TRUE);
		} else {
			print("Az általad megadott értékek alapján létrehozásra kerül a konfigurációs fájl.");
			// Konfigurációs fájl mentése
			$konfig = "<?php 
global \$cfg;
\$cfg = array(
	'dbhost' => '" .$_POST['dbhost']. "',
	'dbuser' => '" .$_POST['dbuser']. "',
	'dbpass' => '" .$_POST['dbpass']. "',
	'dbname' => '" .$_POST['dbname']. "',
	'tbprf' => '" .$_POST['tbprf']. "',

	'SMTP' => '" .$_POST['SMTP']. "',
	'smtp_port' => " .$_POST['smtp_port']. ",
	'sendmail_from' => '" .$_POST['sendmail_from']. "',
	'sendmail_html' => " .$_POST['sendmail_html']. ",

	'phost' => '" .$_POST['phost']. "',
	'pname' => '" .$_POST['pname']. "',

	'webmaster' => '" .$_POST['webmaster']. "',
	'webmaster_email' => '" .$_POST['webmaster_email']. "',
 );
 
 define('THEME_NAME', '" .$_POST['THEME_NAME']. "');
 define('ALLOW_REGISTRATION', " .$_POST['ALLOW_REGISTRATION']. ");
 define('LOG_DEPTH', " .$_POST['LOG_DEPTH']. ");
?>";
			file_put_contents('config.php', $konfig);
			Naplo("config.php létrehozva", TRUE);
			Naplo("Konfigurációs fájl:\r\n" .$konfig. "\r\n\r\n");
			
			if ( file_get_contents('config.php') != $konfig )
			{
				print("<h3>A fájl létrehozása nem sikerült</h3>A <i>config.php</i> állomány nem írható, vagy a beleírt tartalom nem egyezik a szükséges tartalommal. Kérlek, kézileg hozd létre én mentsd el a konfigurációs fájlt.<br><textarea rows='50' cols='55' disabled>Aktuális tartalom (config.php):
				
" .file_get_contents('config.php'). "</textarea><textarea rows='50' cols='55'>Szükséges tartalom:

" .$konfig. "</textarea>");
			Naplo("A tartalom nem egyezett", TRUE);
			} else {
				print("<h3>A fájl létrehozása sikeres</h3>
				<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
					<input type='hidden' name='pos' value='4'>
					<input type='submit' value='Tovább >> (Adatbáziskapcsolat tesztelése)'>
				</form>");
			}
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
		include('config.php'); // Konfigurációs fájl
		$link = @mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']); // Az $sql->Connect; helyett most ezt használjuk
		
		Naplo("Kapcsolat tesztelése megkezdve", TRUE);
		
		print("<div class='postbox'><h3 class='header'><p class='header'>4. Adatbáziskapcsolat tesztelése</p></h3>"); // Fejléc
		if ( $link == FALSE )
		{
			// Ha nem sikerült a kapcsolódás
			print("Az adatbázishoz való kapcsolódás nem sikerült! Lehetséges, hogy a megadott adatok érvénytelenek, vagy az adatbázis szerver nem érhető el.<br>Nyers hibaüzenet: <code>" .mysql_error(). "</code><br>
			<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<p class='formText'>Kérlek válassz egyet az alábbi letőségek közül<br>
					<input type='radio' value='2' name='pos'><< Vissza (Konfigurációs adatok megadása)<br>
					<input type='radio' value='4' name='pos' checked><> Újra próbálkozás</p>
				<input type='submit' value='Választás megerősítése'>
			</form>");
			
			Naplo("Sikertelen kapcsolódás a szerverhez", TRUE);
		} else {
			Naplo("Sikeres kapcsolódás", TRUE);
			
			print("Az adatbázisszerverhez való csatlakozás sikeres volt!
			<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<input type='hidden' value='5' name='pos'>
				<input type='submit' value='Tovább >> (Táblák létrehozása)'>
			</form>");
		}
		@mysql_close($link); // Kapcsolat zárása
		
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
		include('config.php'); // Konfigurációs fájl
		$sql->Connect(); // Maradunk a megszokott kapcsolódási módnál
		print("<div class='postbox'><h3 class='header'><p class='header'>5. Táblák létrehozása</p></h3>"); // Fejléc
		print("Most kerül sor a táblák létrehozására a háttérben, az adatbázisban. Kérlek, ezután ne módosítsd a konfigurációs fájl tartalmát! A végrehajtott tevékenységekről alább megjelennek az információk.<br>"); // Információ
		
		include('install/database.php'); // Egyszerűen betöltjük a megfelelő fájlt és lefut a script.
		print("<br>A táblák létrehozása sikeresen befejeződött!<br><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<input type='hidden' value='6' name='pos'>
				<input type='submit' value='Tovább >> (Adminisztrátor létrehozása)'>
			</form>"); // Továbblépési űrlap
		
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
		Naplo("Adminisztrátori fiók létrehozása megkezdve", TRUE);
		print("<div class='postbox'><h3 class='header'><p class='header'>6. Adminisztrátor létrehozása</p></h3>"); // Fejléc
		print("Az utolsó lépés a portálrendszer telepítésének befejezés előtt egy adminisztrátori felhasználó elkészítése. Az adminisztrátorok teljhatalommal bírnak az oldal működése felett, a készített felhasználó után oszthatsz ki adminisztároti jogkört másoknak.<br>"); // Bevezetés
		
		if ( ($_POST['username'] == $NULL) || ($_POST['password'] == $NULL) || ($_POST['email'] == $NULL) || ($_POST['realname'] == $NULL) )
		{
			// Ha bármely kötelező adat üres, űrlapot adunk
			include('config.php');
			
			print("Néhány adatot előre beírtunk, ezeket szabadon módosíthatod. <b>Minden mezőt kötelező kitölteni.</b><br><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<p class='formText'>Választott felhasználói név<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='username' value='admin'></p>
			<p class='formText'>Jelszó<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='password' name='password'></p>
			<p class='formText'>E-mail cím<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='email' value='" .$cfg['webmaster_email']. "'></p>
			<p class='formText'>Valódi neved<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='realname' value='" .$cfg['webmaster']."'></p>
			<input type='hidden' name='pos' value='6'>
				<input type='submit' value='Tovább >> (adatok elküldése)'>
		</form>");
		} else {
			// Felhasználó létrehozása
			include('config.php');
			$sql->Connect();
			$acToken = md5($_POST['username'] . "|" . md5($_POST['password']) . "|" . Datum("normal","nagybetu","dL","H","i","s")); // Aktiválási kulcs generálása
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']. "user 
	(username, pwd, email, realName, activated, activateToken, regip, regsessid, regdate, activateDate, userLevel, postCount) VALUES ('" .$_POST['username']. "', '" .md5($_POST['password']). "', '" .$_POST['email']. "', '" .$_POST['realname']. "', '1', '" .$acToken. "', '" .$_SERVER['REMOTE_ADDR']. "', '" .session_id(). "', " .time(). ", '" .time(). "', '3', '1')", 'INSTALL'); // Admin létrehozása
			print("A felhasználó sikeresen létrehozva!<br><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<input type='hidden' name='pos' value='7'>
				<input type='submit' value='Tovább >> (befejezés)'>
		</form>");
		}
		
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
		Naplo("Telepítés befejezve...", TRUE);
		file_put_contents('install.lock', "INSTALL_LOCK\nINSTALL_TS," .time(). "\nINSTALL_IP," .$_SERVER['REMOTE_ADDR']);
		print("<div class='postbox'><h3 class='header'><p class='header'>7. Befejezés</p></h3>"); // Fejléc
		print("A portálrendszered telepítése ezzel befejeződött. Az alább található gombra kattintva használatba veheted a portálodat.<br>Biztonsági okokból kérlek távolítsd el a telepítési mappádból az <b>install.php</b> fájlt és a teljes <b>/install</b> mappát. Amíg ezt nem teszed meg, az oldal felett könnyen átvehető az irányítás. Az <b>install.lock</b> fájlt hagyd meg!<br>
			<form action='index.php' method='POST'>
				<input type='submit' value='Tovább >> (Telepítés befejezése)'>
		</form>Telepítési napló:<br><textarea rows='20' cols='80' disabled>" .file_get_contents('logs/install.log'). "</textarea>");
		
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
?>