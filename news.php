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
 
 switch ($_GET['action']) // A beérkező ACTION parancs alapján nézzük, mit csináljon a script
 {
	// Ha a beérkező parancs üres, vagy nincs beérkező parancs
	case $NULL:
	case "":
		// Kislisttázuk a híreket, mindegyiket, azonban mindig csak az első három bekezdést
		
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
		
		break;
	case "view": // Ha VIEW parancsot kapunk 
		// Szükséges bejövő paraméter az ID, mely a megtekiteni kívánt hír azonosítóját tartalmazza
		
		if ( ($_GET['id'] == $NULL ) || ($_GET['id'] == "") )
			Hibauzenet("CRITICAL", "A hír azonosítóját kötelező megadni");
		
		// Bekérjük az aktuális hír adatait (ezt rögtön tömbbé is tömörítjük)
		$hir = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."news WHERE id='" .$_GET['id']. "'"));
		
		// Ha nem létezik ilyen hír, szintén hibaüzenetet generálunk
		if ( $hir == FALSE )
			Hibauzenet("CRITICAL", "A megadott azonosítószámú hír nem létezik");
		
		// Felhasználó adatai
		$felhasznaloadat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$hir['uId']. "'"));
		
		/* Hír formázása */
		$hirBody = $hir['text']; // Nyers
		$hirBody = EmoticonParse($hirBody); // Hangulatjelek hozzáadása BB-kódként
		$hirBody = HTMLDestroy($hirBody); // HTML kódok nélkül 
		$hirBody = BBDecode($hirBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
		
		print("<div class='newsitem'><h2 class='header'><p class='header'>" .$hir['title']. " (" .Datum("normal","kisbetu","dL","H","i","s", $hir['postDate']). ", " .$felhasznaloadat['username']. ")</p></h2><br>" .$hir['text']. "</div><br>"); // Hír szövege
		break;
 }
 
 DoFooter(); // Lábléc
?>