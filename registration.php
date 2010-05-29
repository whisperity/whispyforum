<?php	
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* registration.php
   felhasználói regisztráció
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('registration.php');
 
 $regPos = $_POST['regPos']; // Regisztrációs lépés
 
 print("<span class='regHeader'>Regisztráció</span><br>"); // Fejléc
 
 switch ($regPos) // A regisztrációs lépéseknek megfelelő szöveg kírása
 {
	case '': // Nincs érték, vagy
	case 0: // 0-s érték
		// Kezdés, feltételek
		print("Regisztrációs feltételek");
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']."'>
		<p class='formText'><input type='checkbox' name='elfogad' value='yes'> A regisztrációs feltételeket elolvastam, megértettem és elfogadtam, valamint kijelentem, hogy elmúltam 13 éves</p>
		<input type='hidden' name='regPos' value=1>
		<input type='submit' value='Tovább >> (adatok megadása)'>
		</form>");
		break;
	case 1:
		// Adatok megadása
		if ( $_POST['elfogad'] != 'yes' )
		{
			// Ha a felhasználó nem fogadta el a feltételeket, megszakítás
			
			Hibauzenet("ERROR", "A regisztráció befejezése sikertelen", "A folytatáshoz muszáj elfogadnod a regisztrációs feltételeket");
			print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<input type='hidden' name='regPos' value='0'>
			<input type='submit' value='Újrakezdés'>
			</form>");
		} else {
			print("A <span class='star'>*</span>-gal jelölt mezők kitöltése kötelező! Húzd az egeret a kis <sup>?</sup>-re a további információkért!");
			print("<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<p class='formText'>Választott felhasználói név<a name='' class='feature-extra'><span class='hover'><span class='h3'>Felhasználói név</span>Ezzel fogsz a későbbiekben belépni.<br>Csak szabványos karaktereket (angol abc betűi) és számokat tartalmazhat!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='username' value='" .$_POST['username']."'></p>
			<p class='formText'>Jelszó<a name='' class='feature-extra'><span class='hover'><span class='h3'>Jelszó</span>Ezzel fogsz a későbbiekben belépni.<br>Csak szabványos karaktereket (angol abc betűi) és számokat tartalmazhat!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='password' value='" .$_POST['password']."'></p>
			<p class='formText'>E-mail cím<a name='' class='feature-extra'><span class='hover'><span class='h3'>E-mail</span>Csak <b>létező</b> e-mail címet adj meg!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='email' value='" .$_POST['email']."'></p>
			<p class='formText'>Valódi neved: <input type='text' name='realname' value='" .$_POST['realname']."'></p>
			<input type='hidden' name='regPos' value=2>
			<fieldset class='submit-buttons'>
				<input type='submit' name='tovabb' value='Tovább >> (adatok elküldése)'>
			</fieldset>
		</form>");
		}
		break;
	case 2:
		// Regisztrációs adatok ellenörzése
		
		// Ha az adatok nem megfelelőek (hiányoznak), megszakítás
		if ( ($_POST['username'] == '') || ($_POST['email'] == '') )
		{
			Hibauzenet("ERROR", "Hiányzó adatok", "Nem töltöttél ki minden kötelező (<span class='star'>*</span>-gal jelölt) mezőt");
			print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<input type='hidden' name='regPos' value='1'>
			<input type='hidden' name='username' value='" .$_POST['username']. "'>
			<input type='hidden' name='password' value='" .$_POST['password']. "'>
			<input type='hidden' name='email' value='" .$_POST['email']. "'>
			<input type='hidden' name='realname' value='" .$_POST['realname']. "'>
			<input type='hidden' name='elfogad' value='yes'>
			<input type='submit' value='Újrakezdés'>
			</form>"); // Visszatérési űrlap az adatok visszaküldésével
		} else {
			print("Kérlek még egyszer ellenőrizd le regisztrációs adataidat:");
			print("<table>
			<tr>
				<td>Felhasználói név:</td>
				<td>" .$_POST['username']. "</td>
			</tr>
			<tr>
				<td>Jelszó:</td>
				<td>" .$_POST['password']. "</td>
			</tr>
			<tr>
				<td>E-mail cím:</td>
				<td>" .$_POST['email']. "</td>
			</tr>
			<tr>
				<td>Valódi név:</td>
				<td>" .$_POST['realname']. "</td>
			</tr>
		</table>");
		
		print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<input type='hidden' name='regPos' value='3'>
			<input type='hidden' name='username' value='" .$_POST['username']. "'>
			<input type='hidden' name='password' value='" .$_POST['password']. "'>
			<input type='hidden' name='email' value='" .$_POST['email']. "'>
			<input type='hidden' name='realname' value='" .$_POST['realname']. "'>
			<input type='hidden' name='elfogad' value='yes'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='vissza' value='<< Vissza (adatok módosítása)'>
				<input type='submit' name='tovabb' value='Tovább >> (regisztráció befejezése)'>
			</fieldset>
			</form>"); // Továbblépési/visszalépési űrlap (regPos=3-nál ellenőrizzük a visszalépést, ha vissza akkor visszalépünk)
		}
		break;
	case 3:
		if ( $_POST['vissza'] != $NULL ) // Ha az adatellenörzési mezőből visszalépési parancsot kaptunk
		{ // Kiírjuk a kitöltési mezőt (mint ha regPos=1 lenne)
			print("A <span class='star'>*</span>-gal jelölt mezők kitöltése kötelező! Húzd az egeret a kis <sup>?</sup>-re a további információkért!");
			print("<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
			<p class='formText'>Választott felhasználói név<a name='' class='feature-extra'><span class='hover'><span class='h3'>Felhasználói név</span>Ezzel fogsz a későbbiekben belépni.<br>Csak szabványos karaktereket (angol abc betűi) és számokat tartalmazhat!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='username' value='" .$_POST['username']."'></p>
			<p class='formText'>Jelszó<a name='' class='feature-extra'><span class='hover'><span class='h3'>Jelszó</span>Ezzel fogsz a későbbiekben belépni.<br>Csak szabványos karaktereket (angol abc betűi) és számokat tartalmazhat!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='password' value='" .$_POST['password']."'></p>
			<p class='formText'>E-mail cím<a name='' class='feature-extra'><span class='hover'><span class='h3'>E-mail</span>Csak <b>létező</b> e-mail címet adj meg!</span><sup>?</sup></a><span class='star'>*</span>: <input type='text' name='email' value='" .$_POST['email']."'></p>
			<p class='formText'>Valódi neved: <input type='text' name='realname' value='" .$_POST['realname']."'></p>
			<input type='hidden' name='regPos' value=2>
			<fieldset class='submit-buttons'>
				<input type='submit' name='tovabb' value='Tovább >> (adatok elküldése)'>
			</fieldset>
		</form>");
		} else {
			// Regisztráció finalizálása
			global $cfg, $sql, $mail;
		
			$regisztralt = 1; // Induljunk ki abból, hogy a regisztráció sikerülni fog...
		
			// Megnézzük, van-e már ilyen nevű user
			$adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .$_POST['username']. "'"));
			if ( $adat[0] != "" )
			{
				Hibauzenet("ERROR", "Már létezik ilyen nevű felhasználó: " .$_POST['username']); // Hibaüzeneti ablak generálása
				$regisztralt = 0; // Később ellenőrizendő változó állítása
				print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
				<input type='hidden' name='regPos' value='1'>
				<input type='hidden' name='username'>
				<input type='hidden' name='password' value='" .$_POST['password']. "'>
				<input type='hidden' name='email' value='" .$_POST['email']. "'>
				<input type='hidden' name='realname' value='" .$_POST['realname']. "'>
				<input type='hidden' name='elfogad' value='yes'>
				<input type='submit' value='<< Vissza (adatok módosítása)'>
				</form>"); // Visszatérési űrlap az adatok visszaküldésével
			}
		
			// Megnézzük, regisztrálták-e már ezt az e-mail címet
			$adat2 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE email='" .$_POST['email']. "'"));
			if ( $adat2[0] != "" )
			{
				Hibauzenet("ERROR", "Már regisztrálták ezt az e-mail címet: " .$_POST['email']); // Hibaüzeneti ablak generálása
				$regisztralt=0; // Később ellenőrizendő változó állítása
				print("<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
				<input type='hidden' name='regPos' value='1'>
				<input type='hidden' name='username' value='" .$_POST['username']. "'>
				<input type='hidden' name='password' value='" .$_POST['password']. "'>
				<input type='hidden' name='email'>
				<input type='hidden' name='realname' value='" .$_POST['realname']. "'>
				<input type='hidden' name='elfogad' value='yes'>
				<input type='submit' value='<< Vissza (adatok módosítása)'>
				</form>"); // Visszatérési űrlap az adatok visszaküldésével
			}
		
			// Ha nincs ilyen felhasználó
			if ($regisztralt == 1)
			{
				$acToken = md5($_POST['username'] . "|" . md5($_POST['password']) . "|" . Datum("normal","nagybetu","dL","H","i","s")); // Aktiválási kulcs generálása
				$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']. "user 
	(username, pwd, email, realName, activated, activateToken, regip, regsessid, regdate) VALUES ('" .$_POST['username']. "', '" .md5($_POST['password']). "', '" .$_POST['email']. "', '" .$_POST['realname']. "', '0', '" .$acToken. "', '" .$_SERVER['REMOTE_ADDR']. "', '" .session_id(). "', " .strtotime("now"). ")"); // Adatok elmentése
		
				print("A regisztráció megtörtént!<br>A további részleteket tartalmazó e-mailt elküldtük a következő e-mail címre: <b>" .$_POST['email']. "</b>.<br>Kövesd a levélben található utasításokat!"); // Értesítés
				
				$mail->SendRegistrationMail($_POST['username'], $_POST['password'], $_POST['email'], $acToken, $_POST['realname']); // Elküldjük a levelet
			//}
		}
		break;
	}
 }
 
 DoFooter();
?>