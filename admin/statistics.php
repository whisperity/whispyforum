<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/statistics.php
   statisztikai adatok
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Statisztika</h2></center>
<a href="admin.php?site=statistics&mode=years" alt="Év-hónap-nap bontású statisztika listázása">Időalapú statisztika</a> • 
<?php

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
 
 $gdate = getdate();
 
 echo '<a href="admin.php?site=statistics&mode=months&year=' .$gdate['year']. '" alt="Az idei év részletei">' .$gdate['year']. '</a>';
 echo ' • ';
 echo '<a href="admin.php?site=statistics&mode=days&year=' .$gdate['year']. '&month=' .$gdate['mon']. '" alt="Az aktuális hónap részletei">' .$honapok[$gdate['mon']]. '</a>';
 echo ' • ';
 echo '<a href="admin.php?site=statistics&mode=ip_days&year=' .$gdate['year']. '&month=' .$gdate['mon']. '&day=' .$gdate['mday']. '" alt="A mai nap információinak megjelenítése">Ma (' .$gdate['mday']. '.)</a>';
?>
<br>
<a href="admin.php?site=statistics&mode=ip" alt="A látogatók IP-címeinek listázása">IP-cím statisztika</a> • 

<?php
echo '<a href="admin.php?site=statistics&mode=ip_details&ip=' .$_SERVER['REMOTE_ADDR']. '" alt="Saját IP-cím adatinak megtekintése">' .$_SERVER['REMOTE_ADDR']. '</a>';
?>
<br style="clear: both"><br style="clear: both">

