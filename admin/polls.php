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
			
			if ( $sor['type'] == 1 )
			{
				print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='makepending'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Függővé tétel'>
			</form>");
			}
			
			if ( ( $sor['type'] == 0 ) || ( $sor['type'] == 1 ) )
			{
				print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='archive'>
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
			
			if ( ( $sor['type'] == 0 ) || ( $sor['type'] == 1) )
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
	case "archive": // Szavazás archiválása
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			// Kiválasztot szavazás archívvá tétele
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET type=2 WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			
			ReturnTo("A szavazás archiválása sikeres", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
		break;
	case "makeactive": // Aktívvá tétel
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			// Első lépésként az összes többi aktív szavazást függővé tesszük
			$aktivszavazasok = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE type=1"); // Aktív szavazások
			
			while ( $szav = mysql_fetch_assoc($aktivszavazasok) ) { // Egyesével mindet függővé tesszük
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET type=0 WHERE id='" .$szav['id']. "'");
			}
			
			// Kiválasztott szavazás aktívvá tétele
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET type=1 WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			
			ReturnTo("A szavazás aktívvá tétele sikeres", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
		break;
	case "makepending": // Függővé tétel
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			// Kiválasztot szavazás függővé tétele
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET type=0 WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			
			ReturnTo("A szavazás függővé tétele sikeres", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
		break;
	case "delete": // Szavazás törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			// Kitöröljük a szavazással együtt az
			// összes lehetőséget a szavazáshoz rendelve,
			// az összes leadott szavazatot
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."votes_cast WHERE pollid='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."poll_opinions WHERE pollid='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."polls WHERE id='" .mysql_real_escape_string($_GET['id']). "'"); // Végül a szavazást is
			
			ReturnTo("A szavazás törölve", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
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
	case "viewopinions": // Szavazati lehetőségek megtekintése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			
			print("<a href='admin.php?site=polls'><< Vissza</a><br>
			Szavazati opciók a szavazáson: <b>" .$szavazas['title']. "</b>
			<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Szöveg</th>
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."poll_opinions WHERE pollid='" .mysql_real_escape_string($_GET['id']). "'");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['opinion']. "</td>
				<td>");
			
			/*if ( ( $szavazas['type'] == 0 ) || ( $szavazas['type'] == 1) )
			{
				print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='op_edit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='op_delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
			</tr>");
			} else {
				print("<td></td>");
			}*/
		}
		
		print("</table></div>");
		
		if ( ( $szavazas['type'] == 0 ) || ( $szavazas['type'] == 1) )
		{
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='polls'>
				<input type='hidden' name='action' value='newopinion'>
				<input type='hidden' name='pollid' value='" .$szavazas['id']. "'>
				<input type='submit' value='Új szavazat hozzáadása'>
			</form>");
		}
		
		}
		
		break;
	/*
	case "op_edit": // Szavazati lehetőség szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."poll_opinions WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .$sor['pollid']. "'"));
			
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				// A szavazati lehetőség szerkesztésének mentése
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."poll_opinions SET opinion='" .mysql_real_escape_string($_POST['opinion']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
				
				ReturnTo("A szavazati lehetőség sikeresen szerkesztve", "admin.php?site=polls&action=viewopinions&id=" .$sor['pollid'], "Vissza a lehetőségekhez", TRUE);
				print("</td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			
			if ( ( $szavazas['type'] == 0 ) || ( $szavazas['type'] == 1 ) )
			{
				print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Szavazati lehetőség szerkesztése: " .$sor['opinion']. "</span><br>
		<p class='formText'>Lehetőség: <input type='text' name='opinion' value='" .$sor['opinion']. "' size='64'><br>
		<input type='hidden' name='id' value='" .$sor['id']. "'>
		<input type='hidden' name='action' value='op_edit'>
		<input type='hidden' name='site' value='polls'>
		<input type='submit' name='parancs' value='Szerkeszt'>
		</form>");
			} else {
				Hibauzenet("CRITICAL", "A szavazati lehetőség nem szerkeszthető, mivel a szavazás már archív!");
			}
		}
		
		break;
	case "op_delete": // Szavazati lehetőség törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			
			$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."poll_opinions WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .$sor['pollid']. "'"));
			
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET opcount='" .($szavazas['opcount']-1). "' WHERE id='" .$sor['pollid']. "'"); // Kivonunk az összes szavazat számából egyet
			
			// Kitöröljük az adott szavazati opcióra adott szavazatokat
			// De előbb bekell olvasnunk pár szükséges adatot a megfelelő SQL-kérés létrehozásához
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."votes_cast WHERE pollid='" .mysql_real_escape_string($sor['pollid']). "' AND opinionid='" .mysql_real_escape_string($sor['opinionid']). "'");
			
			// Szavazati lehetőség törlése
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."poll_opinions WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			
			ReturnTo("A szavazati lehetőség törölve", "admin.php?site=polls&action=viewopinions&id=" .$sor['pollid'], "Vissza a lehetőségekhez", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
		break;
	*/
	case "newopinion": // Új szavazati lehetőség hozzáadása
		if ( $_GET['pollid'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .$_GET['pollid']. "'"));
			
			if ( $_POST['parancs'] == "Hozzáad" )
			{
				// A szavazati lehetőség szerkesztésének mentése
				
				$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."poll_opinions(pollid, opinionid, opinion) VALUES ('" .mysql_real_escape_string($_GET['pollid']). "', '" .mysql_real_escape_string(($szavazas['opcount']+1)). "', '" .mysql_real_escape_string($_POST['opinion']). "')");
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."polls SET opcount='" .($szavazas['opcount']+1). "' WHERE id='" .mysql_real_escape_string($_GET['pollid']). "'");
				
				ReturnTo("A szavazati lehetőség hozzáadva", "admin.php?site=polls&action=viewopinions&id=" .$_GET['pollid'], "Vissza a lehetőségekhez", TRUE);
				print("</td><td class='right' valign='top'>");
				Lablec();
				die();
			}
			
			if ( ( $szavazas['type'] == 0 ) || ( $szavazas['type'] == 1 ) )
			{
				print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Szavazati lehetőség hozzáadása: " .$szavazas['title']. "</span><br>
		<p class='formText'>Lehetőség neve: <input type='text' name='opinion' size='64'><br>
		<input type='hidden' name='pollid' value='" .$_GET['pollid']. "'>
		<input type='hidden' name='action' value='newopinion'>
		<input type='hidden' name='site' value='polls'>
		<input type='submit' name='parancs' value='Hozzáad'>
		</form>");
			} else {
				Hibauzenet("CRITICAL", "A szavazati lehetőség nem szerkeszthető, mivel a szavazás már archív!");
			}
		}
		
		break;
	case "newpoll": // Új szavazás hozzáadása
		$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .$_GET['pollid']. "'"));
		
		if ( $_POST['parancs'] == "Hozzáad" )
		{
			// Az új szavazás hozzáadása
			
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."polls(title, type, opcount) VALUES ('" .mysql_real_escape_string($_POST['title']). "', 0, 0)");
			
			ReturnTo("A szavazás hozzáadva", "admin.php?site=polls", "Vissza a szavazásokhoz", TRUE);
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		
			print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Új szavazás hozzáadása</span><br>
		<p class='formText'>Szavazás címe: <input type='text' name='title' size='64'><br>
		<input type='hidden' name='action' value='newpoll'>
		<input type='hidden' name='site' value='polls'>
		<input type='submit' name='parancs' value='Hozzáad'>
		</form>");
		
		break;
	}

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=polls");
}
?>