<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* news.php
   hírek
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('news.php');
 SetTitle("Hírek");
 
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
 
 switch ($ekson) // A beérkező ACTION parancs alapján nézzük, mit csináljon a script
 {
	// Ha a beérkező parancs üres, vagy nincs beérkező parancs
	case $NULL:
	case "":
		// Kislisttázuk a híreket, mindegyiket, azonban mindig csak az első három bekezdést
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
			print("<a href='news.php?action=newentry'>Hír beküldése</a><br>"); // Hír beküldése link, ha a felhasználó moderátor/admin
		
		/* Hírek betöltése */
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news");
 
		/* Hírek listázása */
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'"));
			print("<div class='newsitem'><h2 class='header'><p class='header'>" .$sor['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $sor['postDate']). ", " .$felhasznaloadat['username']. ")</p></h2>
"); // Fejléc
			
			// Hír első három bekezdésének megjelenítése
			$bekezdesek = explode("\r\n", $sor['text']);
			$rovidszoveg = $bekezdesek[0]."<br>".$bekezdesek[1]."<br>".$bekezdesek[2];
			print($rovidszoveg . "<br><br><a href='news.php?id=" .$sor['id']. "&action=view'>Tovább >> (bővebben, kommentelés)</a></div>");
		}
		
		break;
	
	case "view": // Ha VIEW parancsot kapunk 
		// Szükséges bejövő paraméter az ID, mely a megtekiteni kívánt hír azonosítóját tartalmazza
		
		if ( ($_GET['id'] == $NULL ) || ($_GET['id'] == "") )
			Hibauzenet("CRITICAL", "A hír azonosítóját kötelező megadni");
		
		// Bekérjük az aktuális hír adatait (ezt rögtön tömbbé is tömörítjük)
		$hir = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news WHERE id='" .$_GET['id']. "'"));
		
		// Ha nem létezik ilyen hír, szintén hibaüzenetet generálunk
		if ( $hir == FALSE )
			Hibauzenet("CRITICAL", "A megadott azonosítószámú hír nem létezik");
		
		// Felhasználó adatai
		$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$hir['uId']. "'"));
		
		/* Hír formázása */
		$hirBody = $hir['text']; // Nyers
		$hirBody = EmoticonParse($hirBody); // Hangulatjelek hozzáadása BB-kódként
		$hirBody = HTMLDestroy($hirBody); // HTML kódok nélkül 
		$hirBody = BBDecode($hirBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
		
		print("<div class='newsitem'><h2 class='header'><p class='header'>" .$hir['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $hir['postDate']). ", " .$felhasznaloadat['username']. ")</p></h2><br>" .$hirBody. "</div><br>"); // Hír szövege
		
		/* Kommentek */
		print("<h2 class='header'><p class='header'>Hozzászólások</p></h2>");
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE nId='" .$_GET['id']. "'");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			/* Komment formázása */
			$comBody = $sor['text']; // Nyers
			$comBody = EmoticonParse($comBody); // Hangulatjelek hozzáadása BB-kódként
			$comBody = HTMLDestroy($comBody); // HTML kódok nélkül 
			$comBody = BBDecode($comBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
			
			print("<div class='post'><div class='postbody'><div class='content'>" .$comBody. "</div></div><div class='postright'>");
			
			/* Hozzászóló adatai */
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
			
			print("Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$sor['pDate']). "</b><p><b>" .$adat2['username']. "</b><br>Rang: " .$usrRang. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
			print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
			print("</div></div>"); // Hozzászólás vége
		}
		
		/* Komment beküldése */
		if ( $_SESSION['userLevel'] == 0)
		{
			// A felhasználó nem küldhet hozzászólást
		} else {
			print("<br style='clear:both'><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Új hozzászólás beküldése</span>
			<div class='postbox'><p class='formText'>Hozzászólás:<br>
			<textarea rows='15' name='post' cols='70'></textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .THEME_NAME. "/emoticons.php' onClick=\"window.open('/themes/" .THEME_NAME. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			<input type='hidden' name='action' value='postcomment'>
			<input type='hidden' name='id' value='" .$_GET['id']. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' value='Hozzászólás elküldése'>
			</fieldset>
			</form><br>"); // Hozzászólás beküldési űrlap
		}
		
		break;
	
	case "postcomment": // Hozzászólás beküldése
		if ( ($_POST['id'] == $NULL) || ($_POST['post'] == $NULL) )
		{
			Hibauzenet("ERROR", "Nem küldhető üres hozzászólás, vagy a hozzászólásod egy nem létező hírhez küldted");
		} else {
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news_comments(nId, uId, text, postDate) VALUES
				(" .$_POST['id']. ", " .$_SESSION['userID']. ", '" .$_POST['post']. "', " .time(). ")");
			print("<div class='messagebox'>Hozzászólás sikeresen beküldve<br><a href='news.php?id=" .$_POST['id']. "&action=view'><< Vissza a hírehez</a></div>");
		}
		
		break;
		
	case "newentry": // Új hír beküldése
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Új hír beküldése</span>
			<p class='formText'>Cím: <input type='text' name='title' size='70' value='" .$_POST['title']. "'></p>
			<div class='postbox'><p class='formText'>Hír szövege:<br>
			<textarea rows='20' name='post' cols='70'>" .$_POST['post']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .THEME_NAME. "/emoticons.php' onClick=\"window.open('/themes/" .THEME_NAME. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div><br style='clear: both'>
			<input type='hidden' name='action' value='postentry'>
			<fieldset class='submit-buttons'>
				<input type='submit' value='Hír beküldése'>
			</fieldset>
			</form><br>");
		break;
	
	case "postentry": // Beküldött hír tárolása
		if ( ($_POST['title'] != "") && ($_POST['post'] != "") )
		{
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news(title, text, postDate, uId) VALUES ('" .$_POST['title']. "', '" .$_POST['post']. "', " .time(). ", " .$_SESSION['userID']. ")");
			print("<div class='messagebox'>Hír (" .$_POST['title']. ") sikeresen beküldve<br><a href='news.php'><< Vissza a hírekhez</a></div>");
		}
		
		break;
 }
 
 DoFooter(); // Lábléc
?>