<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/polls.php
   szavazások kezelése
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Szavazások</h2></center>
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

switch ( $action ) // A bejövő ACTION paraméter szerint nézzük, mi történjen
{
	/* Alapeset */
	case $NULL:
	case "":
		print("A szavazásoknak három állapota van: <b>aktív</b>, <b>függő</b> és <b>archív</b>.<br>
Egyszerre csak egy <b>aktív</b> szavazás lehet, erre tudnak a felhasználók szavazni.<br>
A <b>függő</b> szavazások azok a szavazások, amelyek még nem lettek archiválva. Függő szavazást lehet aktívvá tenni, ilyenkor az aktuális aktív szavazás függő státuszba kerül.<br>
<b>Archív</b> szavazások már nem tehetőek aktívvá, és csak az eredmények tekinthetőek meg.");
		print("<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Cím</th>
				<th>Lehetőségek száma</th>
				<th>Státusz</th>
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['title']. "</td>
				<td>" .$sor['opcount']. "</td>
				<td>");
			
			if ( $sor['type'] == 0 )
				print("függő");
			if ( $sor['type'] == 1)
				print("<b>aktív</b>");
			if ( $sor['type'] == 2)
				print("archív");
			
			print("</td>
			<td>");
			
			if ( $sor['type'] == 0 )
			{
				print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='makeactive'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Aktívvá tétel'>
			</form>");
			}
			
			if ( ( $sor['type'] == 0 ) || ( $sor['type'] == 1 ) )
			{
				print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='archiv'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Archiválás'>
			</form>");
			}
			
			print("</td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='viewopinions'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Lehetőségek megtekintése'>
			</form></td>");
			
			if ( $sor['type'] == 2 )
			{
				print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='edit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>");
			} else {
				print("<td></td>");
			}
			
			print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
			</tr>");
		}
		
		print("</table></div>
		<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='newpoll'>
				<input type='submit' value='Új szavazás hozzáadása'>
			</form>");
		break;
	case "edit": // Szavazás szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				// A szavazás szerkesztésének mentése
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET title='" .mysql_real_escape_string($_POST['title']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
				
				ReturnTo("A szavazás sikeresen szerkesztve", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
				print("</td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			
			print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Szavazás szerkesztése: " .$sor['title']. "</span><br>
		<p class='formText'>Cím: <input type='text' name='title' value='" .$sor['title']. "' size='64'><br>
		<input type='hidden' name='id' value='" .$sor['id']. "'>
		<input type='hidden' name='action' value='edit'>
		<input type='hidden' name='site' value='polls'>
		<input type='submit' name='parancs' value='Szerkeszt'>
		</form>");
			
		}
		
		break;
	}

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=polls");
}
?>