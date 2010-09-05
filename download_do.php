<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* download_do.php
   letöltés vezérlés (letöltendő fájl elküldése)
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize("download_do.php");
 if ( $_GET['id'] == $NULL )
 {
	Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
	die();
 }
 $letoltes = mysql_fetch_assoc($sql->Lekerdezes("SELECT href, download_count FROM " .$cfg['tbprf']."downloads WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
 $sql->Lekerdezes("UPDATE " .$cfg['tbprf']."downloads SET download_count=" .($letoltes['download_count']+1). " WHERE id='" .mysql_real_escape_string($_GET['id']). "'");

 $fullPath = "uploads/".md5($letoltes['href']);

 if ($fd = fopen ($fullPath, "r")) {
 $fsize = filesize($fullPath);
 $path_parts = pathinfo($fullPath);
 $ext = strtolower($path_parts["extension"]);
 switch ($ext) {
	case "pdf":
		header("Content-type: application/pdf"); // add here more headers for diff. extensions
		header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a download
		break;
	default;
		header("Content-type: application/octet-stream");
		header("Content-Disposition: filename=\"".$letoltes['href']."\"");
 }
 
 header("Content-length: $fsize");
 header("Cache-control: private"); //use this to open files directly
 while(!feof($fd)) {
	$buffer = fread($fd, 2048);
	echo $buffer;
 }
 }
 
 fclose ($fd);
?>