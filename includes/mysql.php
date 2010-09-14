<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/mysql.php
   mySQL kezelési osztály
*/
class mysql // Definiáljuk az osztályt
{
	var $querys = 0; // Lekérdezések száma
	var $queryArray = array(); // Lekérdezésnapló
	function Connect() // Csatlakozás az adatbázisszerverhez
	{
		global $cfg, $wf_debug;
		// Csatlakozunk a szerverhez (vagy hibaüzenet generálása)
		$kapcsolat = @mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']);
		
		if (!$kapcsolat)
		{
			Hibauzenet('CRITICAL',"Nem sikerült a kapcsolódás az adatbázisszerverhez (" .$cfg['dbhost']. " -user " .$cfg['dbuser']. ")", "", __FILE__, __LINE__);
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "SELECT DB " .$cfg['dbname'], "completed" => 0, "mysql_error" => mysql_error());
		} else {
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "CONNECT " .$cfg['dbhost']. " USER " .$cfg['dbuser']. " -PASSWORD " .$cfg['dbpass'], "completed" => 1);
			$wf_debug->RegisterDLEvent("Adatbáziskapcsolódás megtörtént");
		}
		
		// A megadott adatbázis kiválasztása (vagy hibaüzenet generálása)
		$kivalDB = @mysql_select_db($cfg['dbname']);
		
		if (!$kivalDB)
		{
			Hibauzenet("CRITICAL", "Az adatbázis (" .$cfg['dbname']. ") nem választható ki", "", __FILE__, __LINE__);
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "SELECT DB " .$cfg['dbname'], "completed" => 0, "mysql_error" => mysql_error());
		} else {
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "SELECT DB " .$cfg['dbname'], "completed" => 1);
			$wf_debug->RegisterDLEvent("Adatbázis kiválasztva");
		}
	}
	
	function Disconnect() // Lekapcsolódás a szerverről
	{
		global $wf_debug;
		// Zárjuk a kapcsolatot
		$discon = @mysql_close();
		
		if (!$discon)
		{
			Hibauzenet("CRITICAL", "A kapcsolat nem zárható le", "Valószínűleg nincs megnyitott kapcsolat", __FILE__, __LINE__);
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "DISCONNECT", "completed" => 0, "mysql_error" => mysql_error());
		} else {
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => "DISCONNECT", "completed" => 1);
			$wf_debug->RegisterDLEvent("Szétkapcsolás az adatbázisról");
		}
	}
	
	function Lekerdezes ( $lekerd, $tipus = 'NORMAL' ) // Lekérdezés
	{
		global $wf_debug;
		
		$eredmeny = @mysql_query($lekerd);
		
		if (!$eredmeny)
		{
			Hibauzenet("CRITICAL", "A lekérdezés nem futtatható le", "Lekérdezés: <b>" .$lekerd. "</b><br>Nyers MySQL hiba: <b>" .mysql_error(). "</b>", __FILE__, __LINE__);
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => $lekerd, "completed" => 0, "mysql_error" => mysql_error());
		} else {
			$this->querys++;
			$this->queryArray[$this->querys] = array("id" => $this->querys, "time" => $wf_debug->TimeGet(), "query" => $lekerd, "completed" => 1);
			$wf_debug->RegisterDLEvent("SQL lekérdezés <a href='#sql" .$this->querys. "'><b>ID " .$this->querys. "</b></a> lefuttatva");
		}
		
		return $eredmeny;
	}
}

 // Létrehozzuk a globális $sql változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $sql;
 $sql = new mysql();
?>