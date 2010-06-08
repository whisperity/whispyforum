<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewforum.php
   fórumok listázása
*/
 
 include('includes/common.php');
 Inicialize('viewforum.php');
 SetTitle("Fórum");
 
 print("<table class='forum'>
 <tr>
	<th class='forumheader'>Fórum neve</th>
	<th class='forumheader'>Témák</th>
	<th class='forumheader'>Hozzászólások</th>
	<th class='forumheader'>Utolsó hozzászólás</th>
 </tr>"); // Fejléc
 
 global $cfg, $sql;
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum"); // Fórumok betöltése
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Fórumok listázása
	// Felhasználó nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['lastuser']. "'"), MYSQL_ASSOC);
		
	print("<tr>
		<td class='forumlist'><p><a href='viewtopics.php?id=" .$sor['id']. "'>" .$sor['name']. "</a><br>" .$sor['description']. "</p></td>
		<td class='forumlist'>" .$sor['topics']. "</td>
		<td class='forumlist'>" .$sor['posts']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s", $sor['lastpostdate']). "<br>" .$adat2['username']. "<a href='viewtopic.php?id=" .$sor['lpTopic']. "#pid" .$sor['lpId']. "'><img src='themes/" .THEME_NAME. "/lastpost.gif' border='0' alt='Ugrás a legutolsó hozzászóláshoz'></a></p></td>
		</tr>"); // Fórum sor
	}
	
 DoFooter();
?>