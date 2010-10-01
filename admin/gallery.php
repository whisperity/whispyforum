<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/gallery.php
   galériák kezelése
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Galéria</h2></center>
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
		print("<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Név</th>
				<th>Létrehozó felhasználó</th>
				<th>Képek száma</th>
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."galleries");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['title']. "</td>
				");
			
			$felhasznalonev = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($sor['uid']). "'"));
			
			print("<td><a href='profile.php?id=" .$felhasznalonev['id']. "'>" .$felhasznalonev['username']. "</a></td>
				<td>" .$sor['picCount']. "</td>");
			
			print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='viewitems'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Képek megtekintése'>
			</form></td>");
			
			print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='edit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
			<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='delete'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
			</tr>");
		}
		
		print("</table></div>
			<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='newgallery'>
				<input type='submit' value='Új galéria létrehozása'>
			</form>");
		
		break;
	case "newgallery": // Új galéria létrehozása (form)
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Új galéria létrehozása</span>
			<p class='formText'>Galéria neve: <input type='text' name='gtitle' size='50'><br>
			Létrehozó felhasználó: " .$_SESSION['username']. "<br>
			Képek száma: 0</p>
			
			<input type='hidden' name='site' value='gallery'>
			<input type='hidden' name='action' value='newgallery_do'>
			<input type='submit' value='Létrehozás'>
		</form>");
		
		break;
	case "newgallery_do": // Új galéria létrehozása (script)
		if ( $_POST['gtitle'] == $NULL )
		{
			Hibauzenet("ERROR", "A galéria címét kötelező megadni!");
		} else {
			// Galéria hozzáadása
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."galleries(title, uid) VALUES ('" .$_POST['gtitle']. "', '" .$_SESSION['userID']. "')");
			
			if ( mysql_insert_id() != 0 )
			{
				ReturnTo("A galéria hozzáadása megtörtént!", "admin.php?site=gallery", "Galériák listája", TRUE);
			}
		}
		
		break;
	case "edit": // Szerkesztés (form)
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "A galéria azonosítóját kötelező megadni!");
		} else {
			$galdata = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$felhasznalonev = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($galdata['uid']). "'"));
			
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
				<span class='formHeader'>Galéria szerkesztése: " .$galdata['title']. "</span>
				<p class='formText'>Galéria neve: <input type='text' name='gtitle' size='50' value='" .$galdata['title']. "'><br>
				Létrehozó felhasználó: " .$felhasznalonev['username']. "<br>
				Képek száma: " .$galdata['picCount']. "</p>
				
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='id' value='" .$_GET['id']. "'>
				<input type='hidden' name='action' value='edit_do'>
				<input type='submit' value='Szerkesztése'>
			</form>");
		}
		
		break;
	case "edit_do": // Szerkesztés (script)
		if ( $_POST['id'] == $NULL )
		{
			Hibauzenet("ERROR", "A galéria azonosítóját kötelező megadni!");
		} else {
			// Galéria szerkesztése
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."galleries SET title='" .mysql_real_escape_string($_POST['gtitle']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
			
			ReturnTo("A galéria szerkesztése megtörtént!", "admin.php?site=gallery", "Galériák listája", TRUE);
		}
		
		break;
	case "delete": // Galéria törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."gallery_pictures WHERE gid='" .mysql_real_escape_string($_GET['id']). "'");
			ReturnTo("A galéria sikeresen törölve", "admin.php?site=gallery", "Vissza a galériákhoz", TRUE);
		}
		
		break;
	case "viewitems": // Képek megtekintése
	if ( $_GET['id'] == $NULL )
	{
		Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
	} else {
		$gNev = mysql_fetch_assoc($sql->Lekerdezes("SELECT title FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
		print("A(z) <b>" .$gNev['title']. "</b> galéria képeinek szerkesztése.<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Név</th>
				<th>Feltöltő neve</th>
				<th>Feltöltés időpontja</th>
				<th></th>
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."gallery_pictures WHERE gid='" .mysql_real_escape_string($_GET['id']). "'");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$felhasznalonev = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($sor['uid']). "'"));
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['title']. "</td>
				<td><a href='profile.php?id=" .$felhasznalonev['id']. "'>" .$felhasznalonev['username']. "</a></td>
				<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $sor['uploaddate']). "</td>
				<td><img src='uploads/" .$sor['filename']. "' width='160' height='120' alt='Kis kép'></td>
				<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='editpic'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
				<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='delpic'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Törlés'>
			</form></td>
			</tr>");	
		}
		
		print("</table></div><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='newpicture'>
				<input type='hidden' name='gid' value='" .$_GET['id']. "'>
				<input type='submit' value='Új kép feltöltése'>
			</form>");
	}
		break;
	
	case "newpicture": // Kép hozzáadása (in layman's terms: feltöltés)
		if ( $_POST['gid'] != $NULL )
		{
			// Ha POST-tal érkeznek az adatok, a POST action lesz az érték
			$gcid = $_POST['gid'];
		} else {
			// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
			if ( $_GET['gid'] != $NULL )
			{
				// Ha gettel érkezik, az lesz az érték
				$gcid = $_GET['gid'];
			} else {
				// Sehogy nem érkezett adat
				$gcid = $NULL;
			}
		}
		
		if ( $gcid == $NULL )
		{
			Hibauzenet("CRITICAL", "A kategória azonosítóját kötelező megadni!");
		} else {
			$gal = mysql_fetch_assoc($sql->Lekerdezes("SELECT title, picCount FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($gcid). "'"));
			
			if ( $_POST['feltolt'] == "yes" ) // Ha elküldtük a feltöltése parancsot
			{
				if ( $_POST['title'] == $NULL )
				{
					Hibauzenet("CRITICAL", "A címsor mező kitöltése kötelező");
				} else {
				
				if ( $_FILES['newpicfile']['size'] > 5242880 )
				{
					Hibauzenet("ERROR", "A feltöltött fájl túl nagy méretű!", "A feltöltött fájl mérete " .DecodeSize($_FILES['newfile']['size']). ", azonban a maximális megengedett méret csak " .DecodeSize(5242880). "! Kérlek töltsd fel egy kisebb méretű fájlt!");
				} else {
					if(move_uploaded_file($_FILES['newpicfile']['tmp_name'], "uploads/g" .$gcid. "_p" .($gal['picCount']+1). "_" .$_FILES['newpicfile']['name']))
					{
						// Sikeres feltöltés esetén
						$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."gallery_pictures (title, gid, uid, uploaddate, origfilename, filename) VALUES ('" .mysql_real_escape_string($_POST['title']). "', " .mysql_real_escape_string($gcid). ", " .$_SESSION['userID']. ", '" .time(). "', '" .$_FILES['newpicfile']['name']. "', 'g" .$gcid. "_p" .($gal['picCount']+1). "_" .$_FILES['newpicfile']['name']. "')");
						
						$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."galleries SET picCount='" .($gal['picCount']+1). "' WHERE id='" .mysql_real_escape_string($gcid). "'");
						
						ReturnTo("A feltöltés sikeres!", "admin.php?site=gallery&action=viewitems&id=" .$gcid, "Vissza a galéria képeihez", TRUE);
					} else {
						// Hiba volt a feltöltés közben
						Hibauzenet("ERROR", "A fájlt nem sikerült feltölteni!");
						ReturnTo("", "admin.php?site=gallery&action=newpicture&gid=" .$gcid, "Vissza a feltöltéshez", FALSE);
					}
				}
				}
			} else {
		
				print("Fájl feltöltése a következő kategóriába: <i>" .$gal['title']. "</i>
			<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<p class='formText'>Név: <input type='text' name='title' size='50'><br>
			Új fájl feltöltéséhez tallózd be a fájlt a merevlemezedről. A feltölthető fájl mérete maximálisan: " .DecodeSize(5242880). "
			<br><input name='newpicfile' type='file' size='50' accept='application/octet-stream'><br>
			<input type='submit' value='Feltöltés'>
			<input type='hidden' name='site' value='gallery'>
			<input type='hidden' name='action' value='newpicture'>
			<input type='hidden' name='gid' value='" .$gcid. "'>
			<input type='hidden' name='feltolt' value='yes'></p>
			</form>");
			}
		}
		
		break;
	case "editpic": // Kép szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kepadatok = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($kepadatok['uid']). "'"));
			
			print("Minden mezőt ki kell tölteni!\n<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<span class='formHeader'>Kép szerkesztése: " .$kepadatok['title']. "</span>
			<p class='formText'>Cím: <input type='text' name='title' value='" .$kepadatok['title']. "' size='50'><br>
			<img src='uploads/" .$kepadatok['filename']. "' width='160' height='120' alt='Kis kép'><br>
			Fájlméret: " .DecodeSize(@filesize("uploads/" .$kepadatok['filename'])). "<br>
			Feltöltő neve: " .$felhasznalo['username']. "<br>
			Feltöltés időpontja: " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $kepadatok['uploaddate']). "<br>
			Áthelyezés másik galériába: <select name='newgal_id'>");
			
			$aktualisgaleria = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, title FROM " .$cfg['tbprf']."galleries WHERE id='" .$kepadatok['gid']. "'"));
			
			print("<option value='" .$aktualisgaleria['id']. "'>(Nincs áthelyezés) " .$aktualisgaleria['title']. "</option>\n");
			
			$galeriak = $sql->Lekerdezes("SELECT id, title FROM " .$cfg['tbprf']."galleries WHERE id <> " .$kepadatok['gid']);
			
			while ( $gsor = mysql_fetch_assoc($galeriak) ) {
				print("<option value='" .$gsor['id']. "'>" .$gsor['title']. "</option>\n");
			}
			
			print("</select></p>
				<input type='hidden' name='site' value='gallery'>
				<input type='hidden' name='action' value='editpic_do'>
				<input type='hidden' name='id' value='" .$kepadatok['id']. "'>
				<input type='hidden' name='galerid' value='" .$kepadatok['gid']. "'>
				<input type='submit' name='parancs' value='Szerkeszt'>
			</form>");
		}
		
		break;
	case "editpic_do": // Kép szerkesztése - futtatás
		if ( $_POST['title'] == $NULL)
		{
			Hibauzenet("CRITICAL", "Kötelezően kitöltendő mezők hiányoznak!", "A <b>Cím</b> mezőt kötelező kitölteni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				if ( $_POST['galerid'] != $_POST['newgal_id'] ) // Csak akkor frissítjük a képek számát, ha történt áthelyezés
				{
					$oldgal = mysql_fetch_assoc($sql->Lekerdezes("SELECT picCount FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_POST['galerid']). "'"));
					$ujgal = mysql_fetch_assoc($sql->Lekerdezes("SELECT picCount FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_POST['newgal_id']). "'"));
					
					$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."galleries SET picCount='" .($oldgal['picCount'] - 1). "' WHERE id='" .mysql_real_escape_string($_POST['galerid']). "'");
					$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."galleries SET picCount='" .($ujgal['picCount'] + 1). "' WHERE id='" .mysql_real_escape_string($_POST['newgal_id']). "'");
				}
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."gallery_pictures SET title='" .mysql_real_escape_string($_POST['title']). "', gid='" .mysql_real_escape_string($_POST['newgal_id']). "' WHERE id='" .mysql_real_escape_string($_POST['id']). "'");
				ReturnTo("A kép adatainak frissítése megtörtént!", "admin.php?site=gallery&action=viewitems&id=" .$_POST['galerid'], "Vissza a galéria képeinek listájához", TRUE);
			}
		}
		
		break;
	case "delpic": // Kép törlése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$galeriaid = mysql_fetch_assoc($sql->Lekerdezes("SELECT gid FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$kep = mysql_fetch_assoc($sql->Lekerdezes("SELECT filename FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			$kepek = mysql_fetch_assoc($sql->Lekerdezes("SELECT picCount FROM " .$cfg['tbprf']."galleries WHERE id='" .$galeriaid['gid']. "'"));
			
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."galleries SET picCount='" .($categ['picCount']-1). "' WHERE id='" .$galeriaid['gid']. "'");
			unlink("uploads/" .$kep['filename']);
			ReturnTo("A kép törlése megtörtént!", "admin.php?site=gallery&action=viewitems&id=" .$galeriaid['gid'], "Vissza a képek listájához", TRUE);
		}
		
		break;
}

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=gallery");
}
?>