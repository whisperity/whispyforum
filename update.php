<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* update.php
   frissítő
*/
 
 /* Szükséges fájlok betöltése */
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('update.php');
 SetTitle("Frissítés");
 print("<td class='left' valign='top'>"); // Bal oldali doboz
 $session->CheckSession(session_id(), $_SERVER['REMOTE_ADDR']); // Ellenörzés, hogy a felhasználó be van-e jelentkezve
 $user->GetUserData(); // Felhasználó szintjének ellenörzése
 
 print("<link rel='stylesheet' type='text/css' href='themes/default/style.css'>"); // Témaaadatok
 print("<center><h2 class='header'>Frissítés</h2></center>"); // Címsor
 print("<title>" .$cfg['pname']. " - Frissítés</title>"); // HTML-címsor
 $updPos = 0; // Kezdeti lépés: 0
 $updPos = $_POST['pos']; // A lépés a beérkező lépés adat
 
 if ( $_SESSION['userLevel'] != 3) { // Ha a felhasználó nem admin, nem jelenítjük meg a menüt neki
	Hibauzenet("ERROR","Az frissítés nem érhető el","A funkció használatához MINIMUM Adminisztrátori (level 3) jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel'].").");
	DoFooter();
	die();
 }
 
 $verzio = mysql_fetch_assoc($sql->Lekerdezes("SELECT RELEASE_TYPE, VERSION, RELEASE_DATE FROM " .$cfg['tbprf']."version"));
 
 /* Felhasználjuk a fórumban használt postbox (bal oldali nagy) és postright (jobb oldali kicsi) div-eket */
 
 switch ($updPos) { // Lépés szám alapú váltás
	case '':
	case 0:
	case 1:
		// Ha kezdeti lépés
		// Kezdeti információkat, bevezetőt kiírjuk
		print("<div class='postbox'><h3 class='header'><p class='header'>1. Bevezetés</p></h3>");
		print("Üdvözlünk a frissítési segédeszközben.<br>Az eszköz segítségével frissítheted a portálrendszert.<br><div style='font-weight: bold;
	color: Maroon;
	background-color: #FFC0C0;
	border: 1px solid maroon;
	padding: 6px;
	line-height: 125%;

	margin: 1em auto'><b><h3>FONTOS!</h3></b>
		Ellenőrizd, hogy készítettél-e megfelelő biztonsági mentést az adatbázis tartalmáról és a fájlokról!
		Ne kezdd meg a frissítést addig, amíg nincsenek meg a biztonsági mentések.
		<br>
		<small>Ha kamikaze vagy, és nem számít az adatok épsége, megkezdheted biztonsági mentés nélkül is, de a felelősség csak téged terhel. <u>Voltál</u> figyelmeztetve.</small>
	</div>
		Ha készen állsz a frissítésre, kattints a Tovább gombra.
");
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<input type='hidden' name='pos' value='2'>
		<input type='submit' value='Tovább >>'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
		print("<p>
		<span class='regThis'>1. Bevezetés</span><br>
		<span class='regNem'>2. Frissítési lépések ismertetése</span><br>
		<span class='regNem'>3. Frissítés futtatása</span><br>
		<span class='regNem'>4. Befejezés</span><br>
		</p></div>");
		
		break;
	case 2:
		// Frissítési lépések ismertetése
		print("<div class='postbox'><h3 class='header'><p class='header'>2. Frissítési lépések ismertetése</p></h3>");
		print("Az telepített és a futtatott verzió adatait a bal oldalon olvashatod.<br>");
		
		// Sok HA fügvénnyel megállapítjuk mit és hogyan kell frissíteni
		if ( ($verzio['RELEASE_TYPE'] == RELEASE_TYPE) && ($verzio['VERSION'] == VERSION) && ($verzio['RELEASE_DATE'] == RELEASE_DATE) )
		{
			print("<div style='font-weight: bold;
	color: Maroon;
	background-color: #FFC0C0;
	border: 1px solid maroon;
	padding: 6px;
	line-height: 125%;

	margin: 1em auto'><b><h3>FIGYELMEZTETÉS!</h3></b>
		Nincs szükség frissítésre, mivel a verziószámok egyeznek.
	</div>
		<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<input type='hidden' name='pos' value='25'>
		<input type='submit' value='Tovább >>'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regThis'>2. Frissítési lépések ismertetése</span><br>
		<span class='regNem'>3. Befejezés</span><br>
		</p></div>");
		} else {
			
			if ($verzio['RELEASE_TYPE'] != RELEASE_TYPE) {
			print("<div style='font-weight: bold;
	color: Maroon;
	background-color: #FFC0C0;
	border: 1px solid maroon;
	padding: 6px;
	line-height: 125%;

	margin: 1em auto'><b><h3>FIGYELMEZTETÉS!</h3></b>
		A telepített és a futtatott változat kiadási típusa nem egyezik meg. Különböző kiadási típusok között nem lehet frissíteni.
	</div>
		<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<input type='hidden' name='pos' value='25'>
		<input type='submit' value='Tovább >>'>
		</form>");
			
			// Oldalsó menü
			print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
			print("<p>
			<span class='regNem'>1. Bevezetés</span><br>
			<span class='regThis'>2. Frissítési lépések ismertetése</span><br>
			<span class='regNem'>3. Befejezés</span><br>
			</p></div>");
		}
			
			if ( ( $verzio['RELEASE_TYPE'] == "revision" ) && ( RELEASE_TYPE == "revision" ) )
			{
				if ( $verzio['VERSION'] < VERSION )
				{
					print("Nincs szükség különösebb frissítésre, mivel frissítendő objektumok nem változtak, csak a rendszer fájlai.<br>Az újabb fájlok futtatása nem okoz kompatibilitási problémát.<br>
					<br>
					Frissítés lépései:
					<ul>
						<li>Verzióadatok frissítése a hibaüzenet eltüntetéséhez</li>
					</ul><br>");
					
					print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
					<input type='hidden' name='pos' value='20'>
					<input type='submit' value='Frissítés >>'>
					<a class='feature-extra'><span class='hover'><span class='h3'>Frissítés futtatása!</span>Ha rányomsz a gombra, már nincs visszaút!</span><sup>?</sup></a>
			</form>");
				}
			}
			
			if ( ( $verzio['RELEASE_TYPE'] == "lt19" ) && ( RELEASE_TYPE == "lt19" ) )
			{
				print("<div style='font-weight: bold;
	color: Maroon;
	background-color: #FFC0C0;
	border: 1px solid maroon;
	padding: 6px;
	line-height: 125%;

	margin: 1em auto'><b><h3>FIGYELMEZTETÉS!</h3></b>
		A portálmotor aktuális kiadása személyesen a ____ -re készült. Az aktuális verzióhoz nem érhető el frissítés.
	</div>
		<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<input type='hidden' name='pos' value='25'>
		<input type='submit' value='Tovább >>'>
		</form>");
			}
			
			// Oldalsó menü
			print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
			print("<p>
			<span class='regNem'>1. Bevezetés</span><br>
			<span class='regThis'>2. Frissítési lépések ismertetése</span><br>
			<span class='regNem'>3. Befejezés</span><br>
			</p></div>");
		}
		
		break;
	case 4: // Befejezés
		print("<div class='postbox'><h3 class='header'><p class='header'>4. Befejezés</p></h3>");
		print("A frissítés sikeresen befejeződött.<br>Nincs szükség további tennivalóra. A gombra kattintva a kezdőlapra jutsz.");
		print("<form action='index.php' method='POST'>
		<input type='submit' value='Befejezés >>'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
		print("<p>
			<span class='regNem'>1. Bevezetés</span><br>
			<span class='regNem'>2. Frissítési lépések ismertetése</span><br>
			<span class='regNem'>3. Frissítés futtatása</span><br>
			<span class='regThis'>4. Befejezés</span><br>
			</p></div>");
		
		break;
	case 20: // REVSION->REVISION update, verziószámok növelése
		print("<div class='postbox'><h3 class='header'><p class='header'>3. Frissítés futtatása</p></h3>");
		print("Frissítés...<br>");
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."version SET RELEASE_TYPE='" .RELEASE_TYPE. "', VERSION='" .VERSION. "', RELEASE_DATE='" .RELEASE_DATE. "'");
		print("Verzióadatok frissítve...");
		print("<br>Frissítés befejezve.");
		
		print("<form action='index.php' method='POST'>
		<input type='hidden' name='pos' value='4'>
		<input type='submit' value='Befejezés >>'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
		print("<p>
			<span class='regNem'>1. Bevezetés</span><br>
			<span class='regNem'>2. Frissítési lépések ismertetése</span><br>
			<span class='regThis'>3. Frissítés futtatása</span><br>
			<span class='regNem'>4. Befejezés</span><br>
			</p></div>");
		
		break;
	case 25: // Befejezés, nem történt frissítés
		print("<div class='postbox'><h3 class='header'><p class='header'>3. Befejezés</p></h3>");
		print("A portálrendszert nem szükséges frissíteni. Nem történt frissítés.");
		print("<form action='index.php' method='POST'>
		<input type='submit' value='Befejezés >>'>
		</form>");
		// Oldalsó menü
		print("</div><div class='postright'><div class='menubox'><span class='menutitle'>Frissítés</span><br>");
		print("<p>
		<span class='regNem'>1. Bevezetés</span><br>
		<span class='regNem'>2. Frissítési lépések ismertetése</span><br>
		<span class='regThis'>3. Befejezés</span><br>
		</p></div>");
		
		break;
	}
 
 // Információdoboz
 print("<br><div class='menubox'><span class='menutitle'>Információk</span><br>
 <p class='formText'>
	<b>Motor:</b> WhispyForum<br><br>
	<b><u>Telepített verzió:</u></b><br>
	<b>Kiadás típus:</b> " .$verzio['RELEASE_TYPE']. "<br>
	<b>Verziószám:</b> " .$verzio['VERSION']. "<br>
	<b>Kiadás dátuma:</b> " .$verzio['RELEASE_DATE']. "
	<br>↓<br>
	<b><u>Futtatott verzió:</u></b><br>
	<b>Kiadás típus:</b> " .RELEASE_TYPE. "<br>
	<b>Verziószám:</b> " .VERSION. "<br>
	<b>Kiadás dátuma:</b> " .RELEASE_DATE. "</div>");
	
 print("</div>"); // Jobb oldali doboz (div class='rightbox') zárása
?>