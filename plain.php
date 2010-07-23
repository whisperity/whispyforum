<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* plain.php
   statikus tartalom megjelenítése
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('plain.php');
 
 if ( $_GET['id'] == $NULL )
 {
	SetTitle("Hiba!");
	Hibauzenet("ERROR", "Az ID-t kötelező megadni!");
	DoFooter();
	die();
 }
 
 $statikus = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."plain WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
 
 if ( $statikus == FALSE )
 {
	SetTitle("Nem létező statikus tartalom");
	Hibauzenet("ERROR", "A megadott azonosítószámú statikus tartalom nem létezik!");
	DoFooter();
	die();
 }
 
 SetTitle($statikus['title']);
 
 print("<center><h2 class='header'>" .$statikus['title']. "</h2></center>\n\n");
 
 /* Tartalom formázása */
 $staticContent = $statikus['content']; // Nyers
 $staticContent = EmoticonParse($staticContent); // Hangulatjelek hozzáadása BB-kódként
 $staticContent = HTMLDestroy($staticContent); // HTML kódok nélkül 
 $staticContent = BBDecode($staticContent); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
 print($staticContent);
 
 DoFooter();
?>