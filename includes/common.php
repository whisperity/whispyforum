<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/common.php
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
 require('includes/mysql.php'); // MySQL kezelési osztály ($sql)
 require('includes/user.php'); // Felhasználó és munkamenetfolyamat (session) kezelési osztály
 require('includes/sendmail.php'); // Levélküldési osztály
 require('includes/templates.php'); // Modulkezelő
 
 // Témafájl betöltése
 print("<link rel='stylesheet' type='text/css' href='themes/" .THEME_NAME. "/style.css'>");
 /* */
  
 /* INICIALIZÁLÁS */
 WriteLog("PAGE_VIEW", $_SERVER['REMOTE_ADDR']. ',' .$_SERVER['HTTP_USER_AGENT']);
 $sql->Connect(); // Csatlakozás az adatbázisszerverhez
 $user->GetUserData(); // Felhasználó adatainak frissítése
 
 if ($pagename != "admin.php") // Az admin.php-n ezeknek NEM kell megjelenniük
 {
	Fejlec(); // Fejléc
	
	print("<div class='leftbox'>"); // Bal odali doboz
	$user->CheckIfLoggedIn($_SESSION['username']); // Megnézzük, hogy belépett-e már a user
	
	$templates->DoLeft(); // Bal oldali modulok
	
	print("</div>
    <div class='centerbox'>"); // Középső doboz
	//$templates->DoCenter($pagename); // Középső modulok
 }
}

function DoFooter() // Középső rész elküldése után
{
 print("</div><div class='rightbox'>"); // Jobb oldali doboz
 
 global $templates;
 $templates->DoRight(); // Jobb oldali modulok
 
 print("</div>"); // Dobozzárás
 
 Lablec(); // Lábléc
}
?>