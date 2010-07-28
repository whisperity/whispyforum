<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/log.php
   naplóértelmezési kód
*/

// Ha parancsot kapunk, végrehatás
if ( $_GET['cmd'] != $NULL )
{
	switch ($_GET['cmd']) { // A bejövő parancs alapján megfelelő lépés végrehajtása
		case "savelog":
			/* Ellenörzőkulcs ellenörzése */
			include("../includes/user.php");
			include("../includes/mysql.php");
			include("../includes/functions.php");
			include("../config.php");
			$sql->Connect();
			session_start();
			$user->GetUserData();
			
			if ( $_GET['adc'] == $NULL )
				die("");
			if ( $_GET['adc'] != $_SESSION['regdate'])
				die("A napló mentéséhez nincs jogosultságod");
			
			// A napló mentését eltároljuk a naplóban (mielőtt letöltenénk)
			file_put_contents('../logs/site.log', "\n" .time(). ';LOG_SAVED', FILE_APPEND);
			
			$fajl = "naplo-" .date("Y.m.d.H_i_s"). ".txt"; // Nem használjuk a Datum() függvényt, hogy elkerüljük a szóközöket
			$szoveg = "               Dátum-idő               |               Esemény               |                              További infók                              |
";
			
			$data = @file_get_contents("../logs/site.log"); // Adat bekérése
			$sorok = explode("\n", $data);
			
			$tovabbi = "\n                                                                               "; // További részhez ugrás
			
			foreach ($sorok as &$sor) {
				$tagok = explode(';', $sor);
				$szoveg .= "    " .@Datum("normal", "nagybetu", "dL", "H", "i", "s", $tagok[0]);
				$szoveg .= "                 ";
				
				switch ($tagok[1])
				{
					case "LOG_CREATE":
						$szoveg .= "Napló létrehozva";
						break;
					case "LOG_SAVED":
						$szoveg .= "Napló lementve";
						break;
					case "LOG_PURGE":
						$szoveg .= "Napló törölve";
						break;
					case "WARNING":
					case "ERROR":
					case "CRITICAL":
						$szoveg .= "Hibauzenet (" .$tagok[1]. ")";
						$szoveg .= "       ";
						$szoveg .= $tagok[2]. $tovabbi .$tagok[3]. $tovabbi .$tagok[4]. $tovabbi .$tagok[5]. $tovabbi .$tagok[6]. $tovabbi .$tagok[7];
						break;
					case "PAGE_VIEW":
						$szoveg .= "Lapmegtekintés";
						$szoveg .= "              ";
						$szoveg .= "URL: " .$tagok[2].$tovabbi. "IP-cím: " .$tagok[3].$tovabbi. "HTTP_USER_AGENT: " .$tagok[4].$tovabbi. "Felhasználó: " .$tagok[5]. " (" .$tagok[6]. ")";
						break;
					case "USR_REGISTERED_SUCCESSFULLY":
						$szoveg .= "Sikeres felhasználói regisztráció";
						$szoveg .= "Felhasználói név: " .$tagok[2].$tovabbi. "
Jelszó: " .$tagok[3].$tovabbi. "
E-mail: " .$tagok[4].$tovabbi. "
Valódi név: " .$tagok[5] .$tovabbi. "
Aktiválókulcs: " .$tagok[6];
						break;
					case "USR_ACTIVATE":
						$szoveg .= "Felhasználó aktiválta magát";
						$szoveg .= " ";
						$szoveg .= "Felhasználói név: " .$tagok[2].$tovabbi."
Aktiválókulcs (token): " .$tagok[3];
						break;
					case "SQL_CONNECT_SELECTDB":
						$szoveg .= "Adatbázis kapcsolódva, kiválasztva";
						break;
					case "SQL_DC":
						$szoveg .= "Adatbázis szétkapcsolva";
						break;
					case "SQL":
						$szoveg .= "SQL-lekérdezés";
						$szoveg .= "               ";
						$szoveg .= $tagok[2];
						break;
					default:
						$szoveg .= "Egyéb: " .$tagok[1];
						break;
				}
			
			$szoveg .= "\r\n\r\n"; // Új sor
			}
			
			/* Fájl lementési script */
			ob_start();
				header("Cache-Control: public, must-revalidate");
				header("Content-Type: text");
				header("Content-Length: " .(string)(filesize("../logs/site.log")) );
				header('Content-Disposition: attachment; filename="'.$fajl.'"');
				header("Content-Transfer-Encoding: binary\n");
			ob_end_clean();

			print($szoveg); // Fájl szövegének kiküldése
			die(); // További kódnak nem szabad lefutnia
			break;
		case "deletelog":
			/* Ellenörzőkulcs ellenörzése */
			include("../includes/user.php");
			include("../includes/mysql.php");
			include("../includes/functions.php");
			include("../config.php");
			$sql->Connect();
			session_start();
			$user->GetUserData();
			
			if ( $_GET['adc'] == $NULL )
				die("");
			if ( $_GET['adc'] != $_SESSION['regdate'])
				die("A napló törléséhez nincs jogosultságod");
			
			$naplo = file_get_contents('../logs/site.log'); // Napló lekérése
			$fajl = "naplo-" .date("Y.m.d.H_i_s"). ".lo"; // Nem használjuk a Datum() függvényt, hogy elkerüljük a szóközöket
			file_put_contents('../logs/' .$fajl, $naplo); // Napló lementése a törléskori dátum értékével
			file_put_contents('../logs/' .$fajl, "\n" .time(). ',LOG_PURGE', FILE_APPEND); // A törlés beleírása a régi naplóba
			
			// A napló mentését eltároljuk a naplóban (miután töröltünk)
			file_put_contents('../logs/site.log', time(). ';LOG_PURGE'); // Nincs ,FILE_APPEND, a fájl felülíródik
			
			print("Napló sikeresen törölve!"); //Értesítés
			
			die(); // Ne fusson le a többi rész
			break;
	}
}

