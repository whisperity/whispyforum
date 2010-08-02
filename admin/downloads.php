<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/downloads.php
   letöltések kezelése
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Letöltések</h2></center>
<?php

if ( $_POST['action'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST action lesz az érték
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
 
 switch ($action) // A beérkezett ACTION változó által nézzük, mit kell tennünk
 {
	case $NULL: // Alapeset (nincs beérkező változó)
		// Bevezető szöveg, kategórialista megjelenítése
		print("A most megnyitott bővítmény segítségével kezelheted a letölthető tartalmakat.");
		print("<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Cím</th>
				<th>Leírás</th>
				<th>Letöltések száma</th>
			</tr>");
		
		$kategoriak = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ");
		while ( $sor = mysql_fetch_assoc($kategoriak) )
		{
			print("<tr>
			<td>" .$sor['title']. "</td>
			<td>" .$sor['descr']. "</td>
			<td>" .$sor['files']. "</td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='viewitems'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Letöltések megtekintése'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='editcateg'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='delcateg'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
		</tr>");
		}
		
		print("</table></div>");
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='newcateg'>
				<input type='submit' value='Új kategória hozzáadása'>
			</form>");
		break;
	case "editcateg": // Kategória szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kategoriaadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			print("Minden mezőt ki kell tölteni!\n<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Kategória szerkesztése: " .$kategoriaadat['title']. "</span>
			<p class='formText'>Címsor: <input type='text' name='title' value='" .$kategoriaadat['title']. "'><br>
			Leírás: <textarea name='descr' rows='15' cols='60'>" .$kategoriaadat['descr']. "</textarea></p>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='editcateg_do'>
				<input type='hidden' name='id' value='" .$kategoriaadat['id']. "'>
				<input type='submit' name='parancs' value='Szerkeszt'>
			</form>");
		}
		
		break;
	case "editcateg_do": // Kategóriaszerkesztés futtatása
		if ( ($_POST['id'] == $NULL) || ($_POST['title'] == $NULL) || ($_POST['descr'] == $NULL) )
		{
			Hibauzenet("CRITICAL", "Kötelezően kitöltendő mezők hiányoznak!", "A <b>Címsor</b> és a <b>Leírás</b> mezőt kötelező kitölteni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."download_categ SET title='" .mysql_real_escape_string($_POST['title']). "', descr='" .mysql_real_escape_string($_POST['descr']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
				print("<div class='messagebox'>A kategória szerkesztése megtörtént!<br><a href='admin.php?site=downloads'>Vissza a kategórialistához</a></div>");
			}
		}
		
		break;
	case "delcateg": // Kategória törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			print("<div class='messagebox'>A kategória törlése megtörtént!<br><a href='admin.php?site=downloads'>Vissza a kategórialistához</a></div>");
		}
		
		break;
	case "newcateg": // Új kategória hozzáadása
		print("Minden mezőt ki kell tölteni!\n<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Új kategória hozzáadása</span>
			<p class='formText'>Címsor: <input type='text' name='title'><br>
			Leírás: <textarea name='descr' rows='15' cols='60'></textarea></p>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='newcateg_do'>
				<input type='submit' name='parancs' value='Hozzáad'>
			</form>");
		break;
	case "newcateg_do": // Kategóriahozzáadás futtatása
		if ( ($_POST['title'] == $NULL) || ($_POST['descr'] == $NULL) )
		{
			Hibauzenet("CRITICAL", "Kötelezően kitöltendő mezők hiányoznak!", "A <b>Címsor</b> és a <b>Leírás</b> mezőt kötelező kitölteni!");
		} else {
			if ( $_POST['parancs'] == "Hozzáad" )
			{
				$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."download_categ(title, descr, files) VALUES ('" .mysql_real_escape_string($_POST['title']). "', '" .mysql_real_escape_string($_POST['descr']). "', 0)");
				print("<div class='messagebox'>Az új kategória hozzáadása megtörtént!<br><a href='admin.php?site=downloads'>Vissza a kategórialistához</a></div>");
			}
		}
		
		break;
	case "viewitems": // Letöltések megtekintése egy kategórián belül
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kategoria = mysql_fetch_assoc($sql->Lekerdezes("SELECT title, files FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			print("<h3 class='download-categ'>" .$kategoria['title']. " (" .$kategoria['files']. ")</h3>\n");
			
			print("<br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Cím</th>
				<th>Leírás</th>
				<th>Letöltések száma</th>
				<th>Feltöltés időpontja</th>
				<th>Feltöltő felhasználó neve</th>
			</tr>");
			
			$letoltesek = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."downloads WHERE cid='" .mysql_real_escape_string($_GET['id']). "'");
			while ( $sor = mysql_fetch_assoc($letoltesek) )
			{
				$felhasznaloneve = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uid']. "'"));
				print("<tr>
				<td>" .$sor['title']. "</td>
				<td>" .$sor['descr']. "</td>
				<td>" .$sor['download_count']. "</td>
				<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $sor['upload_date']). "</td>
				<td>" .$felhasznaloneve['username']. "</td>");
				/*print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='editdwl'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='deldwl'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>");*/
			print("</tr>");
			}
			
			print("</table></div>");
			/*print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='newdwl'>
				<input type='submit' value='Új letöltés hozzáadása'>
			</form>");*/
		}
		
		break;
 }
 
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=configs");
}
?>