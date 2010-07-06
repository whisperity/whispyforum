<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* profile.php
   profil megjelenítése
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('profile.php');
 
 if ( $_GET['id'] == $NULL ) {
	Hibauzenet("CRITICAL", "Érvénytelen paraméterek", "A kívánt felhasználó ID-jét kötelező megadni!");
	DoFooter();
	die();
 }
 
 if ( $_SESSION['userLevel'] == 0 )
 {
	Hibauzenet("BAN");
	DoFooter();
	die();
 }
 
 DoFooter();
?>