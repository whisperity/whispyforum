<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewtopic.php
   hozzászólások listázása egy témában
*/
 
 include('includes/common.php');
 Inicialize('viewtopic.php');
 
 if ( $_GET['id'] == $NULL )
	die(Hibauzenet("ERROR","A megadott azonosítójú téma nem létezik"));
  
 global $cfg, $sql;
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE tId='" .$_GET['id']. "'"); // Témák betöltése az adott fórumból
 
 /* Fórum id megállapítása */
 $sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT fId FROM " .$cfg['tbprf']."topics WHERE id='" .$_GET['id']. "'"), MYSQL_ASSOC);
 $forumId = $sor2['fId'];
 
 $sor3 = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .$forumId. "'"), MYSQL_ASSOC);
 print("<p class='header'><a href='viewtopics.php?id=" .$forumId. "'><< Vissza a fórumhoz (" .$sor3['name']. ")</a></p>"); // Visszatérési link kiírása
 
 /* +1 megtekintés hozzáadása */
 $sor4 = mysql_fetch_array($sql->Lekerdezes("SELECT opens FROM " .$cfg['tbprf']."topics WHERE id='" .$_GET['id']. "'"), MYSQL_ASSOC);
 $sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET opens='" .($sor4['opens']+1). "' WHERE id='" .$_GET['id']. "'");
 
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Hozzászólások listázása
	// Felhasználók nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['user']. "'"), MYSQL_ASSOC);
	
	switch ($adat2['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
	{
		case 0:
			$usrRang = 'Nincs aktiválva';
			break;
		case 1:
			$usrRang = 'Felhasználó';
			break;
		case 2:
			$usrRang = 'Moderátor';
			break;
		case 3:
			$usrRang = 'Adminisztrátor';
			break;
	}
	
	print("<a name='pid" .$sor['id']. "'><div class='post'>"); // Fejléc
	print("<div class='postbody'><h3 class='postheader'><p class='header'>" .$sor['title']. "</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$sor['post']. "</div></div>"); // Hozzászólás
	print("<dl class='postprofile'><dt>" .$adat2['username']. "</dt><br><dd>Rang: " .$usrRang. "</dd><dd>Hozzászólások: " .$adat2['postCount']. "</dd>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("<dd>Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). "</dd></dl>"); 
	print("</div>"); // Hozzászólás vége
	}
	
 DoFooter();
?>