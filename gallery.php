<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* gallery.php
   galéria
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('gallery.php');
 
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
	case $NULL: // Semmi (nincs beérkező érték)
		SetTitle("Galéria");
		print("<center><h2 class='header'>Galéria</h2></center>\n");
		// Galériák listázása
		$galeriak = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."galleries");
		while ( $sor = mysql_fetch_assoc($galeriak) )
		{
			print("<h3 class='download-categ'>" .$sor['title']. " (" .$sor['picCount']. ")</h3>");
			$elsokep = mysql_fetch_assoc($sql->Lekerdezes("SELECT filename FROM " .$cfg['tbprf']."gallery_pictures WHERE gid='" .mysql_real_escape_string($sor['id']). "' LIMIT 1"));
			
			print("<img src='uploads/" .$elsokep['filename']."' width='160' height='120' alt='Kis kép'>");
			
			print("<p align='right'><small><a href='gallery.php?action=viewgal&id=" .$sor['id']. "'>Galéria böngészése</a></small></p>\n");
		}
		
		$wf_debug->RegisterDLEvent("A galériák listázása befejeződött");
		break;
	case "viewgal": // Galéria böngészése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$galeria = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			SetTitle($galeria['title']);
			print("<center><h2 class='header'>Galéria</h2></center>\n");
			print("<h3 class='download-categ'>" .$galeria['title']. " (" .$galeria['picCount']. ")</h3>\n");
			
			$kepek = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."gallery_pictures WHERE gid='" .mysql_real_escape_string($_GET['id']). "'");
			print("<table border='0'>");
			while ( $sor = mysql_fetch_assoc($kepek) )
			{
				print("<tr>
				<td>" .$sor['title']. "</td>
				<td><img src='uploads/" .$sor['filename']. "' width='320' height='240' alt='Kis kép'></td>
				<td><a href='gallery.php?action=viewpicture&id=" .$sor['id']. "'>Részletek</a></td>
				</tr>");
			}
			print("</table>");
		}
		
		break;
	case "viewpicture": // Kép megtekintése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kep = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			print("<center><h2 class='header'>Galéria</h2></center>\n");
			SetTitle("Kép adatai: " .$kep['title']);
			
			print("<center><img src='uploads/" .$kep['filename']. "' width='800' height='600' alt='" .$kep['title']. "'></center>\n");
			
			$felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .mysql_real_escape_string($kep['uid']). "'"));
			
			$galeria = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, title FROM " .$cfg['tbprf']."galleries WHERE id='" .mysql_real_escape_string($kep['gid']). "'"));
			print("<table>
				<tr>
					<td>Feltöltötte</td>
					<td><a href='profile.php?id=" .$felhasznalo['id']. "'>" .$felhasznalo['username']. "</a></td>
				</tr>
				<tr>
					<td>a galériába</td>
					<td><a href='gallery.php?action=viewgal&id=" .$galeria['id']. "'>" .$galeria['title']. "</a></td>
				</tr>
				<tr>
					<td>Feltöltés időpontja</td>
					<td>" .Datum("normal", "kisbetu", "dL", "H", "i", "s", $kep['uploaddate']). "</td>
				</tr>
				<tr>
					<td><a href='gallery_download.php?id=" .$_GET['id']. "' target='_blank' alt='Kép letöltése'>Letöltés</a></td>
				</tr>
			</table>");
		}
		break;
 }
 
 DoFooter();
?>