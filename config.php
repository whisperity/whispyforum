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
	'tbprfx' => '', // Táblanév prefixum
 );
 
 define('THEME_NAME', 'default'); // Téma neve
 
?>