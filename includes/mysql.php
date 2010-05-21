<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* mysql.php
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
	}
	
	function Query( $sqlQuery ) // SQL lekérdezés végrehajtása
	{
		// Query végrehajtása (vagy hibaüzenet generálása)
		@mysql_query($sqlQuery)
			or Hibauzenet("CRITICAL", "A lekérdezés nem hajtható végre", $sqlQuery, __FILE__, __LINE__);
		
		unset ( $query );
	}
	
	function Disconnect() // Lekapcsolódás a szerverről
	{
		// Zárjuk a kapcsolatot
		@mysql_close()
			or Hibauzenet("CRITICAL", "A kapcsolat nem zárható le", "", __FILE__, __LINE__);
	}
}

 // Létrehozzuk a globális $sql változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $sql;
 $sql = new mysql();
?>