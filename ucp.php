<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* ucp.php
   felhasználói vezérlőpult
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('ucp.php');
 SetTitle("Felhasználói vezérlőpult");
 
 if ( $_SESSION['userID'] == $NULL )
 {
	Hibauzenet("ERROR", "A felhasználói vezérlőpult megtekintéséhez be kell jelentkezned!");
	DoFooter();
	die();
 }
 
 print("<center><h2 class='header'>Felhasználói vezérlőpult</h2></center>");
 $felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$_SESSION['userID']. "'"));
 print("<div class='menubox'><a href='ucp.php'>Kezdőlap</a> • <a href='ucp.php?set=passw' class='menuItem'>Jelszó módosítása</a> • <a href='ucp.php?set=theme' class='menuItem'>Témaváltás</a> • <a href='ucp.php?set=avatar' class='menuItem'>Megjelenítendő kép feltöltése</a> • <a href='ucp.php?set=otherdata' class='menuItem'>Egyéb adatok szerkesztése</a></div><br>");
 
 if ( $_POST['set'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$gpset = $_POST['set'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['set'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$gpset = $_GET['set'];
	} else {
		// Sehogy nem érkezett adat
		$gpset = $NULL;
	}
 }
 global $tdszam;
 $tdszam = 0;
 
 function UjTd()
 {
	global $tdszam;
	if ( $tdszam == 2)
	{
		print("</tr><tr>");
		$tdszam = 0;
	}
 }
 
 switch ( $gpset )
 {
	case "passw": // Jelszó módosítása
		if ( ( $_POST['seta'] == "setpass" ) && ( $_POST['pwd'] != $NULL ) && ( $_POST['npass'] != $NULL ) && ( $_POST['npassdva'] != $NULL ) )
		{
			// A jelszó átállítását lefuttatjuk, ha:
			// ez a rejtett parancs jön (a modulon belül)
			// megadtuk az aktuális jelszót, és kétszer az új jelszót
			// az aktuális jelszó tényleg az aktuális jelszó
			// a két új jelszó egyenlő
			if ( ( $_POST['npass'] == $_POST['npassdva'] ) && ( $_POST['pwd'] == $_SESSION['pass'] ) )
			{
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET pwd='" .md5($_POST['npass']). "' WHERE id='" .$_SESSION['userID']. "'");
				$user->Logout(); // Kijelentkeztetés
				ReturnTo("A jelszavazad sikeresen megváltozott.<br>Mostantól bejelentkezhetsz az új jelszavaddal!", "index.php", "Kezdőlap", TRUE);
				DoFooter();
				die();
			}
			
			// Ha nem fut le a kód, hibát jelenítünk meg
			if ( $_POST['pwd'] != $_SESSION['pass'] ) // Érvénytelen aktuális jelszó
				Hibauzenet("CRITICAL", "Érvénytelen jelszó", "Az aktuális jelszóként megadott érték érvénytelen, mivel nem az aktuális jelszavad!");
			
			if ( $_POST['npass'] != $_POST['npassdva'] ) // A két új jelszó nem egyeizk
				Hibauzenet("CRITICAL", "Érvénytelen jelszó", "Az új jelszóként megadott kód, és az ismétlése nem egyezik.");
		}
		
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<p class='formText'>Aktuális jelszavad: <input type='password' name='pwd'><br>
			Új jelszavad: <input type='password' name='npass'><br>
			Új jelszavad (mégegyszer): <input type='password' name='npassdva'>
			<input type='hidden' name='set' value='passw'>
			<input type='hidden' name='seta' value='setpass'>
			<input type='submit' value='Adatok szerkesztése'></form>");
		break;
	case "theme": // Témaváltás
		
		if ( ($_GET['seta'] == "settheme") && ($_GET['themename'] != $NULL) )
		{
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET theme='" .mysql_real_escape_string($_GET['themename']). "' WHERE id='" .$_SESSION['userID']. "'");
			ReturnTo("A témát sikeresen módosítottad!", "ucp.php", "Vissza a vezérlőpultba", TRUE);
			DoFooter();
			die();
		}
		
		if ( $felhasznalo['theme'] == $NULL ) // Ha a felhasználónál nincs beálított téma, a defaultot használja
		{
			$usrtema = "default";
		} else {
			// Ha van, kiírjuk a nevét
			$usrtema = $felhasznalo['theme'];
		}
		
		print("Aktuális használt téma: <a href='themes/" .$usrtema. "/index.php'>" .$usrtema. "</a><br>");
		
		/* Elérhető témák listázása egy eddig ki nem próbált algoritmussal */
		$dir = "./themes/"; // Téma gyökérmappa
		$exempt = array('.', '..', '.svn', '_svn'); // Megadunk egy le nem kérdezendő kivétellistát
		print("<table><tr>");
		if (is_dir($dir)) 
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if(!in_array(strtolower($file),$exempt))
					{
						if ( filetype($dir . $file) == "dir" )
						{
							/* Bekértük az összes MAPPÁT a themes mappából */
							if ( file_exists($dir . $file . "/style.css") )
							{
								// Csak olyan mappákkal foglalkozunk, ahol van STYLE.CSS (tehát tud témát leírni)
								print("<td style='widht: 50%'>");
								
								if ( file_exists($dir . $file . "/preview.jpg") )
								{
									// Ha van előnézeti kép, megjelenítjük azt is
									print("<a href='" .$dir . $file . "/preview.jpg' alt='" .$file. " téma előnézete'><img src='" .$dir . $file . "/preview.jpg' width='320' height='240' alt='" .$file. " téma előnézete' border='0'></a>");
								} else {
									// Ha nincs, a "nincs előnézeti kép" képet jelenítjük meg
									print("<img src='themes/nopreview.jpg'>");
								}
								
								print("<br style='clear: both'>" .$file ."&nbsp;<a href='ucp.php?set=theme&seta=settheme&themename=" .$file. "'>Téma beállítása</a></td>");
								$tdszam++;
								UjTd();
							}
							echo "<br>";
						}
					}
				}
				closedir($dh);
			}
		}
		print("</tr></table>");
		
		break;
	case "otherdata": // Egyéb adatok szerkesztése
		if ( $_POST['seta'] == "setdatas" )
		{
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET bemutatkozas='" .mysql_real_escape_string($_POST['bemutatkozas']). "', thely='" .mysql_real_escape_string($_POST['thely']). "' WHERE id='" .$_SESSION['userID']. "'");
			ReturnTo("Adatok frissítve!", "ucp.php", "Vissza a vezérlőpultba", TRUE);
			DoFooter();
			die();
		}
		
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<p class='formText'>Bemutatkozás: <textarea name='bemutatkozas' rows='8' cols='25'>" .$felhasznalo['bemutatkozas'] ."</textarea><br>
			Tartózkodási hely: <input type='text' name='thely' value='" .$felhasznalo['thely']. "'><br>
			<input type='hidden' name='set' value='otherdata'>
			<input type='hidden' name='seta' value='setdatas'>
			<input type='submit' value='Adatok szerkesztése'></form>");
			
		break;
	case "avatar": // Megjelenítendő kép szerkesztése
		if ( $_POST['feltolt'] == "yes" ) // Ha elküldtük a feltöltése parancsot
		{
			if ( $_FILES['picfile']['size'] > 2097152 )
			{
				Hibauzenet("ERROR", "A feltöltött fájl túl nagy méretű!", "A feltöltött fájl mérete " .DecodeSize($_FILES['picfile']['size']). ", azonban a maximális megengedett méret csak " .DecodeSize(2097152). "! Kérlek töltsd fel egy kisebb méretű fájlt!");
			} else {
				if ( in_array($_FILES['picfile']['type'], array("image/gif", "image/jpeg", "image/png")) )
				{
					if(move_uploaded_file($_FILES['picfile']['tmp_name'], "uploads/" .md5($_SESSION['username']). ".pict"))
					{
						// Sikeres feltöltés esetén
						ReturnTo("Az avatarod frissítése sikeresen megtörtént!", "ucp.php", "Vissza a vezérlőpultba", TRUE);
					} else {
						// Hiba volt a feltöltés közben
						Hibauzenet("ERROR", "A fájlt nem sikerült feltölteni!");
						ReturnTo("", "ucp.php", "Vissza a vezérlőpultba", FALSE);
					}
				} else {
					Hibauzenet("WARNING", "A feltöltött fájlnak JPG, GIF vagy PNG típusúnak kell lennie!", "A te általad feltöltött fájl (" .$_FILES['picfile']['name']. ") nem érvényes képfájl!");
				}
			}
		}
		
		print("<div class='userbox'>Aktuális megjelenítendő képed:&nbsp;");
		if ( file_exists("uploads/" .md5($_SESSION['username']). ".pict") )
		{
			print("<img src='uploads/" .md5($_SESSION['username']). ".pict' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		} else {
			print("<img src='themes/" .$_SESSION['themeName']. "/anon.png' width='128' height='128' alt='" .$_SESSION['username']. " megjelenítendő képe'>");
		}
		
		print("<br>
		<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
		<p class='formText'>Az aktuális avatarod megváltoztatásához tallóz be egy JPG, PNG vagy GIF fájlt a merevlemezedről. A kép ajánlott mérete <b>128x128 pixel</b>, fájlmérete maximálisan: " .DecodeSize(2097152). "
		<br><input name='picfile' type='file' size='50' accept='application/octet-stream'><br>
	<input type='submit' value='Feltöltés'>
	<input type='hidden' name='set' value='avatar'>
	<input type='hidden' name='feltolt' value='yes'></p>
	</form>");
		
		break;
	default:
		
		switch ($felhasznalo['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
		{
			case -1:
				$usrRang = 'Kititva';
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
 
		print("<div class='menubox'><span class='menutitle'>Információk</span><br>
		<p class='formText'>
			<b>Regisztráció időpontja:</b> " .Datum("normal","kisbetu","dL","H","i","s", $felhasznalo['regdate']). "<br>
			<b>Aktiválás időpontja:</b> " .Datum("normal","kisbetu","dL","H","i","s", $felhasznalo['activatedate']). "<br>
			<b>Rang:</b> " .$usrRang. "</p></div>");
 }
 DoFooter();
?>