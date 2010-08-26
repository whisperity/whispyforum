<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/banip.php
   ip cím alapú kitiltás
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>IP-címek kitiltása</h2></center>
<?php
if ( $_POST['action'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$action = $_POST['action'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['action'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$action = $_GET['action'];
	} else {
		// Sehogy nem érkezett adat
		$action = $NULL;
	}
 }

 switch ( $action )
 {
	/* IP kitiltások listázása */
	case $NULL:
	case "":
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."bannedips");
		
		print("<table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>IP</th>
				<th>Kitiltás dátuma</th>
				<th>Felhasználó</th>
				<th>Komment</th>
			</tr>");
		$vanBan = 0; // Alapból nincs kitiltás
		
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$vanBan = 1; // Ha ez a script lefut legalább egyszer, akkor van kitiltás
			
			$sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'"));
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['ip']. "</td>
				<td>" .Datum("normal","kisbetu","dL","H","i","s", $sor['bandate']). "</td>
				<td>" .$sor2['username']. "</td>
				<td>" .$sor['comment']. "</td>
				<td><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<input type='hidden' name='site' value='banip'>
				<input type='hidden' name='action' value='delban'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Ban törlése'>
			</form></td>
			</tr>");
		}
		
		if ( $vanBan == 0 ) // Ha nincs kitiltás, értesítjük a felhasználót
			print("</table><table border='0' cellspacing='1' cellpadding='1'><tr><td><h3 class='postheader'><p class='header'>Nem található IP ban</p></h3></td></tr>");
		
		print("</table><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<input type='hidden' name='site' value='banip'>
				<input type='hidden' name='action' value='newban'>
				<input type='submit' value='Új kitiltás felvétele'>
			</form>");
		break;
		
	case "delban": // Ban törlése
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."bannedips WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
		
		ReturnTo("A kitiltás sikeresen törölve!", "admin.php?site=banip", "Vissza a kitiltott IP-címek listájához", TRUE);
		print("</td><td class='right' valign='top'>");
		Lablec();
		die();
		break;
	
	case "newban": // Új kitiltás felvétele
		if ( $_POST['ip'] == $NULL )
		{
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<p class='formText'>Kitltandó személy IP címe<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a><a class='feature-extra'><span class='hover'><span class='h3'>IP-cím</span>A kitiltandó IP-cím. Alapesetben a jelenlegi IP-címed tartalmazza, de saját magad nem tilthatod ki.<br>Nem tiltható még ki továbbá a <b>127.0.0.1</b>, vagy a <b>localhost</b> cím, és a <b>mysql-adatbázis címe</b> (jelen esetben: <b>" .$cfg['dbhost']. "</b>)</span><sup>?</sup></a>: <input type='text' name='ip' size='18' value='" .$_SERVER['REMOTE_ADDR']. "'><br>
			Komment<a class='feature-extra'><span class='hover'><span class='h3'>Komment</span>Maximum <b>512</b> karakter</span><sup>?</sup></a>: <textarea name='comment' rows='3' cols='40'></textarea>
			<input type='hidden' name='site' value='banip'>
			<input type='hidden' name='action' value='newban'>
			<input type='submit' value='Ban felvétele'>
			</p></form>");
		} else {
			if ( $_POST['ip'] == $_SERVER['REMOTE_ADDR'] )
			{
				Hibauzenet("CRITICAL", "Nem tilthatod ki saját magad!", "A <b>" .$_POST['ip']. "</b> IP-címet szeretted volna kitiltani, ám ez megegyezik az aktuális IP-címeddel.");
				print("<a href='admin.php?site=banip'>Vissza a kitiltott IP címek listájához</a></td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			
			if ( ($_POST['ip'] == "127.0.0.1") || ($_POST['ip'] == "localhost" ) )
			{
				Hibauzenet("CRITICAL", "Nem tilthatod ki a localhostot");
				print("<a href='admin.php?site=banip'>Vissza a kitiltott IP címek listájához</a></td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			
			if ( $_POST['ip'] == $cfg['dbhost']) 
			{
				Hibauzenet("CRITICAL", "Nem tilthatod ki a mysql-adatbázis hostját", "A mysql-adatbázis hostja (" .$cfg['dbhost']. ") nem tiltható ki, mivel az súlyos következményekkel is járhat");
				print("<a href='admin.php?site=banip'>Vissza a kitiltott IP címek listájához</a></td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."bannedips(ip, bandate, uId, comment) VALUES ('" .mysql_real_escape_string($_POST['ip']). "', " .time(). ", " .$_SESSION['userID']. ", '" .mysql_real_escape_string($_POST['comment']). "')");
			ReturnTo("Az IP-cím (" .$_POST['ip']. ") kitiltása sikeres!", "admin.php?site=banip", "Vissza a kitiltott IP-címek listájához", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
		break;
 }
 
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=banip");
}
?>