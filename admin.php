<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin.php
   adminisztrációs vezérlőpanel
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('admin.php');
 
 Fejlec(); // Fejléc generálása
 print("<div class='leftbox'>"); // Bal oldali doboz
 $session->CheckSession(session_id(), $_SERVER['REMOTE_ADDR']); // Ellenörzés, hogy a felhasználó be van-e jelentkezve
 $user->GetUserData(); // Felhasználó szintjének ellenörzése
 
 if ( $_SESSION['userLevel'] != 3) { // Ha a felhasználó nem admin, nem jelenítjük meg a menüt neki
  Hibauzenet("ERROR","Az admin menü nem érhető el","A menü használatához MINIMUM Adminisztrátori (level 3) jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel']." ).");
	die();
 }
 
 /* Admin menü linkek */
 
 
 print("</div>"); // Bal oldali doboz lezárása
 Lablec(); // Lábléc
?>