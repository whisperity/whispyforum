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
 
 global $cfg, $sql;
 
 switch ($ekson) // A beérkező ACTION alapján nézzük, mi történjen
 {
	/* Nincs beérkező adat, hozzászólások listázása */
	case "":
	case $NULL:
	default:
 
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE tId='" .mysql_real_escape_string($getid). "'"); // Témák betöltése az adott fórumból
 
 /* Fórum id megállapítása */
 $sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT name, fId, locked FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'"), MYSQL_ASSOC);
 $forumId = $sor2['fId'];
 SetTitle($sor2['name']);
 
 $sor3 = mysql_fetch_array($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."forum WHERE id='" .$forumId. "'"), MYSQL_ASSOC);
 print("<p class='header'><a href='viewtopics.php?id=" .$forumId. "'><< Vissza a fórumhoz (" .$sor3['name']. ")</a><img src='themes/" .$_SESSION['themeName']. "/x.bmp'>"); // Visszatérési link kiírása
 
 /* Hozzászólási link / lezártsági kép */
 if ( $_SESSION['loggedin'] == 1)
 {
	if ($sor2['locked'] == 0)
	{
		print("<a href='newpost.php?id=" .$getid. "'>Új hozzászólás</a>"); // Hozzászólási link
	} else {
		print("<img src='/themes/" .$_SESSION['themeName']. "/forum_read_locked.png' alt='A téma lezárva, nem küldhető újabb hozzászólás'>");
	}
 }
 
 print("</p>");
 /* +1 megtekintés hozzáadása */
 $sor4 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'"), MYSQL_ASSOC);
 $sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET opens='" .($sor4['opens']+1). "' WHERE id='" .mysql_real_escape_string($getid). "'");
 
 print("<h3 class='header'><p class='header'>" .$sor4['name']. "</p></h3>"); // Fejléc
 
 while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Hozzászólások listázása
	// Felhasználók nevének betöltése
	$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['uId']. "'"), MYSQL_ASSOC);
	
	switch ($adat2['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
	{
		case -1:
			$usrRang = 'Kitiltva';
			break;
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
			print("\t<a href='viewtopic.php?pId=" .$sor['id']. "&action=editpost'><img src='/themes/" .$_SESSION['themeName']. "/edit_post_icon.gif' alt='Hozzászólás szerkesztése' border='0'></a>\t<a href='viewtopic.php?pId=" .$sor['id']. "&action=deletepost'><img src='/themes/" .$_SESSION['themeName']. "/icon_delete_post.jpg' alt='Hozzászólás törlése' border='0'></a>");
		}
	}
	
	print("</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$postBody);
	if ( $sor['edited'] == 1 )
	{
		// Ha a post szerkesztett, bekérjük a szerkesztő felhasználó adatait, és kiírjuk hogy mikor szerkesztették
		$sor5 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['euId']. "'"));
		print("<br>Utoljára szerkesztve: " .Datum("normal","m","d","H","i","s", $sor['eDate']). " (<a href='profile.php?id=" .$sor5['id']. "'>" .$sor5['username']. "</a>)");
	}
	print("</div></div>"); // Hozzászólás
	print("<div class='postright'>Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$sor['pDate']). "</b><br>&nbsp;");
	
	if ( file_exists("uploads/" .md5($adat2['username']). ".pict") )
	{
		print("<img src='uploads/" .md5($adat2['username']). ".pict' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
	} else {
		print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
	}
	
	
	print("<p><b><a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a></b><br>Rang: " .$usrRang. "<br>Hozzászólások: " .$adat2['postCount']. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
	print("</div></div>"); // Hozzászólás vége
	}
	break;
	
	
	case "editpost": // Hozzászólás szerkesztése
		/* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a hozzászólást
 // Adatok bekérése
 if ( $_POST['pId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['pId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['pId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['pId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 // Felhasználói rang, felhasználó ellenörzése
 $adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE id='" .mysql_real_escape_string($getid). "'")); // Post adatainak bekérése
 $adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$adat['uId']. "'"), MYSQL_ASSOC);
 if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
 {
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
	
	// De ha a felhasználó a hozzászólás szerzője
	if ( $_SESSION['userID'] == $adat['uId'])
	{
		$jog = 1; // Szerkesztési jogát visszadajuk
	}
 } // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
 
 // Téma zároltság ellenörzése
 $sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$adat['tId']. "'")); // Téma sora
 if ( $sor2['locked'] == 1 )
	$jog = 0; // Ha a téma, amelyben a hozzászólás van, zárolt, a hozzászólás nem szerkeszthető.
 
 if ( $jog == 0 )
 {
	SetTitle("Nincs privilégium");
	Hibauzenet("ERROR", "Nincs jogod a hozzászólás szerkesztéséhez, vagy a téma le van zárva");
 } else {
	if ( $_POST['submit'] == "Hozzászólás szerkesztése")
	{
		SetTitle("Hozzászólás szerkesztése");
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "posts SET pTitle='" .mysql_real_escape_string($_POST['title']). "', pText='" .mysql_real_escape_string($_POST['post']). "', edited=1, euId=" .$_SESSION['userID']. ", eDate=" .time(). " WHERE id='" .mysql_real_escape_string($getid). "'"); // Hozzászólás frissítése, szerkesztési adatok hozzáírása
		// Szerkesztés
		print("<div class='messagebox'>Hozzászólás sikeresen szerkesztve!<br><a href='viewtopic.php?id=" .$sor2['id']. "#pid" .$getid. "'>Vissza a hozzászóláshoz</a>");
		
		DoFooter();
		die(); // A többi kód ne fusson le
	}
	
	SetTitle("Hozzászólás szerkesztése");
	// Hozzászólás, és fórum kiírása
	print("<h1><center><p class='header'>Hozzászólás szerkesztése</p></center></h1>");
	$postBody = $adat['pText']; // Nyers
	$postBody = EmoticonParse($postBody); // Hangulatjelek hozzáadása BB-kódként
	$postBody = HTMLDestroy($postBody); // HTML kódok nélkül 
	$postBody = BBDecode($postBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
	
	print("<div class='post'>"); // Fejléc
	print("<div class='postbody'><h3 class='postheader'><p class='header'><a name='pid" .$adat['id']. "'></a>" .$adat['pTitle']. "");
	print("</p></h3>"); // Hozzászólás fejléc
	print("<div class='content'>" .$postBody. "</div></div>"); // Hozzászólás
	print("<div class='postright'>Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$adat['pDate']). "</b>");
	
	if ( file_exists("uploads/" .md5($adat2['username']). ".pict") )
	{
		print("<img src='uploads/" .md5($adat2['username']). ".pict' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
	} else {
		print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
	}
	
	print("<p><b><a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a></b><br>Rang: " .$usrRang. "<br>Hozzászólások: " .$adat2['postCount']. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
	print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
	print("</div></div>"); // Hozzászólás vége
	
	print("<br style='clear: both'>
		<a href='viewtopic.php?id=" .$sor2['id']. "'><< Vissza a témához</a><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Hozzászólás szerkesztése: " .$adat['pTitle']. "</span>
			<p class='formText'>Cím: <input type='text' name='title' size='70' value='" .$adat['pTitle']. "'></p>
			<div class='postbox'><p class='formText'>Hozzászólás:<br>
			<textarea rows='20' name='post' cols='70'>" .$adat['pText']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .$_SESSION['themeName']. "/emoticons.php' onClick=\"window.open('/themes/" .$_SESSION['themeName']. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			<input type='hidden' name='pId' value='" .$adat['id']. "'>
			<input type='hidden' name='action' value='editpost'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='submit' value='Hozzászólás szerkesztése'>
			</fieldset>
			</form><br>");
 }
 
		break;
	case "deletepost": // Hozzászólás törlése
		/* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a hozzászólást
 // Adatok bekérése
 if ( $_POST['pId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['pId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['pId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['pId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 // Felhasználói rang, felhasználó ellenörzése
 $adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE id='" .mysql_real_escape_string($getid). "'")); // Post adatainak bekérése
 $adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT id, username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$adat['uId']. "'"), MYSQL_ASSOC);
 if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
 {
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
	
	// De ha a felhasználó a hozzászólás szerzője
	if ( $_SESSION['userID'] == $adat['uId'])
	{
		$jog = 1; // Szerkesztési jogát visszadajuk
	}
 } // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
 
 // Téma zároltság ellenörzése
 $sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$adat['tId']. "'")); // Téma sora
 if ( $sor2['locked'] == 1 )
	$jog = 0; // Ha a téma, amelyben a hozzászólás van, zárolt, a hozzászólás nem szerkeszthető.
 
 if ( $jog == 0 )
 {
	SetTitle("Nincs privilégium");
	Hibauzenet("ERROR", "Nincs jogod a hozzászólás szerkesztéséhez, vagy a téma le van zárva");
 } else {
		SetTitle("Hozzászólás törlése");
		
		// Felhasználó hozzászólásszám csökkentése
		$sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$adat['uId']. "'")); // A hozzászólást beküldő felhasználó adatai
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET postCount='" .($sor2['postCount']-1). "' WHERE id='" .$adat['uId']. "'"); // -1 hozzászólás a felhasználótól
		
		// Téma hozzászólásszám csökkentése
		$sor3 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$adat['tId']. "'"));
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET replies='" .($sor3['replies']-1). "' WHERE id='" .$adat['tId']. "'");
		
		// Fórum hozzászólásszám csökkentése
		$sor4 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum WHERE id='" .$sor3['fId']. "'"));
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."forum SET posts='" .($sor4['posts']-1). "' WHERE id='" .$sor3['fId']. "'");
		
		// Hozzászólás eltávolítása az adatbázisból
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."posts WHERE id='" .mysql_real_escape_string($getid). "'");
		// Hozzászólás törlése
		print("<div class='messagebox'>Hozzászólás sikeresen törölve!<br><a href='viewtopic.php?id=" .$adat['tId']. "#pid" .$getid. "'>Vissza a témához</a>");
		
		DoFooter();
		die(); // A többi kód ne fusson le
	}
 }
 DoFooter();
?>