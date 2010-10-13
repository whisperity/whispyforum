<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* index.php
   nyítóoldal
*/
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('index.php');
 SetTitle("Kezdőlap");
 
 Print("<p class='formText'></p>");
 
 print("<h2 class='header'>Friss hírek</h2>");
 
 /* Hírek betöltése */
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news ORDER BY id DESC LIMIT 5");
 
		/* Hírek listázása */
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'"));
			print("<div class='newsitem'><h2 class='header'><p class='header'>" .$sor['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $sor['postDate']). ", <a href='profile.php?id=" .$felhasznaloadat['id']. "'>" .$felhasznaloadat['username']. "</a>)</p></h2>
"); // Fejléc
			
			// Hír első három bekezdésének megjelenítése
			$bekezdesek = explode("\r\n", $sor['text']);
			$rovidszoveg = $bekezdesek[0]."\n".$bekezdesek[1]."\n".$bekezdesek[2];
			/* Hír formázása */
			$hirBody = $rovidszoveg; // Nyers
			$hirBody = EmoticonParse($hirBody); // Hangulatjelek hozzáadása BB-kódként
			$hirBody = HTMLDestroy($hirBody); // HTML kódok nélkül 
			$hirBody = BBDecode($hirBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
			print($hirBody . "<br><br><a href='news.php?id=" .$sor['id']. "&action=view'>Tovább >> (bővebben");
			if ( $sor['commentable'] == 1) // Ha a hír kommentelhető, a bővebben linkhez odaillesztjük a kommentelés szót
				print(", kommentelés");
				
			print(")</a></div>");
		}
 
 DoFooter();
?>