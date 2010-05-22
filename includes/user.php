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
 <input type='hidden' name='cmd' value='loginusr'>
 </form>");
	}
	
	function Login ( $un, $pw ) // Bejelentkeztetés
	{
		global $cfg, $sql, $session;
		$sql->Connect();
		
		$adat = mysql_fetch_array($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "user WHERE username='" .$un. "'"));
		if ( md5($pw) == $adat['pwd'])
		{
			$session->StartSession($un, $pw); // Munkamenet indítása
		} else {
			Hibauzenet("WARNING", "Nem sikerült a bejelentkezés", "A felhasználónév nem megfelelő");
		}
		
	}
	
	function Logout() // Kijelentkezés
	{
		global $cfg, $sql, $session;
		$sql->Connect();
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']. "user SET loggedin='0', cursessid='', curip='0.0.0.0' WHERE username='" .$_SESSION['username']. "' AND pwd='" .md5($_SESSION['pass']). "'");
		
		// Session kiűrítése
		$_SESSION['loggedin']=0;
		$_SESSION['username']='';
		$_SESSION['pass']='';
		
		header("Localtion: " .$_SERVER['PHP_SELF']. "");
		session_write_close();
	}
	function DoControlForm() // Felhasználói panel
	{
		print("<div class='userbox'><span class='formHeader'>Üdvözlünk, " .$_SESSION['username']. "!</span><br>
		<form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
		<input type='hidden' name='cmd' value='logoutusr'>
		<input type='submit' value='Kijelentkezés'></form>");
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

class session // Osztálydefiniálás: Munkamenetkezelés
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