// Egyéb esetben napló listázása
if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Oldalnapló megtekintése</h2></center>
		<br>
		Jelen bővítményben a weboldal naplóját tekintheted meg.<br>
		Az aktuális naplómélység:
<?php
switch (LOG_DEPTH)
{
	case 0:
		print("semmi (napló kikapcsolása), lentebb a napló archívuma található");
		break;
	case 1:
		print("alacsony (hibaüzentek)");
		break;
	case 2:
		print("közepes (felhasználók)");
		break;
	case 3:
		print("mély (lapmegtekintések)");
		break;
	case 4:
		print("mélyebb (SQL-kérések)");
		break;
}
print(".<br><br><br>");

$data = @file_get_contents("logs/site.log"); // Fájl megnyitása

if ($data == NULL)
{
	// Ha nem létezik a fájl
	echo "Még nem került létrehozásra naplófájl.";
} else {
?>
<form>
<p class="formText">Naplóval kapcsolatos lehetőségek: 
<input type="button" value="Napló mentése" 
<?php
echo 'onClick="window.open(\'admin/log.php?cmd=savelog&adc=' .$_SESSION['regdate']. "";
?>
', 'popupwindow', 'width=160,height=120'); return false;">
<a name="" class="feature-extra"><span class="hover"><span class="h3">Napló mentése</span>A gombra kattintva lementheted a webhelynapló minden mostanáig megtörtént elemét a fenti táblázathoz hasonlóan egy tagolt, TXT fájlba.</span><sup>?</sup></a>

<input type='button' value='Napló törlése'
<?php
echo 'onClick="window.open(\'admin/log.php?cmd=deletelog&adc=' .$_SESSION['regdate']. "";
?>
', 'popupwindow', 'width=160,height=120'); return false;">
<a name="" class="feature-extra"><span class="hover"><span class="h3">Napló törlése</span>A gombra kattintva törlöd az aktuális webhelynaplót. A törölt naplófájl helyére a program egy újat hoz létre, adatok nélkül.</span><sup>?</sup></a>

</p></form>
<?php
print("<table border='1'>
<tr>
<th>#</th>
<th>Időpont</th>
<th>Esemény típusa</th>
<th>További paraméterek</th>
</tr>"); // Táblázatkezdés

$sorok = explode("\n", $data); // Soronkénti felbontás

$sId = 0; // 0. sor

foreach ($sorok as &$ertek) { // Soronkénti értelmezés
	$sId++; // A sor száma növekszik 1-gyel
	$sor = explode(';', $ertek); // A sorokat szétvagdossuk a ,-k (vesszők) mentén
	
	print("<tr>"); // Új táblázatsor
	print("<td>" .$sId. "</td>"); // Eseményazonosító (sor száma)
	print("<td>" .@Datum("normal", "m", "dL", "H", "i", "s", $sor[0]). "</td>"); // Időpont
	
	switch ($sor[1]) { // Típus alapján szelektálunk
		case "LOG_CREATE":
			// Napló létrehozva
			print("<td>Napló létrehozva</td>
			<td></td>");
			break;
		case "LOG_SAVED":
			// Napló lementve
			print("<td>Napló lementve</td>
			<td></td>");
			break;
		case "LOG_PURGE":
			// Napló törölve
			print("<td>Napló törölve</td>
			<td></td>");
			break;
		case "WARNING":
		case "ERROR":
		case "CRITICAL":
			// Különböző hibaesemények
			print("<td><b>" .$sor[1]. "</b> típusú hiba</td>
			<td>" .$sor[2]. "<br>" .$sor[3]. "<br>" .$sor[4]. "<br>" .$sor[5]. "<br>" .$sor[6]. "<br>" .$sor[7]);
			
			print("</td>");
			break;
		case "PAGE_VIEW":
			// Lap megtekintve
			print("<td>Lapmegtekintés</td>
			<td>URL: " .$sor[2]. "<br>IP-cím: " .$sor[3]. "<br>HTTP_USER_AGENT: " .$sor[4]. "<br>Felhasználó: " .$sor[5]. " (" .$sor[6]. ")</td>");
			break;
		case "USR_REGISTERED_SUCCESSFULLY":
			// Sikeres regisztráció
			print("<td>Sikeres felhasználói regisztráció</td>
			<td>Felhasználói név: " .$sor[2]. "<br>
			Jelszó: " .$sor[3]. "<br>
			E-mail: " .$sor[4]. "<br>
			Valódi név: " .$sor[5] . "<br>
			Aktiválókulcs: " .$sor[6] ."</td>");
			break;
		case "USR_ACTIVATE":
			// Felhasználóaktiválás
			print("<td>Felhasználó aktiválva</td>
			<td>Felhasználói név: " .$sor[2]. "<br>
			Aktiválókulcs (token): " .$sor[3]. "</td>");
			break;
		case "SQL_CONNECT_SELECTDB":
			// Kapcsolódás, adatbáziskiválasztás
			print("<td>Kapcsolódva az adatbázishoz</td>
			<td></td>");
			break;
		case "SQL_DC":
			// SQL szétkapcsolás
			print("<td>Adatbázis szétkapcsolva</td>
			<td></td>");
			break;
		case "SQL":
			// SQL lekérdezés
			print("<td>SQL lekérdezés</td>
			<td>" .$sor[2]. "</td>");
			break;
		default:
			// Egyéb esemény
			print("<td><b>" .$sor[1]. "</b></td><td>"); // Esemény kódját nyersen kiírjuk
			
			// Meg a paramétereket is
			for ($i = 2; $i <= 6; $i++) {
				print($sor[$i]."<br>\n");
			}
			print("</td>");
	}
	
	print("</tr>
	"); // Sorvége
}

print("</table>
</td><td class='right' valign='top'>"); // Táblázat, td-k zárása
}

} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=log");
}
?>