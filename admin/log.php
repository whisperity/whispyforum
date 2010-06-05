<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/log.php
   naplóértelmezési kód
*/

print("<table border='1'>
<tr>
<th>#</th>
<th>Időpont</th>
<th>Esemény típusa</th>
<th>További paraméterek</th>
</tr>"); // Táblázatkezdés
include("../includes/functions.php");

if ($_GET['nap'] == $NULL)
{
	$nap = time();
}

$data = @file_get_contents("../logs/" .Datum("normal","m","d","","","", $nap). "log"); // Fájl megnyitása

if ( $data === FALSE)
{
	die("A megadott napra (" .Datum("normal","m","d","","","", $nap). ") nincs napló létrehozva");
}
$sorok = explode("\n", $data); // Soronkénti felbontás

$sId = 0; // 0. sor

foreach ($sorok as &$ertek) { // Soronkénti értelmezés
	$sId++; // A sor száma növekszik 1-gyel
	$sor = explode(',', $ertek); // A sorokat szétvagdossuk a ,-k (vesszők) mentén
	
	print("<tr>"); // Új táblázatsor
	print("<td>" .$sId. "</td>"); // Eseményazonosító (sor száma)
	print("<td>" .Datum("normal", "m", "dL", "H", "i", "s", $sor[0]). "</td>"); // Időpont
	
	switch ($sor[1]) { // Típus alapján szelektálunk
		case "LOG_CREATE":
			// Napló létrehozva
			print("<td>Napló létrehozva</td>
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
			<td>IP-cím: " .$sor[2]. "<br>HTTP_USER_AGENT: " .$sor[3]. "</td>");
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
		default:
			// Egyéb esemény
			print("<td><b>" .$sor[1]. "</b></td><td>"); // Esemény kódját nyersen kiírjuk
			
			// Meg a paramétereket is
			for ($i = 2; $i <= 6; $i++) {
				print($sor[$i]."<br>\n");
			}
			print("</td>");
	}
	
	print("</tr>"); // Sorvége
}

print("</table>"); // Táblázat zárása
?>