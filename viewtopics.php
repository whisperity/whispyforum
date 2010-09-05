<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewtopics.php
   témák listázása egy adott fórumon belül
*/
 
 include('includes/common.php');
 Inicialize('viewtopics.php');
 
 global $cfg, $sql;
 
 if ( $_POST['action'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST action lesz az érték
	$ekson = $_POST['action'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['action'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$ekson = $_GET['action'];
	} else {
		// Sehogy nem érkezett adat
		$ekson = $NULL;
	}
 }
 
 switch ($ekson) // A beérkező parancs alapján döntjük el, mi fusson
 {
	case "":
	case $NULL:
	default:
 if ( $_GET['id'] == $NULL )
 {
	Hibauzenet("ERROR","A megadott azonosítójú fórum nem létezik");
	DoFooter();
	die();
 }
 
 print("<p class='header'><a href='viewforum.php'><< Vissza a fórumokhoz</a>");  // Visszatérési link kiírása
 if ( $_SESSION['loggedin'] == 1 )
	print(" <a href='newtopic.php?id=" .$_GET['id']. "'>Új téma hozzáadása</a></p>");
 
 print("<div align='center'><center><table class='forum'>
 <tr>
	<th class='forumheader'></th>
	<th class='forumheader'>Témák</th>
	<th class='forumheader'>Válaszok</th>
	<th class='forumheader'>Megtekintések</th>
	<th class='forumheader'>Utolsó hozzászólás</th>
 </tr>"); // Fejléc
 /* Fórum címe, weblapfejléc */
 $forumCime = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .mysql_real_escape_string($_GET['id']). "'"), MYSQL_ASSOC);
 SetTitle($forumCime["name"]);
 
 /* Közlemények */
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE fId='" .$_GET['id']. "' AND type='2'"); // Közlemények betöltése az adott fórumból
 
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Témák listázása
	// Felhasználók nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['startuser']. "'"), MYSQL_ASSOC);
	$adat3 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['lastuser']. "'"), MYSQL_ASSOC);
	print("<tr>
		<td class='forumlist'>"); // Ikon
	
	switch ($sor['type'])
	{
		case 1:
			if ($sor['locked'] == 0) {
				print("<img src='/themes/" .$_SESSION['themeName']. "/icon_topic.gif' alt='Normál téma'>");
			} else {
				print("<img src='/themes/" .$_SESSION['themeName']. "/icon_topic_locked.gif' alt='Lezárt téma'>");
			}
			break;
		case 2:
			print("<img src='/themes/" .$_SESSION['themeName']. "/announce.png' alt='Közlemény'>");
			break;
	}
	
	print("</td>
		<td class='forumlist'><p><a href='viewtopic.php?id=" .$sor['id']. "'>" .$sor['name']. "</a>");
		
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
		{
			// Csak moderátor/admin tud témákat módosítani, törölni
			print("\t<a href='viewtopics.php?tId=" .$sor['id']. "&action=edittopic&id=" .$_GET['id']. "'><img src='/themes/" .$_SESSION['themeName']. "/edit_post_icon.gif' border='0' alt='Téma szerkesztése/törlése'></a>\t<a href='viewtopics.php?tId=" .$sor['id']. "&action=deletetopic&id=" .$_GET['id']. "'><img src='/themes/" .$_SESSION['themeName']. "/icon_delete_post.jpg' alt='Téma törlése' border='0'></a>");
		}
		
		print("<br>Szerző: <a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a> » " .Datum("normal","m","d","H","i","s",$sor['startdate']). "</p></td>
		<td class='forumlist'>" .$sor['replies']. "</td>
		<td class='forumlist'>" .$sor['opens']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s",$sor['lastpostdate']). "<br><a href='profile.php?id=" .$adat3['id']. "'>" .$adat3['username']. "</a><a href='viewtopic.php?id=" .$sor['id']. "#pid" .$sor['lpId']. "'><img src='themes/" .$_SESSION['themeName']. "/lastpost.gif' border='0' alt='Ugrás a legutolsó hozzászóláshoz'></a></p></td>
		</tr>"); // Téma sor
	}
 /* Közlemények vége */
 /* Többi téma */
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE fId='" .$_GET['id']. "' AND type NOT IN('2')"); // Közlemények betöltése az adott fórumból
 
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Témák listázása
	// Felhasználók nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['startuser']. "'"), MYSQL_ASSOC);
	$adat3 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['lastuser']. "'"), MYSQL_ASSOC);
		
	print("<tr>
		<td class='forumlist'>"); // Ikon
	
	switch ($sor['type'])
	{
		case 1:
			if ($sor['locked'] == 0) {
				print("<img src='/themes/" .$_SESSION['themeName']. "/icon_topic.gif' alt='Normál téma'>");
			} else {
				print("<img src='/themes/" .$_SESSION['themeName']. "/icon_topic_locked.gif' alt='Lezárt téma'>");
			}
			break;
		case 2:
			print("<img src='/themes/" .$_SESSION['themeName']. "/announce.png' alt='Közlemény'>");
			break;
	}
	
	print("</td>
		<td class='forumlist'><p><a href='viewtopic.php?id=" .$sor['id']. "'>" .$sor['name']. "</a>");
		
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
		{
			// Csak moderátor/admin tud témákat módosítani, törölni
			print("\t<a href='viewtopics.php?tId=" .$sor['id']. "&action=edittopic&id=" .$_GET['id']. "'><img src='/themes/" .$_SESSION['themeName']. "/edit_post_icon.gif' border='0' alt='Téma szerkesztése/törlése'></a>\t<a href='viewtopics.php?tId=" .$sor['id']. "&action=deletetopic&id=" .$_GET['id']. "'><img src='/themes/" .$_SESSION['themeName']. "/icon_delete_post.jpg' alt='Téma törlése' border='0'></a>");
		}
		
		print("<br>Szerző: <a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a> » " .Datum("normal","m","d","H","i","s",$sor['startdate']). "</p></td>
		<td class='forumlist'>" .$sor['replies']. "</td>
		<td class='forumlist'>" .$sor['opens']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s",$sor['lastpostdate']). "<br><a href='profile.php?id=" .$adat3['id']. "'>" .$adat3['username']. "</a><a href='viewtopic.php?id=" .$sor['id']. "#pid" .$sor['lpId']. "'><img src='themes/" .$_SESSION['themeName']. "/lastpost.gif' border='0'></a></p></td>
		</tr>"); // Téma sor
	}
 /* Többi téma vége */
 print("</table></center></div>"); // Táblázat zárása
 
 break;
 case "edittopic": // Téma szerkesztése
	/* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a témát
 // Adatok bekérése
 if ( $_POST['tId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['tId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['tId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['tId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 
 // Felhasználói rang, felhasználó ellenörzése
 if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
 {
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
 } // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
 
 
 if ( $jog == 0 )
 {
	SetTitle("Nincs privilégium");
	Hibauzenet("ERROR", "Nincs jogod a téma szerkesztéséhez");
 } else {
 if ($_GET['cmd'] == "modifytopic")
	{
		SetTitle("Téma szerkesztése");
		// Téma módosítása
		if ( $_GET['locked'] == $NULL)
		{
			$locked = 0;
		} else if ($_GET['locked'] == 1)
		{
			$locked = 1;
		}
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET locked='" .$locked. "', type='" .$_GET['type']. "' WHERE id='" .mysql_real_escape_string($getid). "'");
		$sor5 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'")); // Téma adatai
		ReturnTo("Téma sikeresen szerkesztve", "viewtopics.php?id=" .$sor5['fId'], "Vissza a fórumhoz", TRUE);
		DoFooter();
		die(); // A többi kód ne fusson le
	}
	$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'")); // Téma adatai
	SetTitle("Téma szerkesztése");
	print("<form method='GET' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Téma szerkesztése: " .$sor['name']. "</span><br>
		<p class='formText'>Téma típusa: 
			<input type='radio' name='type' value='1'");
			if ( $sor['type'] == 1) // Ha a téma típusa 1 (normál), akkor alapból a normál gomb kerül bejelölésre
				print(" checked ");
			print("> Normál <input type='radio' name='type' value='2'");
			if ( $sor['type'] == 2) // Ha a téma típusa 2 (közlemény), akkor alapból a közlemény gomb kerül bejelölésre
				print(" checked ");
			print("> Közlemény<br>
		Lezárt topic: <input type='radio' name='locked' value='0'");
			if ( $sor['locked'] == 0)
				print(" checked ");
			print("> Nem <input type='radio' name='locked' value='1'");
			if ( $sor['locked'] == 1)
				print(" checked ");
		print("> Igen</p>
		<input type='hidden' name='tId' value='" .$getid. "'>
		<input type='hidden' name='cmd' value='modifytopic'>
		<input type='hidden' name='action' value='edittopic'>
		<input type='hidden' name='id' value='" .$_GET['id']. "'>
		<input type='submit' value='Téma szerkesztése'>
		</form>");
 }
	break;
	
 case "deletetopic": // Téma törlése
	/* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a témát
 // Adatok bekérése
 if ( $_POST['tId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['tId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['tId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['tId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 
 // Felhasználói rang, felhasználó ellenörzése
 if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
 {
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
 } // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
 
 
 if ( $jog == 0 )
 {
	SetTitle("Nincs privilégium");
	Hibauzenet("ERROR", "Nincs jogod a téma szerkesztéséhez");
 } else {
	SetTitle("Téma törlése");
		// Téma törlése
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE tId='" .mysql_real_escape_string($getid). "'"); // A téma tartalmának betöltése (postok)
		$hozzaszolas_torolve = 0; // 0 hozzászólás törölve
		while($sor = mysql_fetch_array($adat, MYSQL_ASSOC))
		{
			// Felhasználó hozzászólásszám csökkentése
			$sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'")); // A hozzászólást beküldő felhasználó adatai
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET postCount='" .($sor2['postCount']-1). "' WHERE id='" .$sor['uId']. "'"); // -1 hozzászólás a felhasználótól
			
			$hozzaszolas_torolve++; // +1 hozzászólás törölve
		}
		
		$sor3 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$getid. "'")); // Téma adatai
		$sor4 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum WHERE id='" .$sor3['fId']. "'")); // Fórum adatai
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."forum SET topics='" .($sor4['topics']-1). "', posts='" .($sor4['posts'] - $hozzaszolas_torolve). "' WHERE id='" .$sor3['fId']. "'"); // -1 topic, - a törölt hozzászólások száma a fórumból levonva
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'"); // Téma törlése
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."posts WHERE tId='" .mysql_real_escape_string($getid). "'"); // Hozzászólások törlése
		
		ReturnTo("Hozzászólás sikeresen törölve", "viewtopics.php?id=" .$sor3['fId'], "Vissza a fórumhoz", TRUE);
		DoFooter();
		die(); // A többi kód ne fusson le
	}
	
	break;
}
 DoFooter();
?>