<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* newtopic.php
   új topic hozzáadása
*/

	include('includes/common.php');
	Inicialize('viewtopics.php');
	SetTitle("Új téma");
	
	/* Fórum ID beállítása a bejövő adatok alapján */
	if ( $_GET['id'] != $NULL)
		$fId = $_GET['id'];
	
	if ( $_POST['id'] != $NULL)
		$fId = $_POST['id'];
	
	if ( $fId == $NULL )
		die(Hibauzenet("ERROR", "A megadott azonosítójú fórum nem létezik") );
	
	if ($_SESSION['loggedin'] != 1) { // Ha a felhasználó nincs bejelentkezve, nem szólhat hozzá
		Hibauzenet("ERROR", "Amíg nem jelentkezel be, nem készíthetsz új témát");
	} else {		
		$sor2 = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum WHERE id='" .$fId. "'"), MYSQL_ASSOC); // Fórum adatai
		
		if ( $_POST['submit'] != $NULL )
		{
			// Új téma hozzáadáas
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."forum SET topics='" .($sor2['topics']+1). "', posts='" .($sor2['posts']+1)."' WHERE id='" .$fId. "'"); // Fórum témaszám és postszám növelése
			
			$postSzam = mysql_num_rows($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."posts"));
			$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']. "topics(fId, name,	type, startdate, startuser, lastpostdate, lastuser, replies, opens, lpId, locked)
			VALUES('" .$fId. "', '" .$_POST['Ttitle']. "', '1', '" .time(). "', '" .$_SESSION['userID']. "', '" .time(). "', '" .$_SESSION['userID']. "', '1', '0', '" .($postSzam+1). "', '0')"); // Téma hozzáadása
			
			print("<div class='messagebox'>Az új témát létrehoztam!<br><a href='viewtopics.php?id=" .$_POST['id']. "'>Vissza a fórumhoz</a></div>"); // Visszatérési link
			DoFooter();
			die(); // A többi kód nem fut le!
		}
		
		if ( $sor2 == FALSE )
		{
			Hibauzenet("ERROR", "A megadott azonosítójú fórum nem létezik");
		} else {
			print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'><span class='formHeader'>Új téma beküldése: " .$sor2['name']. "</span>
			<p class='formText'>Téma neve: <input type='text' name='Ttitle' size='70' value='" .$_POST['Ttitle']. "'></p>"); // Bal oldali rész
			print("
			<input type='hidden' name='id' value='" .$fId. "'>
			<fieldset class='submit-buttons'>
				<input type='submit' name='submit' value='Téma létrehozása'>
			</fieldset>
			</form>"); // Hozzászólás beküldési űrlap
		}
	}

DoFooter();
?>