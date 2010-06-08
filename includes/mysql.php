<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/mysql.php
   mySQL kezelési osztály
*/

class mysql // Definiáljuk az osztályt
{
	function Connect() // Csatlakozás az adatbázisszerverhez
	{
		global $cfg;
		
		// Csatlakozunk a szerverhez (vagy hibaüzenet generálása)
		@mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'])
			or Hibauzenet('CRITICAL',"Nem sikerült a kapcsolódás az adatbázisszerverhez (" .$cfg['dbhost']. " -user " .$cfg['dbuser']. ")", "", __FILE__, __LINE__);
		
		// A megadott adatbázis kiválasztása (vagy hibaüzenet generálása)
		@mysql_select_db($cfg['dbname'])
			or Hibauzenet("CRITICAL", "Az adatbázis (" .$cfg['dbname']. ") nem választható ki", "", __FILE__, __LINE__);
		WriteLog("SQL_CONNECT_SELECTDB");
	}
	
	function Disconnect() // Lekapcsolódás a szerverről
	{
		// Zárjuk a kapcsolatot
		@mysql_close()
			or Hibauzenet("CRITICAL", "A kapcsolat nem zárható le", "", __FILE__, __LINE__);
		
		WriteLog("SQL_DC");
	}
	
	function Lekerdezes ( $lekerd, $tipus = 'NORMAL' ) // Lekérdezés
	{
		WriteLog("SQL", $lekerd);
		
		$eredmeny = mysql_query($lekerd)
			or Hibauzenet("CRITICAL", "A lekérdezés nem futtatható le", "Lekérdezés: <b>" .$lekerd. "</b><br>Nyers MySQL hiba: <b>" .mysql_error(). "</b>", __FILE__, __LINE__);
		
		if ( $tipus == 'INSTALL' ) // Ha telepítéskori a lekérdezés (lásd: /install/database.php), naplózunk
			file_put_contents('logs/install.log', "\r\n\r\n\r\nSQL-QUERY: " .$lekerd. "\r\n\r\n\r\n", FILE_APPEND);
		
		return $eredmeny;
	}
}

 // Létrehozzuk a globális $sql változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $sql;
 $sql = new mysql();
?>