<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includessendmail.php
   Levélküldési osztály
*/

 // Beállítjuk a levélküldéshez szükséges adatokat (config.php fájlból)

 global $cfg;
 ini_set('SMTP', $cfg['SMTP']); // SMTP szerver
 ini_set('smtp_port', $cfg['smtp_port']); // SMTP port
 ini_set('sendmail_from', $cfg['sendmail_from']); // Feladó e-mail címe

class sendmail // Definiáljuk az osztályt
{
	function SendRegistrationMail( $name, $password, $email, $token, $realname = '' ) // Regisztrációs értesítési levél elküldése
	{   
		global $cfg;
		if ( $cfg['sendmail_html'] == 1)
		{
			// HTTP üzenet küldése
			$message = "
<html>
<head>
  <title>Regisztráció sikeres - " .$cfg['pname']. "</title>
</head><bod>"; // Bevezetés
			if( $realname != '')
			{
				// Ha a felhasználó megadta a valódi nevét
				$message .= "<h3>Kedves " .$realname. " (" .$name. ")</h3>"; // Valódi név (usernév) formájában köszöntjük
			} else {
				$message .="<h3>Kedves " .$name. "</h3>"; // Usernevén köszöntjük
			}
			
			$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n"; // Levél fejlécek (mime-típus, html-levél)
			$headers .= 'Reply-To: ' .$cfg['webmaster_email'] . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Website-domain: ' .$cfg['phost']; // Válaszcím, levélküldő típusa, weboldal URL
			
			$activateLink = "http://" .$cfg['phost']. "/useractivate.php?token=" .$token. "&username=" .$name;
			$acLinkNoToken = "http://" .$cfg['phost']. "/useractivate.php";
			
			$message .= "A regisztrációd a weboldalon (" .$cfg['pname']. ") sikeres volt.<br>A belépési adataid a következőek<br><ul><li>Felhasználónév: " .$name. "</li>";
			$message .= "<li>Jelszó: " .$password. "</li></ul>Belépés előtt azonban aktiválni kell a fiókod, ezt megteheted a következő linkre kattintva: ";
			$message .= "<a href='" .$activateLink ."'>" .$activateLink ."</a><br>";
			$message .= "Ha a link nem működne, nyisd meg az aktiválási lapot (<a href='" .$acLinkNoToken. "'>" .$acLinkNoToken. "</a>) a böngésződben, majd a megjelenő lapon írd be a felhasználóneved és az aktiválási kulcsod: <b>" .$token. "</b>";
			$message .= "<br><br>Jó szórakozást és eredményes portálhasználatot kívánunk, a fejlesztők, és " .$cfg['webmaster']. ", webmester";
		} else {
			// Szöveges üzenet küldése
			$headers = 'Reply-To: ' .$cfg['webmaster_email'] . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Website-domain: ' .$cfg['phost']; // Válaszcím, levélküldő típusa, weboldal URL
			$message .= "A regisztrációd a weboldalon (" .$cfg['pname']. ") sikeres volt.\r\n\r\nA belépési adataid a következőek\r\n* Felhasználónév: " .$name;
			$message .= "\r\n* Jelszó: " .$password. "\r\n\r\nBelépés előtt azonban aktiválni kell a fiókod, ezt megteheted ha megnyitod böngésződben";
			$message .= "az aktiválási lapot " .$acLinkNoToken. "lapon beírod a felhasználóneved és az aktiválási kulcsod: " .$token. "";
			$message .= "\r\n\r\nJó szórakozást és eredményes portálhasználatot kívánunk, a fejlesztők, és " .$cfg['webmaster']. ", webmester";
		}
		
		mail($email, "Regisztrációs értesítés", $message, $headers);
	}
}

 // Létrehozzuk a globális $mail változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $mail;
 $mail = new sendmail();
?>