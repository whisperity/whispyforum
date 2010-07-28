<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/plain.php
   statikus tartalmak kezelése
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Statikus tartalmak</h2></center>
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
		print("A statikus tartalmak olyan &quot;weblapok&quot;, melyek egyszerű szöveget tartalmaznak (BB-kódokkal formázva), lényegi HTML vagy PHP (script) kódot nem. Ezek lehetnek impresszumok, listák, szabályzatok, minden olyan elem, melyhez nem szükséges futtatható kódot írni.<br>\nA statikus tartalmakat az adatbázis tárolja, és egy keretkód jeleníti meg, hogy ne kelljen külön weblapfájlokat a rendszerbe másolni.<br>\n<br>\nA statikus tartalmak megjelenítéséhez linkekben, menüelemekben mindig a <code>/plain.php?id=<i>ID</i></code> elérési úttal hivatkozunk, ahol <i>ID</i> a táblázatban a tartalomhoz hozzárendelt id értéke.<br>\nHa azt szeretnéd, hogy a menüben a statikus tartalom linkje a tartalom címét vegye fel, a menüelem hozzáadásakor a <b>Címsor</b> mezőbe <code>[plain]</code>-t írj. Ebben az esetben a szöveg megjelenítéskor lecserélődik az aktuális címre.");
		print("<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Cím</th>
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."plain");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['title']. "</td>
				<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='plain'>
				<input type='hidden' name='action' value='edit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Módosítás'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='plain'>
				<input type='hidden' name='action' value='delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
			</tr>");
		}
		
		print("</table></div>
		<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='plain'>
				<input type='hidden' name='action' value='new'>
				<input type='submit' value='Új statikus tartalom hozzáadása'>
			</form>");
		break;
	case "edit": // Szerkesztés
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$statikus = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."plain WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			
			print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Statikus tartalom szerkesztése</span>
			<p class='formText'>Címsor: <input type='text' name='title' value='" .$statikus['title']. "'><br>
			Szöveg:<br>
			<textarea name='content' rows='20' cols='60'>" .$statikus['content']. "</textarea></p>
			<input type='hidden' name='site' value='plain'>
			<input type='hidden' name='action' value='doedit'>
			<input type='hidden' name='id' value='" .$_GET['id']. "'>
			<input type='submit' value='Módosítás'>
		</form>");
		}
		break;
	case "doedit": // Szerkesztés lebonyolítása (SQL)
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."plain SET title='" .mysql_real_escape_string($_POST['title']). "', content='" .mysql_real_escape_string($_POST['content']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
		print("<div class='messagebox'>Szerkesztés sikeres!<br>\n<a href='admin.php?site=plain'>Vissza a listához</a></div>");
		break;
	case "delete": // Modul törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."plain WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			print("<div class='messagebox'>A statikus tartalom sikeresen törölve!<br><a href='admin.php?site=plain'>Vissza a listához</a></td><td class='right' valign='top'>");
			Lablec();
			die();
		}
		break;
	case "new": // Új tartalom
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Statikus tartalom hozzáadása</span>
			<p class='formText'>Címsor: <input type='text' name='title'><br>
			Szöveg:<br>
			<textarea name='content' rows='20' cols='60'></textarea></p>
			<input type='hidden' name='site' value='plain'>
			<input type='hidden' name='action' value='donew'>
			<input type='submit' value='Hozzáadás'>
		</form>");
		break;
	case "donew": // Új tartalom (SQL)
		if ( ($_POST['title'] == $NULL) || ($_POST['content'] == $NULL) )
		{
			Hibauzenet("ERROR", "Kötelező mezők hiányoznak!", "Nem töltöttél ki minden mezőt!");
			Lablec();
			die();
		} else {
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."plain(title, content) VALUES('" .mysql_real_escape_string($_POST['title']). "', '" .mysql_real_escape_string($_POST['content']). "')");
			print("<div class='messagebox'>A statikus tartalom hozzáadva!<br><a href='admin.php?site=plain'>Vissza a listához</a></td><td class='right' valign='top'>");
		}
		break;
}
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=configs");
}
?>