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
		print("A most megnyitott bővítmény segítségével kezelheted a letölthető tartalmakat.<br><a href='admin.php?site=downloads&action=viewitems&id=0' alt='Kategorizálatlan letöltések'>A kategória nélküli letöltések megtekintéséhez kattints ide</a>.");
		print("<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Cím</th>
				<th>Leírás</th>
				<th>Fájlok száma</th>
				<th>Letöltések száma összesen</th>
				<th>Fájlok mérete összesen</th>
			</tr>");
		
		$kategoriak = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ");
		while ( $sor = mysql_fetch_assoc($kategoriak) )
		{
			print("<tr>
			<td>" .$sor['title']. "</td>
			<td>" .$sor['descr']. "</td>
			<td>" .$sor['files']. "</td>");
			
			$letoltesosszes = 0;
			$meretosszes = 0;
			$letoltesekInCateg = $sql->Lekerdezes("SELECT href, download_count FROM " .$cfg['tbprf']."downloads WHERE cid='" .$sor['id']. "'");
			while ( $dwlsor = mysql_fetch_assoc($letoltesekInCateg) )
			{
				$letoltesosszes += $dwlsor['download_count'];
				$meretosszes += @filesize("uploads/" .md5($dwlsor['href']));
			}
			
			print("<td>" .$letoltesosszes. "</td>
			<td>" .DecodeSize($meretosszes). "</td>
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
				ReturnTo("A kategória szerkesztése megtörtént!", "admin.php?site=downloads", "Vissza a kategórialistához", TRUE);
			}
		}
		
		break;
	case "delcateg": // Kategória törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."downloads SET cid='0' WHERE cid='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			ReturnTo("A kategória törlése megtörtént!", "admin.php?site=downloads", "Vissza a kategórialistához", TRUE);
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
				ReturnTo("Az új kategória hozzáadása megtörtént!", "admin.php?site=downloads", "Vissza a kategórialistához", TRUE);
			}
		}
		
		break;
	case "viewitems": // Letöltések megtekintése egy kategórián belül
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kategoria = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, title, files FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			
			if ( $_GET['id'] != 0 )
			{
				print("<h3 class='download-categ'>" .$kategoria['title']. " (" .$kategoria['files']. ")</h3>\n");
			} elseif ( $_GET['id'] == 0 ) {
				print("<h3 class='download-categ'>Kategória nélküli letöltések</h3>\n");
			}
			
			print("<br><a href='admin.php?site=downloads'>Vissza a kategóriákhoz</a><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>Cím</th>
				<th>Leírás</th>
				<th>Letöltések száma</th>
				<th>Fájl mérete</th>
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
				<td>" .DecodeSize(@filesize("uploads/" .md5($sor['href']))). "</td>
				<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $sor['upload_date']). "</td>
				<td>" .$felhasznaloneve['username']. "</td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
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
			</form></td>
			</tr>");
			}
			
			print("</table></div>");
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='newdwl'>
				<input type='hidden' name='cid' value='" .$kategoria['id']. "'>
				<input type='submit' value='Új letöltés hozzáadása'>
			</form>");
		}
		
		break;
	case "editdwl": // Letöltés szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$letoltesadatok = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($letoltesadatok['uid']). "'"));
			$aktualiskategoria = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, title FROM " .$cfg['tbprf']."download_categ WHERE id='" .$letoltesadatok['cid']. "'"));
			
			print("Minden mezőt ki kell tölteni!\n<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Letöltés szerkesztése: " .$letoltesadatok['title']. "</span>
			<p class='formText'>Cím: <input type='text' name='title' value='" .$letoltesadatok['title']. "' size='50'><br>
			Hivatkozás: " .$letoltesadatok['href']. "<br>
			Fájlméret: " .DecodeSize(@filesize("uploads/" .md5($letoltesadatok['href']))). "<br>
			Leírás: <textarea name='descr' rows='15' cols='60'>" .$letoltesadatok['descr']. "</textarea><br>
			Feltöltő neve: " .$felhasznalo['username']. "<br>
			Feltöltés időpontja: " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $letoltesadatok['upload_date']). "<br>
			Letöltések száma: " .$letoltesadatok['download_count']. "<br>
			Áthelyezés másik kategóriába: <select name='newcateg_id'>");
			
			print("<option value='" .$aktualiskategoria['id']. "'>(Nincs áthelyezés) " .$aktualiskategoria['title']. "</option>\n");
			
			$kategoriak = $sql->Lekerdezes("SELECT id, title FROM " .$cfg['tbprf']."download_categ WHERE id <> " .$letoltesadatok['cid']);
			
			while ( $ksor = mysql_fetch_assoc($kategoriak) ) {
				print("<option value='" .$ksor['id']. "'>" .$ksor['title']. "</option>\n");
			}
			
			print("</select></p>
				<input type='hidden' name='site' value='downloads'>
				<input type='hidden' name='action' value='editdwl_do'>
				<input type='hidden' name='id' value='" .$letoltesadatok['id']. "'>
				<input type='hidden' name='kateg' value='" .$letoltesadatok['cid']. "'>
				<input type='submit' name='parancs' value='Szerkeszt'>
			</form>");
		}
		
		break;
	case "editdwl_do": // Letöltés szerkesztése - futtatás
		if ( ($_POST['title'] == $NULL) || ($_POST['descr'] == $NULL) )
		{
			Hibauzenet("CRITICAL", "Kötelezően kitöltendő mezők hiányoznak!", "A <b>Cím</b> és a <b>Leírás</b> mezőt kötelező kitölteni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				$oldkateg = mysql_fetch_assoc($sql->Lekerdezes("SELECT files FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_POST['kateg']). "'"));
				$uj = mysql_fetch_assoc($sql->Lekerdezes("SELECT files FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_POST['newcateg_id']). "'"));
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."download_categ SET files='" .($oldkateg['files'] - 1). "' WHERE id='" .mysql_real_escape_string($_POST['kateg']). "'");
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."download_categ SET files='" .($ujkateg['files'] + 1). "' WHERE id='" .mysql_real_escape_string($_POST['newcateg_id']). "'");
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."downloads SET title='" .mysql_real_escape_string($_POST['title']). "', descr='" .mysql_real_escape_string($_POST['descr']). "', cid='" .mysql_real_escape_string($_POST['newcateg_id']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
				ReturnTo("A letöltés frissítése megtörtént!", "admin.php?site=downloads&action=viewitems&id=" .$_POST['kateg'], "Vissza a letöltések listájához", TRUE);
			}
		}
		
		break;
	case "deldwl": // Letöltés törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kategoriaid = mysql_fetch_assoc($sql->Lekerdezes("SELECT cid FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$letoltes = mysql_fetch_assoc($sql->Lekerdezes("SELECT href FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$categ = mysql_fetch_assoc($sql->Lekerdezes("SELECT files FROM " .$cfg['tbprf']."download_categ WHERE id='" .$kategoriaid['cid']. "'"));
			
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."download_categ SET files='" .($categ['files']-1). "' WHERE id='" .$kategoriaid['cid']. "'");
			unlink("uploads/" .md5($letoltes['href']));
			ReturnTo("A letöltés törlése megtörtént!", "admin.php?site=downloads&action=viewitems&id=" .$kategoriaid['cid'], "Vissza a letöltések listájához", TRUE);
		}
		
		break;
	case "newdwl": // Letöltés hozzáadása (in layman's terms: feltöltés)
		if ( $_POST['cid'] != $NULL )
		{
			// Ha POST-tal érkeznek az adatok, a POST action lesz az érték
			$gcid = $_POST['cid'];
		} else {
			// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
			if ( $_GET['cid'] != $NULL )
			{
				// Ha gettel érkezik, az lesz az érték
				$gcid = $_GET['cid'];
			} else {
				// Sehogy nem érkezett adat
				$gcid = $NULL;
			}
		}
		
		if ( $gcid == $NULL )
		{
			Hibauzenet("CRITICAL", "A kategória azonosítóját kötelező megadni!");
		} else {
			$categ = mysql_fetch_assoc($sql->Lekerdezes("SELECT title, files FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($gcid). "'"));
			
			if ( $_POST['feltolt'] == "yes" ) // Ha elküldtük a feltöltése parancsot
			{
				if ( $_POST['title'] == $NULL )
				{
					Hibauzenet("CRITICAL", "A címsor mező kitöltése kötelező");
				} else {
				
				if ( $_FILES['newfile']['size'] > 157286400 )
				{
					Hibauzenet("ERROR", "A feltöltött fájl túl nagy méretű!", "A feltöltött fájl mérete " .DecodeSize($_FILES['newfile']['size']). ", azonban a maximális megengedett méret csak " .DecodeSize(157286400). "! Kérlek töltsd fel egy kisebb méretű fájlt!");
				} else {
					if(move_uploaded_file($_FILES['newfile']['tmp_name'], "uploads/" .md5($_FILES['newfile']['name'])))
					{
						// Sikeres feltöltés esetén
						$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."downloads (cid, uid, href, title, descr, upload_date) VALUES (" .mysql_real_escape_string($gcid). ", " .$_SESSION['userID']. ", '" .$_FILES['newfile']['name']. "', '" .$_POST['title']. "', '" .$_POST['descr']. "', " .time(). ")");
						$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."download_categ SET files='" .($categ['files']+1). "' WHERE id='" .mysql_real_escape_string($gcid). "'");
						
						ReturnTo("A feltöltés sikeres!", "admin.php?site=downloads&action=viewitems&id=" .$gcid, "Vissza a kategória letöltéseihez", TRUE);
					} else {
						// Hiba volt a feltöltés közben
						Hibauzenet("ERROR", "A fájlt nem sikerült feltölteni!");
						ReturnTo("", "admin.php?site=downloads&action=newdwl&cid=" .$gcid, "Vissza a feltöltéshez", FALSE);
					}
				}
				}
			} else {
		
				print("Fájl feltöltése a következő kategóriába: <i>" .$categ['title']. "</i>
			<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<p class='formText'>Címsor<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='title' size='50'><br>
			Leírás: <textarea name='descr' rows='10' cols='25'></textarea><br>
			Új fájl feltöltéséhez tallózd be a fájlt a merevlemezedről. A feltölthető fájl mérete maximálisan: " .DecodeSize(157286400). "
			<br><a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a><input name='newfile' type='file' size='50' accept='application/octet-stream'><br>
			<input type='submit' value='Feltöltés'>
			<input type='hidden' name='site' value='downloads'>
			<input type='hidden' name='action' value='newdwl'>
			<input type='hidden' name='cid' value='" .$gcid. "'>
			<input type='hidden' name='feltolt' value='yes'></p>
			</form>");
			}
		}
		
		break;
 }
 
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=configs");
}
?>