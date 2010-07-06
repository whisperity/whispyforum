<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewtopics.php
   témák listázása egy adott fórumon belül
*/
 
 include('includes/common.php');
 Inicialize('viewtopics.php');
 
 if ( $_GET['id'] == $NULL )
	die(Hibauzenet("ERROR","A megadott azonosítójú fórum nem létezik"));
 
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
 
 global $cfg, $sql;
 
 /* Fórum címe, weblapfejléc */
 $forumCime = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .$_GET['id']. "'"), MYSQL_ASSOC);
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
				print("<img src='/themes/" .THEME_NAME. "/icon_topic.gif' alt='Normál téma'>");
			} else {
				print("<img src='/themes/" .THEME_NAME. "/icon_topic_locked.gif' alt='Lezárt téma'>");
			}
			break;
		case 2:
			print("<img src='/themes/" .THEME_NAME. "/announce.png' alt='Közlemény'>");
			break;
	}
	
	print("</td>
		<td class='forumlist'><p><a href='viewtopic.php?id=" .$sor['id']. "'>" .$sor['name']. "</a>");
		
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
		{
			// Csak moderátor/admin tud témákat módosítani, törölni
			print("\t<a href='edittopic.php?tId=" .$sor['id']. "'><img src='/themes/" .THEME_NAME. "/edit_post_icon.gif' border='0' alt='Téma szerkesztése/törlése'></a>\t<a href='edittopic.php?tId=" .$sor['id']. "&cmd=deletetopic'><img src='/themes/" .THEME_NAME. "/icon_delete_post.jpg' alt='Téma törlése' border='0'></a>");
		}
		
		print("<br>Szerző: <a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a> » " .Datum("normal","m","d","H","i","s",$sor['startdate']). "</p></td>
		<td class='forumlist'>" .$sor['replies']. "</td>
		<td class='forumlist'>" .$sor['opens']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s",$sor['lastpostdate']). "<br><a href='profile.php?id=" .$adat3['id']. "'>" .$adat3['username']. "</a><a href='viewtopic.php?id=" .$sor['id']. "#pid" .$sor['lpId']. "'><img src='themes/" .THEME_NAME. "/lastpost.gif' border='0' alt='Ugrás a legutolsó hozzászóláshoz'></a></p></td>
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
				print("<img src='/themes/" .THEME_NAME. "/icon_topic.gif' alt='Normál téma'>");
			} else {
				print("<img src='/themes/" .THEME_NAME. "/icon_topic_locked.gif' alt='Lezárt téma'>");
			}
			break;
		case 2:
			print("<img src='/themes/" .THEME_NAME. "/announce.png' alt='Közlemény'>");
			break;
	}
	
	print("</td>
		<td class='forumlist'><p><a href='viewtopic.php?id=" .$sor['id']. "'>" .$sor['name']. "</a>");
		
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
		{
			// Csak moderátor/admin tud témákat módosítani, törölni
			print("\t<a href='edittopic.php?tId=" .$sor['id']. "'><img src='/themes/" .THEME_NAME. "/edit_post_icon.gif' border='0' alt='Téma szerkesztése/törlése'></a>\t<a href='edittopic.php?tId=" .$sor['id']. "&cmd=deletetopic'><img src='/themes/" .THEME_NAME. "/icon_delete_post.jpg' alt='Téma törlése' border='0'></a>");
		}
		
		print("<br>Szerző: <a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a> » " .Datum("normal","m","d","H","i","s",$sor['startdate']). "</p></td>
		<td class='forumlist'>" .$sor['replies']. "</td>
		<td class='forumlist'>" .$sor['opens']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s",$sor['lastpostdate']). "<br><a href='profile.php?id=" .$adat3['id']. "'>" .$adat3['username']. "</a><a href='viewtopic.php?id=" .$sor['id']. "#pid" .$sor['lpId']. "'><img src='themes/" .THEME_NAME. "/lastpost.gif' border='0'></a></p></td>
		</tr>"); // Téma sor
	}
 /* Többi téma vége */
 print("</table></center></div>"); // Táblázat zárása
 DoFooter();
?>