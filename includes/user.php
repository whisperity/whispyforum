<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* user.php
   felhasználókezelési osztály
*/

class user // Definiáljuk az osztályt
{
	function DoLoginForm() // Bejelentkezési űrlap létrehozása
	{
		print("<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
	<span class='formHeader'>Bejelentkezés</span>
 <p class='formText'>Felhasználói név: <input type='text' name='username'></p>
 <p class='formText'>Jelszó: <input type='password' name='pwd'></p>
 <input type='submit' value='Bejelentkezés'>
 </form>");
	}
	
	function Login ( $un, $pw ) // Bejelentkeztetés
	{
		
	}
}
	
	// Létrehozzuk a globális $user változót
	// mellyel meghívhatjuk az osztály függvényeit
	global $user;
	$user = new user();
	
	/* Ha van bejövő felhasználónév-jelszó kombó, beléptetjük a usert */
	if ( ($_POST['username'] != $NULL) && ($_POST['pwd'] != $NULL) )
		$user->Login($_POST['username'], $_POST['pwd']);
	

 
?>