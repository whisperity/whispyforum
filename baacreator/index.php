<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* baacreator/index.php
   addon tömörítő (BAA-fájl létrehozó)
*/
 function DecodeSize( $bytes ) // Fájlméret dekódolása emberileg értelmezhető formátumba
 {
   $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
   for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
   return( round( $bytes, 2 ) . " " . $types[$i] );
 }
 
 if ( $_POST['action'] == "add-newfile" )
 {
	if ( $_FILES['phpfile']['name'] == "index.php" )
		die("Ilyen nevű fájl nem tölthető fel!<br><a href='index.php'>Új fájl feltöltése</a>");
	
	
	if ( file_exists($_FILES['phpfile']['name']) )
		die("Ez a fájl már feltöltésre került!<br><a href='index.php'>Új fájl feltöltése</a>");
	
	if(move_uploaded_file($_FILES['phpfile']['tmp_name'], $_FILES['phpfile']['name'])) // Feltöltött fájl mozgatása
	{
		print("Fájl " .$_FILES['phpfile']['name']. " (" .DecodeSize(filesize($_FILES['phpfile']['name'])). ") sikeresen feltöltve!");
		
		if ( !file_exists("fajlok") ) // Ha a fájlokat tartalmazó lista még nem létezik, létrehozunk egyet
		{
			file_put_contents("fajlok", $_FILES['phpfile']['name']);
		} else {
			// Ha már létezik, hozzáírjuk az aktuális fájlt
			file_put_contents("fajlok", "\r\n" .$_FILES['phpfile']['name'], FILE_APPEND);
		}
	}
	
	die("<a href='" .$_SERVER['PHP_SELF']. "'>Kezdőlap</a>");
}

if ( ( $_POST['action'] == "createBAA" ) && ( $_POST['subdir'] != $NULL ) )
{
	print("BAA fájl (kötegelt addonfájl) létrehozása folyamatban...<br>");
	
	$baafajl = $_POST['subdir'].".baa";
	file_put_contents($baafajl, "@@@@@@@@@@@@@@@@@@BATCHED ADDON ARCHIVE//////////////////\r\n@@@@@@@@@@@@@@@@@@" .$_POST['subdir']. "//////////////////");
	
	$fajlok = @file_get_contents("fajlok"); // Fájllista betöltése stringbe
	$fajlsor = explode("\r\n", $fajlok);
	
	foreach ($fajlsor as &$fajl )
	{
		$fajltartalom = @file_get_contents($fajl);
		if ( $fajltartalom != $NULL ) // Ha a fájlnak van tartalma (időnként előfordul 0 bájtos hozzáadás is), akkor archiváljuk
		{
			file_put_contents($baafajl, "\r\n@@@@@@@@@@@@@@@@@@" .$fajl. "//////////////////\r\n" .$fajltartalom, FILE_APPEND);
			print("Fájl " .$fajl. " (" .DecodeSize(@filesize($fajl)). ") archiválva<br>");
		}
		@unlink($fajl);
	}
	
	print("BAA-fájl " .$baafajl. " (" .DecodeSize(@filesize($baafajl)). ") sikeresen létrehozva!<br><a href='index.php'>Kezdőlap</a>");
	file_put_contents("lastbaa", $baafajl);
	@unlink("fajlok");
	
	die();
}

if ( $_POST['action'] == "purge" )
{
	$lastbaaname = @file_get_contents("lastbaa");
	@unlink($lastbaaname); // Utolsó BAA fájl törlése
	@unlink("lastbaa"); // Utolsó BAA-fájl nevét tároló fájl törlése
	
	$fajlok = @file_get_contents("fajlok"); // Fájllista betöltése stringbe
	$fajlsor = explode("\r\n", $fajlok);
	
	foreach ($fajlsor as &$fajl )
	{
		@unlink($fajl); // Fájl törlése
	}
	@unlink("fajlok"); // Fájlleíró törlése
}

if ( $_POST['action'] == "downloadBAA" )
{
	// Legutóbbi BAA-fájl letöltése
	$lastbaaname = @file_get_contents("lastbaa");
	if ( ( $lastbaaname == $NULL ) || !file_exists($lastbaaname) )
	{
		// Ha a legutolsó fájl neve semmi, vagy a fájl nem létezik, nem történik semmi
	} else {
		// Fájl letöltése
		ob_start();
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream, charset=utf-8');
			header('Content-Disposition: attachment; filename='.$lastbaaname);
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($lastbaaname));
		ob_end_flush();
			readfile($lastbaaname);
		exit;
	}
}

 print("<h2 class='header'><p class='header'><center>BAA kötegelt addonfájl létrehozó</center></h2></p><br>
 A segédprogram segítségével 3 egyszerű lépéssel kötegelheted addonodat BAA-fájllá. Csak használd az alábbi űrlapokat:
  <ol>
	<li>A szükséges fájlok feltöltése a szerverre.</li>
	<li>Addon nevének (subdir) megadása, BAA fájl kötegelése</li>
	<li>BAA-fájl letöltése</li>
  </ol>
  Emellett, ha valamit elrontottál, lehetőséged van a mappa tartalmának teljes törlésére is. Ne feledd, ezután újból kell kezdened a fájlok feltöltését.");
 
 print("<form enctype='multipart/form-data' action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<p class='formText'>Tallóz be egy fájlt, majd töltsd fel a szerverre<br><input name='phpfile' type='file' size='50' accept='application/octet-stream'><br>
	<input type='submit' value='Feltöltés'>
	<input type='hidden' name='action' value='add-newfile'></p>
	</form>"); // Feltöltő űrlap
 
 if ( file_exists("fajlok") ) // Baa létrehozó űrlap csak akkor, ha legalább 1 fájl fel van töltve
 {
	print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	Addon mappaneve (subdir): <input type='text' name='subdir' size='30'><br>
	<input type='hidden' name='action' value='createBAA'>
	<input type='submit' value='BAA fájl létrehozása'>
	</form>"); // BAA létrehozó űrlap
 }
 
	$lastbaaname = @file_get_contents("lastbaa");
 if ( ( $lastbaaname != $NULL ) && file_exists($lastbaaname) ) // Letöltési gomb csak akkor, ha a fájl neve ismert, és létezik is
 {
	print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<input type='hidden' name='action' value='downloadBAA'>
	<input type='submit' value='Legutoljára létrehozott BAA fájl (" .$lastbaaname. ") letöltése'>
	</form>"); // Utolsó BAA-t letöltő űrlap
 }
 if ( file_exists("fajlok") ) // Törlési űrlap csak akkor, ha legalább 1 fájl fel van töltve
 {
	print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<input type='hidden' name='action' value='purge'>
	<input type='submit' value='Összes fájl törlése'>
	</form>"); // Törlési űrlap
 }
?>