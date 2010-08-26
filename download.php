<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* download.php
   letöltések
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('download.php');
 
 if ( (DOWNLOAD_MINLVL == 1) && ( ($_SESSION['userLevel'] == 0 ) || ($_SESSION['userLevel'] == $NULL) ) )
 {
	SetTitle("Letöltések");
	if ( $_SESSION['userLevel'] == $NULL )
		$_SESSION['userLevel'] = 0;
	
	Hibauzenet("ERROR","Az letöltések nem érhetőek el","A menü használatához MINIMUM level " .DOWNLOAD_MINLVL. " jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel'].").");
	DoFooter();
	die();
 }
 
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
		SetTitle("Letöltések");
		print("<center><h2 class='header'>Letöltések</h2></center>\n");
		// Kategóriák listázása
		$kategoriak = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ");
		while ( $sor = mysql_fetch_assoc($kategoriak) )
		{
			print("<h3 class='download-categ'>" .$sor['title']. " (" .$sor['files']. ")</h3>\n" .$sor['descr']. "\n<br><p align='right'><small><a href='download.php?action=viewcateg&id=" .$sor['id']. "'>Kategória böngészése</a></small></p>\n");
		}
		
		$wf_debug->RegisterDLEvent("A letöltéskategóriák listázása befejeződött");
		break;
	case "viewcateg": // Kategória böngészése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$kategoria = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			SetTitle("Letöltések: " .$kategoria['title']);
			print("<center><h2 class='header'>Letöltések</h2></center>\n");
			print("<h3 class='download-categ'>" .$kategoria['title']. " (" .$kategoria['files']. ")</h3>\n");
			
			$letoltesek = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."downloads WHERE cid='" .mysql_real_escape_string($_GET['id']). "'");
			print("<table border='0'>");
			while ( $sor = mysql_fetch_assoc($letoltesek) )
			{
				print("<tr>
				<td>" .$sor['title']. "</td>
				<td>" .$sor['descr']. "</td>
				<td><a href='download.php?action=viewdwl&id=" .$sor['id']. "'>Részletek</a></td>
				</tr>");
			}
			print("</table>");
		}
		
		break;
	case "viewdwl": // Letöltés megtekintése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			$letoltes = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
			
			SetTitle("Letöltés adatai: " .$letoltes['title']);
			
			print("<table>
			<tr>
				<td>Letöltés címe</td>
				<td>" .$letoltes['title']. "</td>
			</tr><tr>
				<td>Letöltés leírása</td>
				<td>" .$letoltes['descr']. "</td>
			</tr><tr>
				<td>Feltöltő neve</td>
				<td><a href='profile.php?id=");
			
			$felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .mysql_real_escape_string($letoltes['uid']). "'"));
			print($felhasznalo['id']. "'>" .$felhasznalo['username']. "</a></td>
			</tr><tr>
				<td>Feltöltés időpontja</td>
				<td>" .Datum("normal", "kisbetu", "dl", "H", "i", "s", $letoltes['upload_date']). "</td>
			</tr><tr>
				<td>Fájl mérete</td>
				<td>" .DecodeSize(@filesize("uploads/" .md5($letoltes['href']))). "</td>
			</tr><tr>
				<td>Letöltés</td>
				<td><a href='download_do.php?id=" .$_GET['id']. "' target='_blank'><img src='themes/" .$_SESSION['themeName']. "/download.gif' alt='Letöltés' border='0'></a></td>
			</tr><tr>
				<td>Letöltések száma</td>
				<td>" .$letoltes['download_count']. "</td>
			</tr>
			</table>");
		}
		break;
 }
 
 DoFooter();
?>