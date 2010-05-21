<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* common.php
   minden weboldalról meghívandó betöltőscript
*/
 
 /* SZÜKSÉGES FÁJLOK BETÖLTÉSE */
 require('config.php'); // Konfigurációs állomány betöltése
	
 // Funkciótárak és osztályok betöltése
 require('includes/functions.php'); // Funkciótár
 require('mysql.php'); // MySQL kezelési osztály ($sql)
 require('user.php'); // Felhasználó kezelési osztály
 
 // Témafájl betöltése
 print("<link rel='stylesheet' type='text/css' href='themes/" .THEME_NAME. "/style.css'>");
 /* */
 
 /* INICIALIZÁLÁS */
 $sql->Connect(); // Csatlakozás az adatbázisszerverhez
 
 
?>