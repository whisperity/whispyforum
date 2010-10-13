<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* news.php
   hírek
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('news.php');
 
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
		SetTitle("Hírek");
		// Kislisttázuk a híreket, mindegyiket, azonban mindig csak az első három bekezdést
		if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) )
			print("<a href='news.php?action=newentry'>Hír beküldése</a><br>"); // Hír beküldése link, ha a felhasználó moderátor/admin
		
		/* Hírek betöltése */
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news ORDER BY id DESC");
 
		/* Hírek listázása */
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'"));
			print("<div class='newsitem'><h2 class='header'><p class='header'>" .$sor['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $sor['postDate']). ", <a href='profile.php?id=" .$felhasznaloadat['id']. "'>" .$felhasznaloadat['username']. "</a>)</p></h2>
"); // Fejléc
			
			// Hír első három bekezdésének megjelenítése
			$bekezdesek = explode("\r\n", $sor['text']);
			$rovidszoveg = $bekezdesek[0]."\n".$bekezdesek[1]."\n".$bekezdesek[2];
			/* Hír formázása */
			$hirBody = $rovidszoveg; // Nyers
			$hirBody = EmoticonParse($hirBody); // Hangulatjelek hozzáadása BB-kódként
			$hirBody = HTMLDestroy($hirBody); // HTML kódok nélkül 
			$hirBody = BBDecode($hirBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
			print($hirBody . "<br><br><a href='news.php?id=" .$sor['id']. "&action=view'>Tovább >> (bővebben");
			if ( $sor['commentable'] == 1) // Ha a hír kommentelhető, a bővebben linkhez odaillesztjük a kommentelés szót
				print(", kommentelés");
				
			print(")</a></div>");
		}
		
		break;
	
	case "view": // Ha VIEW parancsot kapunk 
		// Szükséges bejövő paraméter az ID, mely a megtekiteni kívánt hír azonosítóját tartalmazza
		
		if ( ($_GET['id'] == $NULL ) || ($_GET['id'] == "") )
			Hibauzenet("CRITICAL", "A hír azonosítóját kötelező megadni");
		
		// Bekérjük az aktuális hír adatait (ezt rögtön tömbbé is tömörítjük)
		$hir = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
		SetTitle($hir['title']);
		
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
		
		print("<div class='newsitem'><h2 class='header'><p class='header'>" .$hir['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $hir['postDate']). ", <a href='profile.php?id=" .$felhasznaloadat['id']. "'>" .$felhasznaloadat['username']. "</a>)</p></h2><br>" .$hirBody. "</div><br>"); // Hír szövege
		
		/* Kommentek */
		if ( $hir['commentable'] == 1)
		{
		// Csak akkor jelenítjük meg a kommenteket (és adunk lehetőséget a kommentelésre), ha a hír kommentelhető
		print("<h2 class='header'><p class='header'>Hozzászólások</p></h2>");
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE nId='" .mysql_real_escape_string($_GET['id']). "'");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			/* Komment formázása */
			$comBody = $sor['text']; // Nyers
			$comBody = EmoticonParse($comBody); // Hangulatjelek hozzáadása BB-kódként
			$comBody = HTMLDestroy($comBody); // HTML kódok nélkül 
			$comBody = BBDecode($comBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
			
			print("<div class='post'><div class='postbody'>");
			
			if ( ($_SESSION['userLevel'] == 2) || ($_SESSION['userLevel'] == 3) ||  ($_SESSION['userID'] == $sor['uId']) )
			{ // Csak moderátor, admin, valamint a hozzászólás beküldője tudja szerkeszteni, törölni a hozzászólást
				print("\t<a href='news.php?cid=" .$sor['id']. "&action=cedit'><img src='/themes/" .$_SESSION['themeName']. "/edit_post_icon.gif' alt='Hozzászólás szerkesztése' border='0'></a>\t<a href='news.php?cid=" .$sor['id']. "&action=cdelete'><img src='/themes/" .$_SESSION['themeName']. "/icon_delete_post.jpg' alt='Hozzászólás törlése' border='0'></a>");
			}
			
			print("<div class='content'>" .$comBody. "</div></div><div class='postright'>");
			
			/* Hozzászóló adatai */
			$adat2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['uId']. "'"));
			
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
			
			print("Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$sor['pDate']). "</b><br>&nbsp;");
			
			if ( file_exists("uploads/" .md5($adat2['username']). ".pict") )
			{
				print("<img src='uploads/" .md5($adat2['username']). ".pict' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
			} else {
				print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$adat2['username']. " megjelenítendő képe'>");
			}
			
			print("<p><b><a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a></b><br>Rang: " .$usrRang. "<br>"); // Hozzászólás adatai (hozzászóló, stb.)
			print("Csatlakozott: " .Datum("normal","m","d","H","i","", $adat2['regdate']). ""); // Hozzászóló adatai
			print("</div></div>"); // Hozzászólás vége
		}
		
		/* Komment beküldése */
		if ( $_SESSION['userLevel'] == 0)
		{
			// A felhasználó nem küldhet hozzászólást
		} else {
			SetTitle("Hozzászólás beküldése");
			print("<br style='clear:both'><form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Új hozzászólás beküldése</span>
			<div class='postbox'><p class='formText'>Hozzászólás:<br>
			<textarea rows='15' name='post' cols='70'></textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .$_SESSION['themeName']. "/emoticons.php' onClick=\"window.open('/themes/" .$_SESSION['themeName']. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			<input type='hidden' name='action' value='postcomment'>
			<input type='hidden' name='id' value='" .$_GET['id']. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' value='Hozzászólás elküldése'>
			</fieldset>
			</form><br>"); // Hozzászólás beküldési űrlap
		}
		}
		break;
	
	case "postcomment": // Hozzászólás beküldése
		if ( ($_POST['id'] == $NULL) || ($_POST['post'] == $NULL) )
		{
			Hibauzenet("ERROR", "Nem küldhető üres hozzászólás, vagy a hozzászólásod egy nem létező hírhez küldted");
		} else {
			SetTitle("Hozzászólás beküldve");
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news_comments(nId, uId, text, postDate) VALUES
				(" .$_POST['id']. ", " .$_SESSION['userID']. ", '" .mysql_real_escape_string($_POST['post']). "', " .time(). ")");
			ReturnTo("Hozzászólás sikeresen beküldve", "news.php?id=" .$_POST['id']. "&action=view", "Vissza a hírhez", TRUE);
		}
		
		break;
	
	case "cedit": // Hozzászólás szerkesztése
		SetTitle("Hozzászólás szerkesztése");
		/* Inicializációs rész */
		$jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a hozzászólást
		
		$getid = $_GET['cid']; // Hozzászólás azonosítója
		
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE id='" .mysql_real_escape_string($getid). "'")); // Hozzászólás adatainak bekérése
		$adat2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username, userLevel, postCount, regdate FROM " .$cfg['tbprf']. "user WHERE id='" .$adat['uId']. "'")); // Felhasználó adatainak bekérése
		$hiradat = mysql_fetch_assoc($sql->Lekerdezes("SELECT commentable FROM " .$cfg['tbprf']."news WHERE id='" .$adat['nId']. "'")); // A hozzászóláshoz kapcsolódó hír kommentelhetőségének bekérése
		
		if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
		{
			$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
	
			// De ha a felhasználó a hozzászólás szerzője
			if ( $_SESSION['userID'] == $adat['uId'])
			{
				$jog = 1; // Szerkesztési jogát visszadajuk
			}
		} // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
		
		// Ha a hír nem kommentelhető, a többi eseménytől függetlenül nem szerkeszthetjük a hozzászólást
		if ( $hiradat['commentable'] == 0 )
			$jog = 0;
		
		if ( $jog == 0 )
		{
			SetTitle("Nincs privilégium");
			Hibauzenet("ERROR", "Nincs jogod a hozzászólás szerkesztéséhez, vagy a hír nem kommentelhető");
		} else {
		
		if ( $_POST['submit'] == "Hozzászólás szerkesztése")
		{
			SetTitle("Hozzászólás szerkesztése");
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "news_comments SET text='" .$_POST['post']. "' WHERE id='" .$_POST['cid']. "'"); // Hozzászólás frissítése, szerkesztési adatok hozzáírása
			
			$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE id='" .$_POST['cid']. "'")); // Hozzászólás adatainak bekérése
			
			// Szerkesztés
			ReturnTo("Hozzászólás sikeresen szerkesztve", "news.php?id=" .$adat['nId']. "&action=view", "Vissza a hírhez", TRUE);
			DoFooter();
			die(); // A többi kód ne fusson le
		}
		SetTitle("Hozzászólás szerkesztése");
		// Hozzászólás, és fórum kiírása
		print("<h1><center><p class='header'>Hozzászólás szerkesztése</p></center></h1>");
		$postBody = $adat['text']; // Nyers
		$postBody = EmoticonParse($postBody); // Hangulatjelek hozzáadása BB-kódként
		$postBody = HTMLDestroy($postBody); // HTML kódok nélkül 
		$postBody = BBDecode($postBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
	
		print("<div class='post'>"); // Fejléc
		print("<div class='postbody'>");
		print("<div class='content'>" .$postBody. "</div></div>"); // Hozzászólás
		print("<div class='postright'>Hozzászólás időpontja: <b>" .Datum("normal","kisbetu","dL","H","i","s",$adat['pDate']). "</b><br>&nbsp;");
		
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
		<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Hozzászólás szerkesztése</span>
			<div class='postbox'><p class='formText'>Hozzászólás:<br>
			<textarea rows='20' name='post' cols='70'>" .$adat['text']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .$_SESSION['themeName']. "/emoticons.php' onClick=\"window.open('/themes/" .$_SESSION['themeName']. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div>
			<input type='hidden' name='action' value='cedit'>
			<input type='hidden' name='cid' value='" .$adat['id']. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='submit' value='Hozzászólás szerkesztése'>
			</fieldset>
			</form><br>");
		}
		
		break;
	case "cdelete": // Hozzászólás törlése
		SetTitle("Hozzászólás törlése");
		/* Inicializációs rész */
		$jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a hozzászólást
		
		$getid = $_GET['cid']; // Hozzászólás azonosítója
		
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE id='" .$getid. "'")); // Hozzászólás adatainak bekérése
		$hiradat = mysql_fetch_assoc($sql->Lekerdezes("SELECT commentable FROM " .$cfg['tbprf']."news WHERE id='" .$adat['nId']. "'")); // A hozzászóláshoz kapcsolódó hír kommentelhetőségének bekérése
		if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
		{
			$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
		} // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
		
		// Ha a hír nem kommentelhető, a többi eseménytől függetlenül nem szerkeszthetjük a hozzászólást
		if ( $hiradat['commentable'] == 0 )
			$jog = 0;
		
		if ( $jog == 0 )
		{
			SetTitle("Nincs privilégium");
			Hibauzenet("ERROR", "Nincs jogod a hozzászólás szerkesztéséhez, vagy a hír nem kommentelhető");
		} else {
			SetTitle("Hozzászólás törölve");
			$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news_comments WHERE id='" .$getid. "'")); // Hozzászólás adatainak bekérése
			
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."news_comments WHERE id='" .$getid. "'"); // Törlés
			
			ReturnTo("Hozzászólás sikeresen törölve", "news.php?id=" .$adat['nId']. "&action=view", "Vissza a hírhez", TRUE); // Visszatérési link (az $adat előbb jött létre, ezért nem zavar be a törlés)
			
			// A többi kód ne fusson le
			DoFooter();
			die();
		}
		
		break;
	case "newentry": // Új hír beküldése
		SetTitle("Új hír beküldése");
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
			<span class='formHeader'>Új hír beküldése</span>
			<p class='formText'>Cím: <input type='text' name='title' size='70' value='" .$_POST['title']. "'></p>
			<div class='postbox'><p class='formText'>Hír szövege:<br>
			<textarea rows='20' name='post' cols='70'>" .$_POST['post']. "</textarea></div>
			<div class='postright'>"); // Bal oldali rész
			print("<a href='/themes/" .$_SESSION['themeName']. "/emoticons.php' onClick=\"window.open('/themes/" .$_SESSION['themeName']. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a>"); // Emoticon, BB-kód ablak
			print("</div><br style='clear: both'>
			<p class='formText'><input type='checkbox' name='commentable' value='1'>A hír kommentálható</p>
			<input type='hidden' name='action' value='postentry'>
			<fieldset class='submit-buttons'>
				<input type='submit' value='Hír beküldése'>
			</fieldset>
			</form><br>");
		break;
	
	case "postentry": // Beküldött hír tárolása
		SetTitle("Hír beküldve");
		if ( ($_POST['title'] != "") && ($_POST['post'] != "") )
		{
			
			switch ($_POST['commentable']) // A kommentálhatóság CHECKBOX-ból jön, ezért ha nincs bejelölve, NULL értéket kapunk
			{
				case $NULL:
					$commentable = 0;
					break;
				case 1:
					$commentable = 1;
					break;
			}
				
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."news(title, text, postDate, uId, commentable) VALUES ('" .$_POST['title']. "', '" .$_POST['post']. "', " .time(). ", " .$_SESSION['userID']. ", " .$commentable. ")");
			ReturnTo("Hír (" .$_POST['title']. ") sikeresen beküldve!", "news.php", "Vissza a hírhekhez", TRUE);
		}
		
		break;
 }
 
 DoFooter(); // Lábléc
?>