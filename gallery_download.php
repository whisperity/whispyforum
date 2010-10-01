<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* gallery_download.php
   letöltés vezérlés (galériából kép letöltése)
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize("download_do.php"); // Itt a DOWNLOAD_DO-hoz hasonlóan nem töltetjük be a nem betöltendő részt
 if ( $_GET['id'] == $NULL )
 {
	Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
	die();
 }
 
 $kep = mysql_fetch_assoc($sql->Lekerdezes("SELECT origfilename, filename FROM " .$cfg['tbprf']."gallery_pictures WHERE id='" .mysql_real_escape_string($_GET['id']). "'"));
 
 $fullPath = "uploads/".$kep['filename'];

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
		header("Content-Disposition: filename=\"".$kep['origfilename']."\"");
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