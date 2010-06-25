<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/templates.php
   oldasávban modul és menükezelő rendszer
*/

class templates // Osztálydeklaráció
{
	function DoLeft() // Bal oldali modulok létrehozása
	{
		global $cfg, $sql;
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE side='1'"); // Bal oldalra beállított modulok bekérése
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Modulok adatainak olvasása
			switch ($sor['type']) { // Típus alapú szelektálás
				case 'menu': // Ha menü
					$this->Menu($sor['id'], $sor['name']);
					break;
				
			}
		}
	}
	
	function DoRight() // Jobb oldali modulok létrehozása
	{
		global $cfg, $sql;
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE side='2'"); // Bal oldalra beállított modulok bekérése
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Modulok adatainak olvasása
			switch ($sor['type']) { // Típus alapú szelektálás
				case 'menu': // Ha menü
					$this->Menu($sor['id'], $sor['name']);
					break;
				
			}
		}
	}
	
	function Menu($id, $menuName) // Menü generálása
	{
		global $cfg, $sql;
		
		print("<div class='menubox'><span class='menutitle'>" .$menuName. "</span><br><p>"); // Menü fejléc
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuItems WHERE menuId='" .$id. "'");
		
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) {
			$http = explode('://', $sor['href']);
			
			print("<a class='menuitem' href='" .$sor['href']. "'>" .$sor['text']. "</a>");
			
			if ( $http[0] == "http" )
				print("<img src='/themes/" .THEME_NAME. "/external_href.jpg' alt='Külső hivatkozás' border='0'>");
			
			print("<br>");
		}
		
		print("</p></div><br>"); // Doboz lezárása
	}
}

 // Létrehozzuk a globális $user változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $templates;
 $templates = new templates();
?>