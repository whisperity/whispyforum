<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* common.php
   minden weboldalról meghívandó betöltőscript
*/

function Fejlec()
{
	// Fejléc létrehozása
	print("<div class='headerbox'>"); // Blokknyitás
	
	// KÓD IDE //
	print("</div>"); // Blokkzárás
}

function Lablec()
{
	// Lábléc létrehozása
	print("<div class='footerbox'>"); // Blokknyitás
	
	// KÓD IDE //
	print("</div>"); // Blokkzárás
}

function Inicialize ( $pagename )
{
 session_start(); // Elindítjuk a munkafolyamatot
  
 /* SZÜKSÉGES FÁJLOK BETÖLTÉSE */
 require('config.php'); // Konfigurációs állomány betöltése
	
 // Funkciótárak és osztályok betöltése
 require('includes/functions.php'); // Funkciótár
 require('mysql.php'); // MySQL kezelési osztály ($sql)
 require('user.php'); // Felhasználó és munkamenetfolyamat (session) kezelési osztály
 
 // Témafájl betöltése
 print("<link rel='stylesheet' type='text/css' href='themes/" .THEME_NAME. "/style.css'>");
 /* */
 
 /* INICIALIZÁLÁS */
 $sql->Connect(); // Csatlakozás az adatbázisszerverhez
 
 Fejlec(); // Fejléc
 
 print("<div class='leftbox'>"); // Bal odali doboz
 $user->CheckIfLoggedIn($_SESSION['username']); // Megnézzük, hogy belépett-e már a user
 //$templates->DoLeftBox(); // Bal oldali további modulok
 print("</div><div class='centerbox'>"); // Középső doboz
 //$templates->DoCenter($pagename); // Középső modulok
 print("</div><div class='rightbox'>"); // Jobb oldali doboz
 //$templates->DoRight(); // Jobboldali modulok
 print("</div>"); // Dobozzárás
 
 Lablec(); // Lábléc
}
?>