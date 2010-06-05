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
		<input type='submit' value='Tovább >> (Adatbázis adatok megadása)'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regThis'>1. Bevezetés</span><br>
		<span class='regNem'>2. Adatbázis adatok megadása</span><br>
		<span class='regNem'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>4. Táblák létrehozása</span><br>
		<span class='regNem'>5. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>6. Befejezés</span>
		</p></div>");
		break;
	case 2:
		// Adatbázis adatainak megadása
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regThis'>2. Adatbázis adatok megadása</span><br>
		<span class='regNem'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>4. Táblák létrehozása</span><br>
		<span class='regNem'>5. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>6. Befejezés</span>
		</p></div>");
		break;
	case 3:
		// Adatbáziskapcsolat tesztelése
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Adatbázis adatok megadása</span><br>
		<span class='regThis'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>4. Táblák létrehozása</span><br>
		<span class='regNem'>5. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>6. Befejezés</span>
		</p></div>");
		break;
	case 4:
		// Táblák létrehozása
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Adatbázis adatok megadása</span><br>
		<span class='regNem'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regThis'>4. Táblák létrehozása</span><br>
		<span class='regNem'>5. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>6. Befejezés</span>
		</p></div>");
		break;
	case 5:
		// Adminisztrátor létrehozása
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Adatbázis adatok megadása</span><br>
		<span class='regNem'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>4. Táblák létrehozása</span><br>
		<span class='regThis'>5. Adminisztrátor létrehozása</span><br>
		<span class='regNem'>6. Befejezés</span>
		</p></div>");
		break;
	case 6:
		// Befejezés
		print("<div class='postbox'>
		");
		
		
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Telepítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Adatbázis adatok megadása</span><br>
		<span class='regNem'>3. Adatbáziskapcsolat tesztelése</span><br>
		<span class='regNem'>4. Táblák létrehozása</span><br>
		<span class='regNem'>5. Adminisztrátor létrehozása</span><br>
		<span class='regThis'>6. Befejezés</span>
		</p></div>");
		break;
 }
 
 // Információdoboz
 print("<br><div class='menubox'><span class='menutitle'>Információk</span><br>
 <p class='formText'>
	<b>Motor:</b> WhispyForum<br>
	<b>Kiadás típus:</b> " .RELEASE_TYPE. "<br>
	<b>Verziószám:</b> " .VERSION); // DIV NOT CLOSED...
?>