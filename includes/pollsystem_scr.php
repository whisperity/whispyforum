<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/pollsystem_scr.php
   szavazásrendszer
*/
 
 function PS_LoadModule() // Szavazási modul létrehozása
 {
	global $cfg, $sql, $wf_debug;
	
	$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE type=1 LIMIT 1")); // Aktív szavazás bekérése
	
	if ( $szavazas != FALSE )
	{
	
	if ( PS_CheckUserVoteOnPoll($szavazas['id']) == 1 )
	{
		PS_GenerateResults($szavazas['id']);
	} else {
	
	$lehetosegek = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."poll_opinions WHERE pollid='" .mysql_real_escape_string($szavazas['id']). "'"); // A szavazáshoz tartozó lehetőségek bekérése
	
	$wf_debug->RegisterDLEvent($szavazas['name']." szavazás adatai és lehetőségei beolvasva, doboz építése");
	
	print("<div class='userbox'><form action='vote_cast.php' method='POST'><span class='formHeader'>" .$szavazas['title']. "</span>
	<p class='formText'>"); // Űrlap fejléc
	
	
	while ($lehetoseg = mysql_fetch_assoc($lehetosegek)) {
		print("<input type='radio' name='pollop_id' value='" .$lehetoseg['opinionid']. "'");
		
		if ( $_SESSION['userID'] == $NULL )
		print(" disabled");
		
		print("> " .$lehetoseg['opinion']. "<br>");
		$wf_debug->RegisterDLEvent($lehetoseg['opinion']." (id: " .$lehetoseg['opinionid']. ") szavazat hozzáadva a szavazáshoz: " .$szavazas['name']);
	}
	
	print("<br><input type='radio' name='pollop_id' value='eredmeny' checked");
	
	if ( $_SESSION['userID'] == $NULL )
		print(" disabled");
	
	print("> Eredmények megtekintése</p>");
	
	if ( $_SESSION['userID'] != $NULL )
	{
		print("<input type='submit' value='Szavazok'>");
	} else {
		print("<input type='submit' value='Nem tudsz szavazni, amíg nem jelentkezel be!' disabled>");
	}
	
	print("<input type='hidden' name='pollid' value='" .$szavazas['id']. "'>
	</form></div>");
	}
	}
 }
 
 function PS_RegisterVote($pollid, $opinionid) // Szavazat elküldése (regisztrálása)
 {
	global $cfg, $sql, $wf_debug;
	if ( PS_CheckUserVoteOnPoll($pollid) == 1 )
	{
		Hibauzenet("WARNING", "Te már szavaztál erre a szavazásra egyszer!", "Egy szavazásra csak egyszer szavazhatsz");
	} else {
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."votes_cast(userid, pollid, opinionid) VALUES 
			(" .$_SESSION['userID']. ", " .mysql_real_escape_string($pollid). ", " .mysql_real_escape_string($opinionid). ")");
		
		$wf_debug->RegisterDLEvent("Szavazat regisztrálása");
		
		print("A szavazatod lementésre került! Köszönjük, hogy szavaztál!");
	}
 }
 
 function PS_GenerateResults($pollid) // Eredmények kiírása
 {
	global $cfg, $sql, $wf_debug;
	
	$wf_debug->RegisterDLEvent("Eredmények generálása");
	
	$szavazas = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."polls WHERE id='" .mysql_real_escape_string($pollid). "'")); // Szavazás adatainak bekérése
	
	print("<div class='menubox'><span class='menutitle'>Eredmények: " .$szavazas['title']."</span><br>");
	print("<ul>");
	
	$lehetosegszamok = array();
	
	$wf_debug->RegisterDLEvent("Lehetőségek, szavazatok számának megszámolása");
	
	for ($i = 1; $i <= $szavazas['opcount']; $i++) // Egyesével megnézzük a lehetőségeket
	{
		$lehetoseg = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."poll_opinions WHERE pollid='" .mysql_real_escape_string($pollid). "' AND opinionid='" .$i. "'")); // Lehetőség adatainak bekérése
		
		$szavazatszam = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."votes_cast WHERE pollid='" .mysql_real_escape_string($pollid). "' AND opinionid='" .mysql_real_escape_string($lehetoseg['opinionid']). "'"); // Szavazatok bekérése
		
		$lehetosegszamok[$lehetoseg['opinionid']] = array('text' => $lehetoseg['opinion'], 'count' => 0); // Szöveg beállítása
		
		while ($szavazat = mysql_fetch_assoc($szavazatszam)) { // Megszámoljuk a szavazatokat
			$lehetosegszamok[$lehetoseg['opinionid']]['count']++;
		}
		
		print("<li>" .$lehetosegszamok[$lehetoseg['opinionid']]['text']. ": " .$lehetosegszamok[$lehetoseg['opinionid']]['count']. "</li>\n");
	}
	
	print("</ul>");
 }
 
 function PS_CheckUserVoteOnPoll($pollid) // Megnézzük, hogy a felhasználó szavazott-e már
 {
	global $cfg, $sql, $wf_debug;
	
	$felhasznaloszavazat = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."votes_cast WHERE userid='" .mysql_real_escape_string($_SESSION['userID']). "' AND pollid='" .mysql_real_escape_string($pollid). "'"));
	
	$wf_debug->RegisterDLEvent("Felhasználó szavazata a(z) " .$pollid. " azonosítójú szavazáson ellenörzése");
	
	if ( $felhasznaloszavazat != FALSE ) {
		$wf_debug->RegisterDLEvent("A felhasználó már szavazott!");
		return 1; // Ha szavazott, 1-t küldünk vissza, így a PS_LoadModule az eredményeket tölti be
	}
 }
?>