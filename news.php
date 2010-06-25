<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* news.php
   hírek
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('news.php');
 SetTitle("Hírek");
 
 /* Hírek betöltése */
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news");
 
 /* Hírek listázása */
 while ( $sor = mysql_fetch_assoc($adat) )
 {
	$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'"));
	print("<div class='newsitem'><h2 class='header'><p class='header'>" .$sor['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $sor['postDate']). ", " .$felhasznaloadat['username']. ")</p></h2>
"); // Fejléc
	
	// Hír első három bekezdésének megjelenítése
	$bekezdesek = explode("\r\n", $sor['text']);
	$rovidszoveg = $bekezdesek[0]."<br>".$bekezdesek[1]."<br>".$bekezdesek[2];
	print($rovidszoveg . "<br><br><a href='news.php?id=" .$sor['id']. "&action=view'>Tovább >> (bővebben, kommentelés)</a></div>");
 }
 
 DoFooter(); // Lábléc
?>