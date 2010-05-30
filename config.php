<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* config.php
   konfigurációs állomány
*/
 
 global $cfg; // Globális változó a konfigurációhoz
 
 $cfg = array(
	/* Adatbázis adatok */
	'dbhost' => '', // Adatbázisszerver elérhetősége
	'dbuser' => '', // Belépési név
	'dbpass' => '', // Jelszó
	'dbname' => '', // Adatbázis neve
	'tbprf' => '', // Táblanév prefixum
	
	/* Levélküldési beállítások */
	'SMTP' => "", // SMTP szerver neve (e-mailek küldéséhez)
	'smtp_port' => 25, // SMTP port szám (alapértelmezett: 25)
	'sendmail_from' => '', // A portál által küldött e-mailek feladó címe
	'sendmail_html' => 1, // HTML-stílusú levél küldése (1-igen, 0-nem (szöveges levél küldése))
	
	/* Weboldal adatai */
	'phost' => '', // Webcím (domain)
	'pname' => '', // Weboldalnév
	
	/* Webmester */
	'webmaster' => '', // Webmester neve
	'webmaster_email' => '', // Webmester e-mail címe
 );
 
 define('THEME_NAME', 'default'); // Téma neve
 define('ALLOW_REGISTRATION', 0); // Regisztráció engedélyezése (0 - nem, 1 - igen)

?>