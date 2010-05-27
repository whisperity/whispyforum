<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* usractivate.php
   felhasználói regisztráció aktiválása
*/
 
 include('includes/common.php');
 Inicialize('usractivate.php');
 
 global $cfg, $sql, $user;
 
 if ( ($_GET['token'] == $NULL) || ($_GET['username'] == $NULL) )
 {
	// Ha nincsen megadva token, kirakunk egy űrlapot
	print("<form method='GET' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Felhasználói név: <input type='text' name='username' value='" .$_GET['username']. "'></p>
	<p class='formText'>Aktiválókulcs (token): <input type='text' name='token' value='" .$_GET['token']. "' size='33'></p>
	<input type='submit' value='Aktiválás'>
	</form>");
 } else {
	// Aktiválás
	$adat = mysql_fetch_array($sql->Lekerdezes("SELECT activateToken, activated, userLevel FROM " .$cfg['tbprf']. "user WHERE username='" .$_GET['username']. "'"));
	
	if ($_GET['token'] != $adat['activateToken']) // Aktiválókulcs ellenörzése
	{
		Hibauzenet("ERROR", "Az aktiválókulcs nem megfelelő"); // Hibaüzenet generálása
		// Űrlap újragenerálása
		print("<form method='GET' action='" .$_SERVER['PHP_SELF']. "'>
	<p class='formText'>Felhasználói név: <input type='text' name='username' value='" .$_GET['username']. "'></p>
	<p class='formText'>Aktiválókulcs (token): <input type='text' name='token' value='" .$_GET['token']. "' size='33'></p>
	<input type='submit' value='Aktiválás'>
	</form>");
	} else {
		// Megnézzük, aktivált-e már a user
		if ( $adat['activated'] == 0 )
		{
			// Aktiválunk
			$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET activated='1', userLevel='1' WHERE username='" .$_GET['username']. "'");
			print("<div class='infobox'>Az a felhasználód (" .$_GET['username']. ") mostantól aktiválva van.");
		} else {
			Hibauzenet("ERROR", "Ez a felhasználó már aktiválva van!");
		}
	}
 }
 
 DoFooter();
?>