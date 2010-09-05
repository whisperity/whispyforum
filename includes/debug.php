<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/debug.php
   futtásutáni hibakeresési osztály
*/

/* a hibakeresés ki- és bekapcsolásához lásd
   a /debug.php állományt, a gyökérmappában
*/
 
 class wf_debug // Definiáljuk az osztályt
 {
	var $numDLevents = 0; // Oldalletöltési események száma
	var $dlHistArray = array(); // Oldalletöltési napló tömb
	
	function TimeGet() // Oldalletöltés elkezdése óta eltelt idő visszaadása
	{
		if ( SHOWDEBUG == 1) { // Csak akkor futtatjuk az egészet, ha be van kapcsolva a debuggolás
			// A függvény meghíváskor visszatér az oldal letöltésének megkezdése óta eltelt idővel
			// Hasonló módon, mint ahogy a Footer-ben megjelenik a teljes generálási idő
			global $start_time; // Inicializáskor tárolódik a kezdeti időpont
			
			// Eltelt idő kiszámítása
			$mtime = microtime();
			$mtime = explode(' ',$mtime);
			$current_time = $mtime[1] + $mtime[0];
			
			$genIdo = substr(($current_time - $start_time), 0, 5);
			return $genIdo;
		}
	}
	
	function RegisterDLEvent($eventText) // Oldalletöltési esemény tárolása
	{
		if ( SHOWDEBUG == 1) { // Csak akkor futtatjuk az egészet, ha be van kapcsolva a debuggolás
			$this->numDLevents++; // +1 oldalletöltési esemény
			$this->dlHistArray[$this->numDLevents] = array("time" => $this->TimeGet(), "text" => $eventText);
		}
	}
	
	function GenerateFooterInf() // Debug információk megjelenítése
	{
		if ( SHOWDEBUG == 1) { // Csak akkor jelenítjük meg az egészet, ha be van kapcsolva a debuggolás
		global $cfg, $sql, $start_time;
		// JavaScript a hibakeresési információk megjelenítésére/elrejtésére
		?>
		<script language="javaScript">
		function debugShow(i)
		{
			var blc = document.getElementById(i);
			blc.style.display = (blc.style.display != 'none') ? 'none' : 'block';
		}
		</script>
		<?php
		echo  "\n\n\n\n\n\n\n"; // Forráskód tördelése
		// Stílusinfó, fejléc
		echo '<div style="font-family: Courier New, Fixed; font-size: 13px; text-align: left; width: auto; border: 1px solid black; padding: 6px; background-color: white; color: black; margin: 18px; 0px 60px 0px; ">
		<b>Hibakeresési információk: <a href="#" onclick="debugShow(\'debugPanel\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a></b>';
		echo '<div id="debugPanel" style="text-align: left;">';
		
		// Globális tömbök kilistázása
		echo '<span style="font-size: 12px"><b>Globális tömbök: <a href="#" onclick="debugShow(\'debugGlobals\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a></b></span>';
		echo '<div id="debugGlobals" style="text-align: left;">';
		
		function TombExportacioTablaba($tomb, $tombnev, $tombfelirat)
		{
			if ( count($tomb) != 0 )
			{
				// Ha van legalább 1 érték a tömbben
				echo '<br><b>' .$tombfelirat. '</b> ';	
				
				// Minden táblát külön elrejthetünk
				echo '<a href="#" onclick="debugShow(\'debugGlobals' .$tombnev. '\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a>';
				echo '<div id="debugGlobals' .$tombnev. '" style="text-align: left;">'; // Doboz nyitása
				
				print("<table border='1' style='border: black 1px dotted; font-size: 13px;'>
				<tr>
					<th>Tömb neve:</th>
					<th>" .$tombnev. "</th>
				</tr>
				<tr>
					<th>Név</th>
					<th>Érték</th>
				</tr>"); // Bevezető táblázat
				
				$etomb = var_export($tomb, TRUE); // Exportáció
				$sorok = explode("\n", $etomb); // Sorokra bontás
				
				foreach ($sorok as &$sor)
				{
					if ( ($sor == "array (") || ($sor == ")") )
					{
						// Ha a sor az array( vagy a ) szöveget tartalmazza
						// (a tömb adatait körülvevő megjelnő/exportált szöveg), 
						// akkor nem történik semmi
					} else {
						// Ellenkező esetben igen
						$nincsHozzaRendelJel = explode("' => ", $sor); // Hozzárendelési jel kivágása
						// Kételemű tömböt kapunk, az első elem (0) a változó, a második (1) az érték, de még vannak felesleges jelek
						
						$nincsElotag = explode("  '", $nincsHozzaRendelJel[0]); // Előtag levágása a változó elől
						$valtozo = $nincsElotag[1]; // A változó az előtagmentes érték
						
						$nincsUtotag = explode(",", $nincsHozzaRendelJel[1]); // Levágjuk az érték utótagját
						$ertek = $nincsUtotag[0]; // Az érték már utótag jel mentes
						print("<tr>
							<td>" .$valtozo. "</td>
							<td>" .$ertek. "</td>
						</tr>"); // Kiírás egy új sorba
					}
				}
				
				print("</table>"); // Táblázat zárása
				echo '</div>'; // debugGlobals$tombnev doboz zárása
			}
		}
			// Táblázatba rendezve kilistázgatjuk a szuperglobális tömböket
			// PHP beépített global tömbök
			TombExportacioTablaba($_GET, '$_GET', 'HTTP GET');
			TombExportacioTablaba($_POST, '$_POST', 'HTTP POST');
			TombExportacioTablaba($_SESSION, '$_SESSION', 'Munkamenet');
			TombExportacioTablaba($_FILES, '$_FILES', 'Fájlok');
			TombExportacioTablaba($_SERVER, '$_SERVER', 'Szerver');
			// WhispyFórum portál tömbök
			TombExportacioTablaba($cfg, '$cfg', 'Konfigurációs információk');
			
			// Define információk
			// Minden táblát külön elrejthetünk
			echo '<br><b>DEFINE-konstansok</b>: <a href="#" onclick="debugShow(\'debugDefines\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a>';
			echo '<div id="debugDefines" style="text-align: left;">'; // Doboz nyitása
			
			print("<table border='1' style='border: black 1px dotted; font-size: 13px;'>
			<tr>
				<th>Név</th>
				<th>Érték</th>
			</tr>"); // Bevezető táblázat
		
			print("<tr>
				<td>RELEASE_TYPE</td>
				<td>" .RELEASE_TYPE. "</td>
			</tr>
			<tr>
				<td>VERSION</td>
				<td>" .VERSION. "</td>
			</tr>
			<tr>
				<td>RELEASE_DATE</td>
				<td>" .RELEASE_DATE. "</td>
			</tr>
			<tr>
				<td>ALLOW_REGISTRATION</td>
				<td>" .ALLOW_REGISTRATION. "</td>
			</tr>
			<tr>
				<td>SHOWDEBUG</td>
				<td>" .SHOWDEBUG. "</td>
			</tr>
			<tr>
				<td>FACEBOOK_LIKE</td>
				<td>" .FACEBOOK_LIKE. "</td>
			</tr>
			<tr>
				<td>GOOGLE_ANALYTICS</td>
				<td>" .GOOGLE_ANALYTICS. "</td>
			</tr>
			<tr>
				<td>GOOGLE_ANALYTICS_ID</td>
				<td>" .GOOGLE_ANALYTICS_ID. "</td>
			</tr>
			<tr>
				<td>DOWNLOAD_MINLVL</td>
				<td>" .DOWNLOAD_MINLVL. "</td>
			</tr>"); // A WF által használt define-ok kiírása
		
		echo '</table>'; // Táblázat zárás
		echo '</div>'; // debugDefines zárás
		echo '</div>'; // debugGlobals zárás 
		
		// SQL információk
		echo '<br><span style="font-size: 12px"><b>SQL: <a href="#" onclick="debugShow(\'debugSQL\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a></b></span>';
		echo '<div id="debugSQL" style="text-align: left;">'."\n";
		
		echo '<b>Lefuttatott SQL kérések száma: </b>' .$sql->querys. "<br>\n";
		
		echo '<br><b>SQL-kérések</b> ';	
		
		// Minden táblát külön elrejthetünk
		echo '<a href="#" onclick="debugShow(\'debugSQLquerys\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a>';
		echo '<div id="debugSQLquerys" style="text-align: left;">'; // Doboz nyitása
		
		print("<table style='font-size: 13px;'>
		<tr>
			<th>ID</th>
			<th>SQL kérés</th>
			<th>Sikeresség/Hiba</th>
		</tr>"); // Bevezető táblázat
		
		for ($k = 1; $k <= $sql->querys; $k++)
		{
			echo "<tr>
			<td><a name='sql" .$sql->queryArray[$k]["id"]. "'></a>" .$sql->queryArray[$k]["id"]. "</td>
			<td>" .$sql->queryArray[$k]["query"]. "</td>";
			
			if ( $sql->queryArray[$k]["completed"] == 1 ) {
				echo "<td>Igen</td>";
			} else {
				echo "<td>Nem<br>Hiba:<br>" .$sql->queryArray[$k]["mysql_error"]. "</td>";
			}
			
			echo '</tr>';
		}
		echo '</table>'; // Táblázat zárása
		
		echo '</div>'; // debugSQLquerys zárás
		echo '</div>'; // debugSQL zárás
		
		// Oldal letöltés/generálás történet
		echo '<br><span style="font-size: 12px"><b>Letöltés/generálás-történet: <a href="#" onclick="debugShow(\'debugHistory\'); return false;"><abbr title="Doboz megjelenítése/elrejtése">[+/-]</abbr></a></b></span>';
		echo '<div id="debugHistory" style="text-align: left;">'."<br>\n";
		echo '<b>Naplózott események száma:</b> ' .$this->numDLevents. "<br>\n";
		
		echo "<table border='1' style='border: black 1px dotted; font-size: 13px;'>
		<tr>
			<th>Idő/eltolódás (sec)</th>
			<th>Szöveg</th>
		</tr>\n";
		echo "<tr>
			<td>" .$start_time. "</td>
			<td>Oldalletöltés megkezdve<br>Globális paraméterek átadása megtörtént</td>
		</tr>";
		
		for ($i = 1; $i <= $this->numDLevents; $i++)
		{
			echo "<tr>
			<td>+ " .$this->dlHistArray[$i]["time"]. "</td>
			<td>" .$this->dlHistArray[$i]["text"]. "</td>
		</tr>";
		}
		
		$mtime = microtime();
		$mtime = explode(' ',$mtime);
		$current_time = $mtime[1] + $mtime[0];
		
		echo '<tr>
		<td>' .$current_time. '</td>
		<td>Oldal generálása befejezve</td>
		</tr>';
		
		echo '</table>'; // Táblázat zárása
		echo '</div>'; // debugHistory zárás
		echo '</div>'; // debugPanel zárás
	}
	}
 }
 
 // Létrehozzuk a globális $wf_debug változót
 // mellyel meghívhatjuk a függvényeket
 global $wf_debug;
 $wf_debug = new wf_debug();
?>