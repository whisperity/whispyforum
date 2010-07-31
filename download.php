<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* index.php
   nyítóoldal
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('download.php');
 
 if ( (DOWNLOAD_MINLVL == 1) && ( ($_SESSION['userLevel'] == 0 ) || ($_SESSION['userLevel'] == $NULL) ) )
 {
	SetTitle("Letöltések");
	if ( $_SESSION['userLevel'] == $NULL )
		$_SESSION['userLevel'] = 0;
	
	Hibauzenet("ERROR","Az letöltések nem érhetőek el","A menü használatához MINIMUM level " .DOWNLOAD_MINLVL. " jogosultság kell<br>A te jogosultságod: " .$_SESSION['userLevelTXT']. " (level " .$_SESSION['userLevel'].").");
	DoFooter();
	die();
 }
 
 if ( $_POST['action'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST action lesz az érték
	$action = $_POST['action'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['action'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$action = $_GET['action'];
	} else {
		// Sehogy nem érkezett adat
		$action = $NULL;
	}
 }
 
 switch ($action) // A beérkezett ACTION változó által nézzük, mit kell tennünk
 {
	case $NULL: // Semmi (nincs beérkező érték)
		SetTitle("Letöltések");
		print("<center><h2 class='header'>Letöltések</h2></center>\n");
		// Kategóriák listázása
		$kategoriak = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."download_categ");
		while ( $sor = mysql_fetch_assoc($kategoriak) )
		{
			print("<h3 class='download-categ'>" .$sor['title']. " (" .$sor['files']. ")</h3>\n" .$sor['descr']);
			//print("\n<br><p align='right'><small><a href='download.php?action=viewcateg&id=" .$sor['id']. "'>Kategória böngészése</a></small></p>\n");
		}
		
		$wf_debug->RegisterDLEvent("A letöltéskategóriák listázása befejeződött");
		break;
 }
 
 DoFooter();
?>