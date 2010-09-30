<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* userpassrecovery.php
   elfelejtett jelszó
*/
 
 include('includes/common.php');
 Inicialize('userpassrecovery.php');
 SetTitle("Jelszóemlékeztető");
 
 if ( $_POST['mode'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST mode lesz az érték
	$pwsmode = $_POST['mode'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['mode'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$pwsmode = $_GET['mode'];
	} else {
		// Sehogy nem érkezett adat
		$pwsmode = $NULL;
	}
 }
 
 switch ($pwsmode)
 {
	case "uname": // Felhasználói név megadása
		print("Kérlek, add meg a felhasználói neved!
		<form method='GET' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Felhasználói név: <input type='text' name='username'></p>
	<input type='hidden' name='mode' value='sendmail'>
	<input type='submit' value='Emlékeztetőlevél elküldése'>
	</form>");
		break;
	case "sendmail": // Levél elküldése
		if ( $_GET['username'] != $NULL )
		{
			$uAr = mysql_fetch_assoc($sql->Lekerdezes("SELECT realName, email FROM " .$cfg['tbprf']. "user WHERE username='" .mysql_real_escape_string($_GET['username']). "'"));
			
			$ttoken = Datum("normal", "kisbetu", "dL", "H", "i", "s") ."|". $uAr['realname']. "|". $uAr['email'];
			
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET tempToken='" .md5($ttoken). "' WHERE username='" .mysql_real_escape_string($_GET['username']). "'");
			
			$mail->SendPwdRecoveryMail($_GET['username'], md5($ttoken), $uAr['realName']);
			print("A jelszóvisszaállítási folyamat elindítva.<br>A további részleteket tartalmazó e-mailt elküldtük a következő e-mail címre: <b>" .$uAr['email']. "</b>.<br>Kövesd a levélben található utasításokat!"); // Értesítés
		} else {
			Hibauzenet("CRITICAL", "Hiányzó felhasználói név!", "Kérlek add meg a felhasználói neved. Enélkül nem lehet továbblépni!");
			print("Kérlek, add meg a felhasználói neved!
		<form method='GET' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Felhasználói név: <input type='text' name='username'></p>
	<input type='hidden' name='mode' value='sendmail'>
	<input type='submit' value='Emlékeztetőlevél elküldése'>
	</form>");
		}
		
		break;
	case "recover": // Helyreállítás
			print("<form method='POST'	 action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Felhasználói név: <input type='text' name='username' value='" .$_GET['username']. "'><br>
	Aktiválókulcs (token): <input type='text' name='token' value='" .$_GET['token']. "' size='33'><br><br>
	Új jelszó: <input type='password' name='np'><br>
	Új jelszó (mégegyszer): <input type='password' name='npd'></p>
	<input type='hidden' name='mode' value='recover_do'>
	<input type='submit' value='Jelszó módosítása'>
	</form>");
		break;
	case "recover_do": // Új jelszó beírása (futtatókód)
		$adat = mysql_fetch_assoc($sql->Lekerdezes("SELECT tempToken FROM " .$cfg['tbprf']. "user WHERE username='" .mysql_real_escape_string($_POST['username']). "'"));
	
		if ( ( $_POST['username'] == $NULL ) || ( $_POST['token'] == $NULL) || ( $_POST['np'] == $NULL ) || ( $_POST['npd'] == $NULL ) )
		{
			Hibauzenet("CRITICAL", "Érvénytelen űrlap", "Nem töltöttél ki minden mezőt! Minden mezőt kötelező kitölteni!");
		} else {
			if ($_POST['token'] != $adat['tempToken']) // Kulcs ellenörzése
			{
				Hibauzenet("ERROR", "A kulcs nem megfelelő"); // Hibaüzenet generálása
			} else {
				// Jelszó újraírása
				if ( $_POST['np'] != $_POST['npd'] )
				{
					Hibauzenet("CRITICAL", "Érvénytelen jelszó", "Az új jelszóként megadott kód, és az ismétlése nem egyezik.");
				} else {
					$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET pwd='" .md5($_POST['np']). "', tempToken='' WHERE username='" .$_POST['username']. "'");
					ReturnTo("A jelszavazad sikeresen megváltozott.<br>Mostantól bejelentkezhetsz az új jelszavaddal!", "index.php", "Kezdőlap", TRUE);
				}
			}
		}
 }
 DoFooter();
?>