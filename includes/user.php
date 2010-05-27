<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* user.php
   felhasználó és munkamenetfolyamat (session) kezelési osztály
*/

class user // Definiáljuk az osztályt (felhasználók)
{
	function DoLoginForm() // Bejelentkezési űrlap létrehozása
	{
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
	<span class='formHeader'>Bejelentkezés</span>
 <p class='formText'>Felhasználói név: <input type='text' name='username'></p>
 <p class='formText'>Jelszó: <input type='password' name='pwd'></p>
 <input type='submit' value='Bejelentkezés'>
 <input type='hidden' name='cmd' value='loginusr'><br>
 <a href='registration.php'>Regisztráció</a></form><br>");
	}
	
	function Login ( $un, $pw ) // Bejelentkeztetés
	{
		global $cfg, $sql, $session;
		$sql->Connect();
		
		$adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .$un. "'"));
		
		if ( (md5($pw) == $adat['pwd']) && ($adat['activated'] ==1 ) )
		{
			$session->StartSession($un, $pw); // Munkamenet indítása
			$this->GetUserLevel();
		} else {
			Hibauzenet("WARNING", "Nem sikerült a bejelentkezés", "A felhasználónév nem megfelelő, vagy még nem aktiváltad a felhasználód.<br><a href='usractivate.php?username=" .$un. "'>Aktiválási űrlap megnyitása</a>");
		}
	}
	
	function Logout() // Kijelentkezés
	{
		global $cfg, $sql, $session;
		$sql->Connect();
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET loggedin='0', cursessid='', curip='0.0.0.0' WHERE username='" .$_SESSION['username']. "' AND pwd='" .md5($_SESSION['pass']). "'");
		
		// Session kiűrítése
		$session->Purge();
		
		header("Localtion: " .$_SERVER['PHP_SELF']. "");
		session_write_close();
	}
	
	function DoControlForm() // Felhasználói panel
	{
		print("<div class='userbox'><span class='formHeader'>Üdvözlünk, ");
		
		$this->GetUserLevel();
		$this->GetUserRealName();
		
		// Köszöntjük a felhasználót a valódi nevén (ha megadata)
		if ($_SESSION['realName'] == $NULL)
		{
			print ($_SESSION['username']);
		} else { // Ha nem, a usernevén
			print ($_SESSION['realName'] ." (". $_SESSION['username'] .")");
		}
		
		print("!</span><br>");
		print("<p class='formText'>Felhasználói szinted: " .$_SESSION['userLevelTXT']. "<br><a href='ucp.php'>Felhasználói vezérlőpult</a>");
		if ( $_SESSION['userLevel'] == 3) // Ha a felhasználó admin linket írunk az admin vezérlőpultra
			print("<br><a href='admin.php'>Adminisztrátor vezérlőpult</a>");
		
		print("</p><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
		<input type='hidden' name='cmd' value='logoutusr'>
		<input type='submit' value='Kijelentkezés'></form>");
		print("</div>");
	}
	
	function GetUserLevel() // Felhasználó szintjének megállapítása
	{
		global $cfg, $sql;
		$adat = mysql_fetch_array($sql->Lekerdezes("SELECT userLevel FROM " .$cfg['tbprf']. "user WHERE username='" .$_SESSION['username']. "' AND pwd='" .md5($_SESSION['pass']). "'")); // Bekérjük a session adatokat és IP-t
		
		$_SESSION['userLevel'] = $adat['userLevel']; // Tároljuk a felhasználó szintjét
		
		switch ($adat['userLevel']) // Beállítjuk a szöveges userLevel értéket (userLevelTXT)
		{
			case 0:
				$_SESSION['userLevelTXT'] = 'Nincs aktiválva';
				break;
			case 1:
				$_SESSION['userLevelTXT'] = 'Felhasználó';
				break;
			case 2:
				$_SESSION['userLevelTXT'] = 'Moderátor';
				break;
			case 3:
				$_SESSION['userLevelTXT'] = 'Adminisztrátor';
				break;
		}
	}
	
	function GetUserRealName() // Felhasználó valódi nevének megállapítása
	{
		global $cfg, $sql;
		$adat = mysql_fetch_array($sql->Lekerdezes("SELECT realName FROM " .$cfg['tbprf']. "user WHERE username='" .$_SESSION['username']. "' AND pwd='" .md5($_SESSION['pass']). "'")); // Bekérjük a session adatokat és IP-t
		
		$_SESSION['realName'] = $adat['realName']; // Tároljuk a felhasználó szintjét
	}
	
	function ForcedLogout() // Kényszerített kiléptetés
	{
		Hibauzenet("WARNING", "Ki lettél jelentkeztetve"); // Hibaüzenet megjelenítése
		$this->Logout; // Kiléptetjük a usert
		$this->DoLoginForm(); // Beléptető ablak
	}
	
	function CheckIfLoggedIn( $username ) // A felhasználó bejelentkezettségének ellenörzése
	{
		global $cfg, $sql, $session;
		
		if ($username != '')
		{
			$session->CheckSession(session_id(), IpCim()); // Ellenörzés
			$this->DoControlForm(); // Belépett panel létrehozása a login helyén
		} else {
			$this->DoLoginForm(); // Loginpanel létrehozása
		}
	}
}

class session // Munkamenet (session) kezelő osztály
{
	function StartSession( $username, $pass ) // Munkamenet elindítása, bejelentkeztetés
	{
		global $cfg, $sql;
		
		session_start(); // Indítás
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET lastip='" .IpCim(). "', lastsessid='" .session_id()."', loggedin='1', cursessid='" .session_id(). "', curip='" .IpCim(). "' WHERE username='" .$username. "' AND pwd='" .md5($pass). "'");
		$_SESSION['username'] = $username;
		$_SESSION['pass'] = $pass;
		
	}
	
	function CheckSession($sid, $ip) // Belépettség megjelenítése
	{
		global $cfg, $user, $sql;
		
		$adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .$_SESSION['username']. "' AND pwd='" .md5($_SESSION['pass']). "'")); // Bekérjük a session adatokat és IP-t
		
		if ( ($sid == $adat['cursessid']) && ($ip == $adat['curip']) ) // Egyezés ellenörzése (ip cím és session ID)
		{
			$_SESSION['loggedin'] = 1; // Be vagyunk jelentkeztetve
		} else {
			$user->ForcedLogout(); // Kényszerített kiléptetés
		}
	}
	
	function Purge() // Session kiürítése
	{
		$_SESSION['username'] = "";
		$_SESSION['pass'] = "";
		$_SESSION['userLevel'] = "";
		$_SESSION['userLevelTXT'] = "";
		$_SESSION['loggedin'] = 0;
		$_SESSION['realName'] = "";
		
		session_destroy();
	}
}
	
	// Létrehozzuk a globális $user változót
	// mellyel meghívhatjuk az osztály függvényeit
	global $user;
	$user = new user();
	// Létrehozzuk a globális $session változót
	// mellyel kezelhetjük a munkameneteket
	global $session;
	$session = new session();
	
	/* Ha van bejövő felhasználónév-jelszó kombó, beléptetjük a usert */
	if ( ($_GET['username'] != $NULL) && ($_GET['pwd'] != $NULL) && ($_GET['cmd'] == 'loginusr') )
		$user->Login($_GET['username'], $_GET['pwd']);
	
	if ( $_GET['cmd'] == 'logoutusr' )
		$user->Logout(); // Felhasználó kiléptetése
?>