<?php
// ?mode= állítja a megtekintendő módot
switch ( $_GET['mode'] ) {
	case "": // Semmi
	case $NULL: // Semmi
	case "years": // Évek
	case "ys": // Évek (rövidítés)
		// Az évek megjelenítése
		
		print("Kiválasztott időszak: <b>1999-2020</b>.<br>Kattints az adott év számára a bővebb statisztikáért.<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Év</th>
				<th>Látogatók száma</th>
				<th>Arány</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("Éves statisztika generálása megkezdve");
		
		$eveslatogatas = array();
		$osszes = 0;
		for ($ev = 1999; $ev <= 2020; $ev++) {
			$result = $sql->Lekerdezes("SELECT year FROM " .$cfg['tbprf']."statistics WHERE year=" .$ev);
			$wf_debug->RegisterDLEvent($ev. ". év lekérdezve, megtekintések száma: " .mysql_num_rows($result));
			$eveslatogatas[$ev]['latogatok'] = mysql_num_rows($result);
			
			$osszes = $osszes + mysql_num_rows($result);
			mysql_free_result($result);
		}
		
		for ( $ev2 = 1999; $ev2 <= 2020; $ev2++ ) {
			if ( $eveslatogatas[$ev2]['latogatok'] != 0 )
			{
				$arany =  $eveslatogatas[$ev2]['latogatok'] / ( $osszes / 100 );
				print("<tr>
					<td><a href='admin.php?site=statistics&mode=months&year=" .$ev2. "' alt='Bővebb statisztika megtekintése az adott évről'>" .$ev2. "</a></td>
					<td>" .$eveslatogatas[$ev2]['latogatok']. "</td>
					<td>" .round($arany, 2). "%</td>
				</tr>");
				$wf_debug->RegisterDLEvent($ev2. " adatai kiírva a táblázatba");
			} elseif ( $eveslatogatas[$ev2]['latogatok'] == 0 ) {
				$wf_debug->RegisterDLEvent($ev2. ". évben nem látogatta meg a weboldalt senki");
			}
		}
		
		print("<tr>
			<td><b>Összesen:</b></td>
			<td><b>" .$osszes. "</b></td>
			<td><b>100%</b></td>
		</tr></table></div>");
		
		$wf_debug->RegisterDLEvent("Éves statisztika generálása befejezve");
		
		break;
	case "months": // Hónapok
	case "ms": // Hónapok (rövidítés)
		// A hónapok megjelenítése egy adott évben
		
		if ( $_GET['year'] == $NULL ) // Első lépésként nem fut a szkript ha nem adtuk meg az évet
		{
			Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt év számát!");
		} else {
		
		print("Kiválasztott időszak: <b>" .$_GET['year']. ".</b><br>Kattints az adott hónap nevére a bővebb statisztikáért.<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Hónap</th>
				<th>Látogatók száma</th>
				<th>Arány</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("Havi statisztika generálása megkezdve a(z) " .$_GET['year']. ". évben");
		
		$havilatogatas = array();
		$osszes = 0;
		for ($honap = 1; $honap <= 12; $honap++) {
			$result = $sql->Lekerdezes("SELECT year, month FROM " .$cfg['tbprf']."statistics WHERE year=" .mysql_real_escape_string($_GET['year']). " AND month=" .$honap);
			$wf_debug->RegisterDLEvent("Hónap " .$honap. " lekérdezve a(z) " .$_GET['year']. ". évben, megtekintések száma: " .mysql_num_rows($result));
			$havilatogatas[$honap]['latogatok'] = mysql_num_rows($result);
			
			$osszes = $osszes + mysql_num_rows($result);
			mysql_free_result($result);
		}
		
		for ( $honap2 = 1; $honap2 <= 12; $honap2++ ) {
			if ( $havilatogatas[$honap2]['latogatok'] != 0 )
			{
				$arany =  $havilatogatas[$honap2]['latogatok'] / ( $osszes / 100 );
				print("<tr>
					<td><a href='admin.php?site=statistics&mode=days&year=" .$_GET['year']. "&month=" .$honap2. "' alt='Bővebb statisztika megtekintése az adott hónapról'>" .$honapok[$honap2]. "</a></td>
					<td>" .$havilatogatas[$honap2]['latogatok']. "</td>
					<td>" .round($arany, 2). "%</td>
				</tr>");
				$wf_debug->RegisterDLEvent($honapok[$honap2]. " hónap (év: " .$_GET['year']. ") adatai kiírva a táblázatba");
			} elseif ( $havilatogatas[$honap2]['latogatok'] == 0 ) {
				$wf_debug->RegisterDLEvent($honapok[$honap2]. " hónapban (év: " .$_GET['year']. ") nem látogatta meg a weboldalt senki");
			}
		}
		
		print("<tr>
			<td><b>Összesen:</b></td>
			<td><b>" .$osszes. "</b></td>
			<td><b>100%</b></td>
		</tr></table></div>");
		}
		
		$wf_debug->RegisterDLEvent("Havi statisztika generálása befejezve");
		
		break;
	case "days": // Napok
	case "ds": // Napok (rövidítés)
		// A napok megjelenítése egy adott év adott hónapjában
		
		if ( ($_GET['year'] == $NULL) || ($_GET['month'] == $NULL) ) // Első lépésként nem fut a szkript ha nem adtuk meg az évet és/vagy hónapot
		{
			// A felhasználót bővebb módon tájékoztatjuk
			if ( $_GET['year'] == $NULL)
				Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt év számát!");
			if ( $_GET['month'] == $NULL)
				Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt hónap számát!");
		} else {
		
		print("Kiválasztott időszak: <b>" .$_GET['year']. ". " .$honapok[$_GET['month']]. ".</b><br>Kattints az adott nap számára a bővebb statisztikáért.<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Nap</th>
				<th>Látogatók száma</th>
				<th>Arány</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("Napi statisztika generálása megkezdve a(z) " .$_GET['year']. ". évben, " .$honapok[$_GET['month']]. " hónapban");
		
		$napilatogatas = array();
		$osszes = 0;
		for ($nap = 1; $nap <= 31; $nap++) {
			$result = $sql->Lekerdezes("SELECT year, month, day FROM " .$cfg['tbprf']."statistics WHERE year=" .mysql_real_escape_string($_GET['year']). " AND month=" .mysql_real_escape_string($_GET['month']). " AND day=" .$nap);
			$wf_debug->RegisterDLEvent("Nap " .$nap. " lekérdezve a(z) " .$honapok[$_GET['month']]. "hónapban (év: " .$_GET['year']. ") megtekintések száma: " .mysql_num_rows($result));
			$napilatogatas[$nap]['latogatok'] = mysql_num_rows($result);
			
			$osszes = $osszes + mysql_num_rows($result);
			mysql_free_result($result);
		}
		
		for ( $nap2 = 1; $nap2 <= 31; $nap2++ ) {
			if ( $napilatogatas[$nap2]['latogatok'] != 0 )
			{
				$arany =  $napilatogatas[$nap2]['latogatok'] / ( $osszes / 100 );
				print("<tr>
					<td><a href='admin.php?site=statistics&mode=ip_days&year=" .$_GET['year']. "&month=" .$_GET['month']. "&day=" .$nap2. "' alt='Bővebb statisztika megtekintése az adott napon'>" .$nap2. "</a></td>
					<td>" .$napilatogatas[$nap2]['latogatok']. "</td>
					<td>" .round($arany, 2). "%</td>
				</tr>");
				$wf_debug->RegisterDLEvent($nap2. ". nap adatai kiírva a táblázatba (hónap: " .$honapok[$_GET['month']]. ", év: " .$_GET['year']. ")");
			} elseif ( $napilatogatas[$nap2]['latogatok'] == 0 ) {
				$wf_debug->RegisterDLEvent($_GET['year']. ". év " .$honapok[$_GET['month']]. " hónap " .$nap2. ". napján nem látogatta meg a weboldalt senki");
			}
		}
		
		print("<tr>
			<td><b>Összesen:</b></td>
			<td><b>" .$osszes. "</b></td>
			<td><b>100%</b></td>
		</tr></table></div>");
		}
		
		$wf_debug->RegisterDLEvent("Napi statisztika generálása befejezve");
		
		break;
	case "ip_days": // IP-címek / nap
	case "ipds": // IP-címek / nap (rövidítés)
		// A látogatók IP-címeinek megjelenítése egy adott év adott hónapjának adott napján
		
		if ( ($_GET['year'] == $NULL) || ($_GET['month'] == $NULL) || ($_GET['day'] == $NULL) ) // Első lépésként nem fut a szkript ha nem adtuk meg az évet és/vagy hónapot és/vagy napot
		{
			// A felhasználót bővebb módon tájékoztatjuk
			if ( $_GET['year'] == $NULL)
				Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt év számát!");
			if ( $_GET['month'] == $NULL)
				Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt hónap számát!");
			if ( $_GET['day'] == $NULL)
				Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt nap számát!");
		} else {
		
		print("Kiválasztott időszak: <b>" .$_GET['year']. ". " .$honapok[$_GET['month']]. " " .$_GET['day']. ".</b><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>IP-cím</th>
				<th>Első látogatás időpontja</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("IP-statisztika generálása megkezdve a(z)" .$_GET['year']. " év " .$honapok[$_GET['month']]. " hónapjának " .$_GET['day']. " napjáról");
		
		$result = $sql->Lekerdezes("SELECT ip, year, month, day, epoch FROM " .$cfg['tbprf']."statistics WHERE year=" .mysql_real_escape_string($_GET['year']). " AND month=" .mysql_real_escape_string($_GET['month']). " AND day=" .mysql_real_escape_string($_GET['day']));
		
		while ( $row = mysql_fetch_assoc($result) ) {
			print("<tr>
				<td><a href='admin.php?site=statistics&mode=ip_details&ip=" .$row['ip']. "' alt='Részletek megtekintése az IP-címről'>" .$row['ip']. "</a></td>
				<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $row['epoch']). "</td>
			</tr>");
		}
		
		print("</table></div>");
		}
		
		$wf_debug->RegisterDLEvent("Statisztika generálása befejezve");
		
		break;
	case "ip_details": // IP-cím részletei
	case "ipd": // IP-cím részletei (rövidítés)
		// Információk megjelenítése egy adott IP-címről
		
		if ( $_GET['ip'] == $NULL ) // Első lépésként nem fut a szkript ha nem adtuk meg az ip-címet
		{
			Hibauzenet("CRITICAL", "Hiányzó paraméterek!", "Meg kell adni a kívánt IP-címet!");
		} else {
		
		print("Kiválasztott tartomány: <b>IP-cím " .$_GET['ip']. ".</b><br>");
		
		print("<div class='userbox'><a href='http://whois.domaintools.com/" .$_GET['ip']. "' alt='IP-cím információk lekérdezése' target='blank'>Whois</a><br><table cellspacing='1' cellpadding='1'>
			<tr>
				<th>Látogatások időpontja</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("IP-statisztika generálása megkezdve a következő IP-címre: " .$_GET['ip']);
		
		$iplatogatas = array();
		$osszes = 0;
		
		$result = $sql->Lekerdezes("SELECT epoch FROM " .$cfg['tbprf']."statistics WHERE ip='" .mysql_real_escape_string($_GET['ip']). "'");
		
		while ( $row = mysql_fetch_assoc($result) ) {
			print("<tr>
				<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $row['epoch']). "</td>
			</tr>");
			$osszes++;
		}
		
		print("<tr>
			<td><b>Összesen:</b></td>
			<td><b>" .$osszes. "</b></td>
		</tr></table></div>");
		}
		
		$wf_debug->RegisterDLEvent("IP-statisztika generálása befejezve");
		
		break;
	case "ip": // IP-címek listázása
		// IP-címek kilistázása
		print("Kiválasztott tartomány: <b>IP-címek</b>.<br>Kattints az adott IP-címre a bővebb statisztikáért.<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>IP-cím</th>
				<th>Látogatások száma</th>
				<th>Arány</th>
			</tr>");
		
		$wf_debug->RegisterDLEvent("IP-összesítő generálása megkezdve");
		
		$iplatogatas = array();
		$osszes = 0;
		
		$result = $sql->Lekerdezes("SELECT COUNT(*), ip FROM " .$cfg['tbprf']."statistics GROUP BY ip");
		
		while($row = mysql_fetch_assoc($result) ) {
			$iplatogatas[$osszes]['ip'] = $row['ip'];
			$iplatogatas[$osszes]['latogat'] = $row['COUNT(*)'];
			
			$osszes++;
		}
		
		for ( $ip2 = 0; $ip2 <= ($osszes - 1); $ip2++) {
			$arany =  $iplatogatas[$ip2]['latogat'] / ( $osszes / 100 );
			
			print("<tr>
				<td><a href='admin.php?site=statistics&mode=ip_details&ip=" .$iplatogatas[$ip2]['ip']. "' alt='Részletek megtekintése az IP-címről'>" .$iplatogatas[$ip2]['ip']. "</a></td>
				<td>" .$iplatogatas[$ip2]['latogat']. "</td>
				<td>" .round($arany, 2). "%</td>
			</tr>");
		}
		
		print("<tr>
			<td><b>Összesen:</b></td>
			<td><b>" .$osszes. "</b></td>
			<td><b>100%</b></td>
		</tr></table></div>");
		
		$wf_debug->RegisterDLEvent("IP-összesítő generálása befejezve");
		
		break;
}
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=statistics");
}
?>