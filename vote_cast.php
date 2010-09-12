<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* vote_cast.php
   szavazás elbírálása (átjátszólap)
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('vote_cast.php');
 SetTitle("Szavazás leadása");
 
 if ( ($_POST['pollop_id'] == $NULL) || ($_POST['pollid'] == $NULL) )
 {
	Hibauzenet(CRITICAL, "Érvénytelen szavazat!", "Valószínűleg helytelenül adtad le a szavazati űrlapot, kérlek szavazz újra!");
 }
 
 if ( ($_POST['pollop_id'] != "eredmeny") && ($_POST['pollop_id'] != $NULL) && ($_POST['pollid'] != $NULL) )
 {
	PS_RegisterVote($_POST['pollid'], $_POST['pollop_id']); // Szavazat beküldése
 }
 
 if ( ($_POST['pollop_id'] == "eredmeny") && ($_POST['pollid'] != $NULL) )
 {
	if ( $_POST['adminmenu'] == "yes" )
	{
		print("<a href='admin.php?site=polls'>Vissza az admin menübe</a>");
	}
	PS_GenerateResults($_POST['pollid']); // Eredmények kiírása
 }
 
 DoFooter();
?>