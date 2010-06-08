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
 $sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT name, fId, locked FROM " .$cfg['tbprf']."topics WHERE id='" .$_GET['id']. "'"), MYSQL_ASSOC);
 $forumId = $sor2['fId'];
 SetTitle($sor2['name']);
 
 $sor3 = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .$forumId. "'"), MYSQL_ASSOC);
 print("<p class='header'><a href='viewtopics.php?id=" .$forumId. "'><< Vissza a fórumhoz (" .$sor3['name']. ")</a><img src='themes/" .THEME_NAME. "/x.bmp'>"); // Visszatérési link kiírása
 
 /* Hozzászólási link / lezártsági kép */
 if ($sor2['locked'] == 0)
 {
	print("<a href='newpost.php?id=" .$_GET['id']. "'>Új hozzászólás</a>"); // Hozzászólási link
 } else {
	print("<img src='/themes/" .THEME_NAME. "/forum_read_locked.png' alt='A téma lezárva, nem küldhető újabb hozzászólás'>");
 }
 print("</p>");
 /* +1 megtekintés hozzáadása */
 $sor4 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$_GET['id']. "'"), MYSQL_ASSOC);
 $sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET opens='" .($sor4['opens']+1). "' WHERE id='" .$_GET['id']. "'");
 
 print("<h3 class='header'><p class='header'>" .$sor4['name']. "</p></h3>"); // Fejléc
 
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Hozzászólások listázása
	// Felhasználók nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['uId']. "'"), MYSQL_ASSOC);
	
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
	
	/* Hózzászólás formázása */
	$postBody = $sor['pText']; // Nyers
	$postBody = EmoticonParse($postBody); // Hangulatjelek hozzáadása BB-kódként
	$postBody = HTMLDestroy($postBody); // HTML kódok nélkül 
	$postBody = BBDecode($postBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
	
	print("<a name='pid" .$sor['id']. "'><div class='post'>"); // Fejléc
	print("<div class='postbody'><h3 class='postheader'><p class='header'>" .$sor['pTitle']. "</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$postBody. "</div></div>"); // Hozzászólás
	print("<div class='postright'>Hozzászólás időpontja: " .Datum("normal","m","d","H","i","",$sor['pDate']). "<p><b>" .$adat2['username']. "</b><br>Rang: " .$usrRang. "<br>Hozzászólások: " .$adat2['postCount']. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
	print("</div>"); // Hozzászólás vége
	}
	
 DoFooter();
?>