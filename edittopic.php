<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* edittopic.php
   témák szerkesztése
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('edittopic.php');
 
 /* Inicializációs rész */
 $jog = 1; // Induljunk ki abból, hogy van jogunk szerkeszteni a témát
 // Adatok bekérése
 if ( $_POST['tId'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$getid = $_POST['tId'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['tId'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$getid = $_GET['tId'];
	} else {
		// Sehogy nem érkezett adat
		$getid = $NULL;
	}
 }
 // Felhasználói rang, felhasználó ellenörzése
 if ( ($_SESSION['userLevel'] == 0) || ( $_SESSION['userLevel'] == 1) )
 {
	$jog = 0; // Ha a felhasználó userszintje 0 (vendég) vagy 1 (felhasználó), nincs joga szerkeszteni
 } // egyéb esetben a felhasználó mod/admin, van joga szerkeszteni
 
 
 if ( $jog == 0 )
 {
	SetTitle("Nincs privilégium");
	Hibauzenet("ERROR", "Nincs jogod a téma szerkesztéséhez");
 } else {
	if ($_GET['cmd'] == "deletetopic")
	{
		SetTitle("Téma törlése");
		// Téma törlése
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts WHERE tId='" .mysql_real_escape_string($getid). "'"); // A téma tartalmának betöltése (postok)
		$hozzaszolas_torolve = 0; // 0 hozzászólás törölve
		while($sor = mysql_fetch_array($adat, MYSQL_ASSOC))
		{
			// Felhasználó hozzászólásszám csökkentése
			$sor2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."user WHERE id='" .$sor['uId']. "'")); // A hozzászólást beküldő felhasználó adatai
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."user SET postCount='" .($sor2['postCount']-1). "' WHERE id='" .$sor['uId']. "'"); // -1 hozzászólás a felhasználótól
			
			$hozzaszolas_torolve++; // +1 hozzászólás törölve
		}
		
		$sor3 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .$getid. "'")); // Téma adatai
		$sor4 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum WHERE id='" .$sor3['fId']. "'")); // Fórum adatai
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."forum SET topics='" .($sor4['topics']-1). "', posts='" .($sor4['posts'] - $hozzaszolas_torolve). "' WHERE id='" .$sor3['fId']. "'"); // -1 topic, - a törölt hozzászólások száma a fórumból levonva
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'"); // Téma törlése
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."posts WHERE tId='" .mysql_real_escape_string($getid). "'"); // Hozzászólások törlése
		
		print("<div class='messagebox'>A téma sikeresen törölve!<br><a href='viewtopics.php?id=" .$sor3['fId']. "'>Vissza a fórumhoz</a>");
		
		DoFooter();
		die(); // A többi kód ne fusson le
	}
	if ($_GET['cmd'] == "modifytopic")
	{
		SetTitle("Téma szerkesztése");
		// Téma módosítása
		if ( $_GET['locked'] == $NULL)
		{
			$locked = 0;
		} else if ($_GET['locked'] == 1)
		{
			$locked = 1;
		}
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."topics SET locked='" .$locked. "', type='" .$_GET['type']. "' WHERE id='" .mysql_real_escape_string($getid). "'");
		$sor5 = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'")); // Téma adatai
		print("<div class='messagebox'>A téma sikeresen szerkesztve!<br><a href='viewtopics.php?id=" .$sor5['fId']. "'>Vissza a fórumhoz</a>");
		DoFooter();
		die(); // A többi kód ne fusson le
	}
	$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."topics WHERE id='" .mysql_real_escape_string($getid). "'")); // Téma adatai
	SetTitle("Téma szerkesztése");
	print("<form method='GET' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Téma szerkesztése: " .$sor['name']. "</span><br>
		<p class='formText'>Téma típusa: 
			<input type='radio' name='type' value='1'");
			if ( $sor['type'] == 1) // Ha a téma típusa 1 (normál), akkor alapból a normál gomb kerül bejelölésre
				print(" checked ");
			print("> Normál <input type='radio' name='type' value='2'");
			if ( $sor['type'] == 2) // Ha a téma típusa 2 (közlemény), akkor alapból a közlemény gomb kerül bejelölésre
				print(" checked ");
			print("> Közlemény<br>
		Lezárt topic: <input type='radio' name='locked' value='0'");
			if ( $sor['locked'] == 0)
				print(" checked ");
			print("> Nem <input type='radio' name='locked' value='1'");
			if ( $sor['locked'] == 1)
				print(" checked ");
		print("> Igen</p>
		<input type='hidden' name='tId' value='" .$getid. "'>
		<input type='hidden' name='cmd' value='modifytopic'>
		<input type='submit' value='Téma szerkesztése'>
		</form>");
 }
 
 DoFooter();
?>