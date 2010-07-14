<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* profile.php
   profil megjelenítése
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('profile.php');
 
 /* Csak akkor jelenik meg tartalom, ha a lekérdező felhasználó be van jelentkezve, és megadta a lekérdezni kívánt felhasználó id-jét */
 if ( $_GET['id'] == $NULL ) {
	Hibauzenet("CRITICAL", "Érvénytelen paraméterek", "A kívánt felhasználó ID-jét kötelező megadni!");
	DoFooter();
	die();
 }
 
 if ( $_SESSION['userLevel'] == 0 )
 {
	Hibauzenet("BAN", "Be kell jelentkezned a felhasználói profilok megtekintéséhez");
	DoFooter();
	die();
 }
 
 $felhasznalo = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($_GET['id']). "'")); // Adatok bekérése a megadott felhasználóról
 
 if ( $felhasznalo == FALSE )
 {
	SetTitle("Nincs ilyen ID-jű felhasználó");
	Hibauzenet("ERROR", "Nincs ilyen ID-jű felhasználó: " .$_GET['id'], "A megadott felhasználó nem létezik");
	DoFooter();
	die();
 }
 SetTitle("Profil megtekintése: " .$felhasznalo['username']);
 
 print("<center><h2 class='header'>Profil megtekintése: " .$felhasznalo['username']. "</h2></center>
<table><tr><td style='width: 50%' valign='top'>
<div class='userbox'><span class='formHeader'> </span><p class='formText'>Felhasználó neve: " .$felhasznalo['username']. "<br>");

	if ( $felhasznalo['realName'] != $NULL ) // Valódi név kiírása csak akkor, ha van valódi név
		print("Valódi neve: " .$felhasznalo['realName']. "<br>");
		
print("E-mail címe: ");
	
	if ( $felhasznalo['showemail'] == 1 ) // A felhasználó e-mail címének megjelenítése csak akkor, ha ő ezt kérte
	{
		$usremail = str_replace("@", " (kukac) ", $felhasznalo['email']);
		$usremail = str_replace(".", " (pont) ", $usremail);
		print($usremail);
	} else {
		print("nem publikus");
	}
 
 print("<br>
Regisztráció időpontja: " .Datum("normal","kisbetu","dL","H","i","s", $felhasznalo['regdate']). "<br>
Legutoljára bejelentkezett: ");
	
	if ( $felhasznalo['lastlogintime'] == 0 ) // Ha a felhasználó UNIX idő szerint 0-kor (ergó: soha) nem lépett be
	{
		print("<span style='color: red; font-weight: bold'>soha</span>"); // azt írjuk ki
	} else {
		// Ha nem, akkor a tényleges időt, magyar dátummá formázva
		print(Datum("normal","m","d","H","i","", $felhasznalo['lastlogintime']));
	}
	
	if ( ($felhasznalo['loggedin'] == 1) && ($felhasznalo['cursessid'] != $NULL) && ($felhasznalo['curip'] != "0.0.0.0" ) ) // Ha a felhasználó jelenleg be van lépve, kiírjuk
		print("&nbsp;&nbsp;<span style='color: darkgreen; font-weight: bold'>(jelenleg is itt tartózkodik)</span>");
	
print("<br>
");
 
 print("</p></td><td style='width: 50%' valign='top'><div class='userbox'><span class='formheader'> </span>
<p class='formText'>Használt téma: <a href='themes/");
	
	if ( $felhasznalo['theme'] == $NULL ) // Ha a felhasználónál nincs beálított téma, a defaultot használja
	{
		$usrtema = "default";
	} else {
		// Ha van, kiírjuk a nevét
		$usrtema = $felhasznalo['theme'];
	}
 print($usrtema. "/index.php'>" .$usrtema. "</a><br>
Hozzászólások száma: " .$felhasznalo['postCount']. "");
 print("</p></div></td></tr></table>
 <div class='userbox'>
 <p class='formText'>");
	
	switch ($felhasznalo['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
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
	print("Rang: " .$usrRang. "<br>");
	
	if ( ($_SESSION['userLevel'] == 3) && ($_GET['id'] != $_SESSION['userID']) )
	{
		if ( ($_GET['set'] == "userlevel") && ( $_GET['userrank'] != $NULL) )
		{
			
			// Módosítás végrehajtása
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET userLevel='" .mysql_real_escape_string($_GET['userrank']). "' WHERE id='" .mysql_real_escape_string($_GET['id']). "'");
			
			switch ($_GET['userrank']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
			{
				case -1:
					$uNewRang = 'Kitiltva';
					break;
				case 0:
					$uNewRang = 'Nincs aktiválva';
					break;
				case 1:
					$uNewRang = 'Felhasználó';
					break;
				case 2:
					$uNewRang = 'Moderátor';
					break;
				case 3:
					$uNewRang = 'Adminisztrátor';
					break;
			}
			
			print("<p class='formText'>Felhasználó rangja <b>" .$usrRang. "</b> módosítva: <b>" .$uNewRang. "</b>!</p>");
		} else {
		// Felhasználó rangjának állítása a profilban történik
		// ha az állítást végző felhasználó admin, és nem a saját adatlapja
		print("<form method='GET' action='" .$_SERVER['PHP_SELF']. "'>
			<fieldset class='submit-buttons'><p class='formText'><input type='radio' name='userrank' value='-1'");
			if ( $felhasznalo['userLevel'] == -1 )
				print(" checked ");
			print("> Kitiltva <input type='radio' name='userrank' value='1'");
			if ( $felhasznalo['userLevel'] == 1 )
				print(" checked ");
			print("> Felhasználó <input type='radio' name='userrank' value='2'");
			if ( $felhasznalo['userLevel'] == 2 )
				print(" checked ");
			print("> Moderátor <input type='radio' name='userrank' value='3'");
			if ( $felhasznalo['userLevel'] == 3 )
				print(" checked ");
			print("> Adminisztrátor</p>
			<input type='hidden' name='set' value='userlevel'>
			<input type='hidden' name='id' value='" .$_GET['id']. "'>
			<input type='submit' value='Rang módosítása'></fieldset>
		</form>");
		}
	}
	
	if ( $felhasznalo['bemutatkozas'] != $NULL ) // Ha a felhasználónak van bemutatkozása, kiírjuk
	{
		$bemutatkozas = $felhasznalo['bemutatkozas']; // Nyers
		$bemutatkozas = EmoticonParse($bemutatkozas); // Hangulatjelek hozzáadása BB-kódként
		$bemutatkozas = HTMLDestroy($bemutatkozas); // HTML kódok nélkül 
		$bemutatkozas = BBDecode($bemutatkozas); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
		print("Bemutatkozás: " .$bemutatkozas. "<br>");
	}
	
	if ( $felhasznalo['thely'] != $NULL ) // Ha van tartózkodási hely megadva, kiírjuk
		print("Tartózkodási hely: " .$felhasznalo['thely']. "<br>");
 
 print("</div></p>");
 DoFooter();
?>