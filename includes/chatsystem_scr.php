<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/chatsystem_scr.php
   chatrendszer kódja
*/
 global $sql, $cfg, $wf_debug;
 
 function Chat_LoadModule() // Chat modul
 {
	global $cfg, $sql, $wf_debug;
	
	$wf_debug->RegisterDLEvent("Chat ablak létrehozva");
	
	print("<div class='userbox'><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
		<span class='formHeader'>Chat</span><br>
		<table style='height: 300; width: 235; border: 1px solid #000000'>
		<tr><td valign='top'>");
	
	// Legújabb 20 üzenet bekérése és kiírása
	$sorok = $sql->Lekerdezes("SELECT id, timeepoch, uid, szoveg FROM " .$cfg['tbprf']."chat ORDER BY id DESC LIMIT 20");
	
	while ( $sor = mysql_fetch_assoc($sorok) ) {
		$felhasznalo = mysql_fetch_row($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($sor['uid']). "'"));
		
		/* Hózzászólás formázása */
		$postCBody = $sor['szoveg']; // Nyers
		$postCBody = EmoticonParse($postCBody); // Hangulatjelek hozzáadása BB-kódként
		$postCBody = HTMLDestroy($postCBody); // HTML kódok nélkül 
		$postCBody = BBDecode($postCBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
		
		print("<i>" .Datum("", "", "", "H", "i", "s", $sor['timeepoch']). "</i>&nbsp;<b><a href='profile.php?id=" .$sor['uid']. "' alt='Profil megtekintése'>" .$felhasznalo[0]. "</a></b>:&nbsp;" .$postCBody. "<br>\n");
	}
	
	print("</td></tr></table>\n<p class='formText'>
		Üzeneted:<br>
	<textarea rows='7' cols='20' name='message'");
	
	if ( $_SESSION['userID'] == $NULL )
		print(" disabled");
	
	print("></textarea>
	<br><a href='/themes/" .$_SESSION['themeName']. "/emoticons.php' onClick=\"window.open('/themes/" .$_SESSION['themeName']. "/emoticons.php', 'popupwindow', 'width=192,heigh=600,scrollbars=yes'); return false;\">Hangulatjelek</a>
			<a href='/includes/help.php?cmd=BB' onClick=\"window.open('includes/help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a><br>");
	
	if ( $_SESSION['userID'] != $NULL )
	{
		print("<input type='hidden' name='chat' value='sendmsg'>
		<input type='submit' value='Hozzászól'>");
	} else {
		print("<input type='button' value='Hozzászólás előtt kérlek jelentkezz be!' disabled>");
	}
	print("</p>
	</form>");
	
	if ( $_POST['id'] != $NULL )
 {
  // Ha POST-tal érkeznek az adatok, a POST site lesz az érték
  $getid = $_POST['id'];
 } else {
  // Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
  if ( $_GET['id'] != $NULL )
  {
  // Ha gettel érkezik, az lesz az érték
  $getid = $_GET['id'];
  } else {
  // Sehogy nem érkezett adat
  $getid = $NULL;
  }
 }
 
 if ( $_POST['action'] != $NULL )
 {
  // Ha POST-tal érkeznek az adatok, a POST site lesz az érték
  $getaction = $_POST['action'];
 } else {
  // Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
  if ( $_GET['action'] != $NULL )
  {
  // Ha gettel érkezik, az lesz az érték
  $getaction = $_GET['action'];
  } else {
  // Sehogy nem érkezett adat
  $getaction = $NULL;
  }
 }
 
	print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
		<input type='submit' value='Frissítés'>");
		if ( $getid != $NULL )
			echo "<input type='hidden' name='id' value='" .$getid. "'>";
		
		if ( $getaction != $NULL )
			echo "<input type='hidden' name='action' value='" .$getaction. "'>";
	
	print("</form>
	</div>"); // Hozzászóló űrlap
 }
 
 function Chat_LoadModuleAdmin() // Chat modul (admin menü)
 {
	global $cfg, $sql, $wf_debug;
	
	$wf_debug->RegisterDLEvent("Chat ablak létrehozva");
	
	print("<div class='userbox'>
		<table style='border: 1px solid #000000'>
		<tr><td valign='top'>");
	
	// Legújabb 20 üzenet bekérése és kiírása
	$sorok = $sql->Lekerdezes("SELECT id, timeepoch, uid, szoveg FROM " .$cfg['tbprf']."chat ORDER BY id DESC LIMIT 20");
	
	while ( $sor = mysql_fetch_assoc($sorok) ) {
		$felhasznalo = mysql_fetch_row($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string($sor['uid']). "'"));
		
		/* Hózzászólás formázása */
		$postCBody = $sor['szoveg']; // Nyers
		$postCBody = EmoticonParse($postCBody); // Hangulatjelek hozzáadása BB-kódként
		$postCBody = HTMLDestroy($postCBody); // HTML kódok nélkül 
		$postCBody = BBDecode($postCBody); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)
		
		print("<i>" .Datum("", "", "", "H", "i", "s", $sor['timeepoch']). "</i>&nbsp;<b><a href='profile.php?id=" .$sor['uid']. "' alt='Profil megtekintése'>" .$felhasznalo[0]. "</a></b>:&nbsp;" .$postCBody. "<br>\n");
	}
	
	print("</td></tr></table>\n<p class='formText'>
	</p>
	</div>"); // Hozzászóló űrlap
 }
 
 function Chat_Truncate()
 {
	global $cfg, $sql, $wf_debug;
	
	$wf_debug->RegisterDLEvent("Chat ürítés megkezdve...");

	$sorokszama = mysql_num_rows($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."chat ORDER BY id DESC"));
	$huszadikID = mysql_fetch_assoc($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."chat ORDER BY id DESC LIMIT 20,1"));
	
	if ( ( $sorokszama > 19 ) && ( $huszadikID['id'] != $NULL ) )
	{
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."chat WHERE id <= " .$huszadikID['id']);
		$wf_debug->RegisterDLEvent("Chat hátramaradó része ürítve");
	}
	
	$wf_debug->RegisterDLEvent("Chat ürítés befejezve");

 }
 
 if ( ( $_POST['chat'] == "sendmsg") && ( $_POST['message'] != $NULL ) ) // Hozzászólás beküldése
 {
	$wf_debug->RegisterDLEvent("Üzenet mentése...");
	
	$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."chat(timeepoch, uid, szoveg) VALUES ('" .time(). "', '" .$_SESSION['userID']. "', '" .mysql_real_escape_string($_POST['message']). "')");
	
	$wf_debug->RegisterDLEvent("Üzenet elmentve");
	
	Chat_Truncate(); // Ha az új hozzászólással a hozzászólások száma több lenne, mint 20, automatikusan töröljük a régieket.
 }
?>