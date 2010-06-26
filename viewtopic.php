<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewtopic.php
   hozzászólások listázása egy témában
*/
 
 include('includes/common.php');
 Inicialize('viewtopic.php');
 
 if ( $_POST['id'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['id'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['id'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['id'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
  
 global $cfg, $sql;
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE tId='" .$getid. "'"); // Témák betöltése az adott fórumból
 
 /* Fórum id megállapítása */
 $sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT name, fId, locked FROM " .$cfg['tbprf']."topics WHERE id='" .$getid. "'"), MYSQL_ASSOC);
 $forumId = $sor2['fId'];
 SetTitle($sor2['name']);
 
 $sor3 = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .$forumId. "'"), MYSQL_ASSOC);
 print("<p class='header'><a href='viewtopics.php?id=" .$forumId. "'><< Vissza a fórumhoz (" .$sor3['name']. ")</a><img src='themes/" .THEME_NAME. "/x.bmp'>"); // Visszatérési link kiírása
 
 /* Hozzászólási link / lezártsági kép */
 if ( $_SESSION['loggedin'] == 1)
 {
	if ($sor2['locked'] == 0)
	{
		print("<a href='newpost.php?id=" .$getid. "'>Új hozzászólás</a>"); // Hozzászólási link
	} else {
		print("<img src='/themes/" .THEME_NAME. "/forum_read_locked.png' alt='A téma lezárva, nem küldhető újabb hozzászólás'>");
	}
 }
 
 print("</p>");
 /* +1 megtekintés hozzáadása */
 $sor4 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$getid. "'"), MYSQL_ASSOC);
 $sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET opens='" .($sor4['opens']+1). "' WHERE id='" .$getid. "'");
 
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
	
	print("<div class='post'>"); // Fejléc
	print("<div class='postbody'><h3 class='postheader'><p class='header'><a name='pid" .$sor['id']. "'></a>" .$sor['pTitle']. "");
	if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) ||  ($_SESSION['userID'] == $sor['uId']) )
	{ // Csak moderátor, admin, valamint a hozzászólás beküldője tudja szerkeszteni, törölni a hozzászólást
		if ($sor4['locked'] == 0)
		{ // Csak nyitott téma esetén szerkeszthetőek, törölhetőek a hozzászólások
			print("\t<a href='editpost.php?pId=" .$sor['id']. "'><img src='/themes/" .THEME_NAME. "/edit_post_icon.gif' alt='Hozzászólás szerkesztése' border='0'></a>\t<a href='editpost.php?pId=" .$sor['id']. "&cmd=deletepost'><img src='/themes/" .THEME_NAME. "/icon_delete_post.jpg' alt='Hozzászólás törlése' border='0'></a>");
		}
	}
	
	print("</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$postBody);
	if ( $sor['edited'] == 1 )
	{
		// Ha a post szerkesztett, bekérjük a szerkesztő felhasználó adatait, és kiírjuk hogy mikor szerkesztették
		$sor5 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['euId']. "'"));
		print("<br>Utoljára szerkesztve: " .Datum("normal","m","d","H","i","s", $sor['eDate']). " (" .$sor5['username']. ")");
	}
	print("</div></div>"); // Hozzászólás
	print("<div class='postright'>Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$sor['pDate']). "</b><p><b>" .$adat2['username']. "</b><br>Rang: " .$usrRang. "<br>Hozzászólások: " .$adat2['postCount']. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
	print("</div></div>"); // Hozzászólás vége
	}
	
 DoFooter();
?>