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
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE side='1' ORDER BY hOrder"); // Bal oldalra beállított modulok bekérése
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Modulok adatainak olvasása
			switch ($sor['type']) { // Típus alapú szelektálás
				case 'menu': // Ha menü
					$this->Menu($sor['id'], $sor['name']);
					break;
				case 'addonmodule': // Ha egy addon egy modulja
					$this->LoadAddonModule($sor['id'], $sor['name']);
					break;
			}
		}
	}
	
	function DoRight() // Jobb oldali modulok létrehozása
	{
		global $cfg, $sql;
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE side='2' ORDER BY hOrder"); // Bal oldalra beállított modulok bekérése
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) { // Modulok adatainak olvasása
			switch ($sor['type']) { // Típus alapú szelektálás
				case 'menu': // Ha menü
					$this->Menu($sor['id'], $sor['name']);
					break;
				case 'addonmodule': // Ha egy addon egy modulja
					$this->LoadAddonModule($sor['id'], $sor['name']);
					break;
			}
		}
	}
	
	function Menu($id, $menuName) // Menü generálása
	{
		global $cfg, $sql;
		
		print("<div class='menubox'><span class='menutitle'>" .$menuName. "</span><br><p>"); // Menü fejléc
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuItems WHERE menuId='" .$id. "' ORDER BY hOrder");
		
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) {
			$http = explode('://', $sor['href']);
			
			print("<a class='menuitem' href='" .$sor['href']. "'");
			
			if ( $http[0] == "http" )
				print(" target='_blank'");
			
			print(">" .$sor['text']. "</a>");
			
			if ( $http[0] == "http" )
				print("<img src='/themes/" .THEME_NAME. "/external_href.jpg' alt='Külső hivatkozás' border='0'>");
			
			print("<br>");
		}
		
		print("</p></div><br>"); // Doboz lezárása
	}
	
	function LoadAddonModule($id, $name) // Addonmodul betöltése
	{
		global $cfg, $sql;
		
		/* Addon nevének megállapítása ($name mindig /addonsubdir [/almappa/...] /fájl.php) */
		$pereknelkul = explode('/', $name);
		$addonneve = $pereknelkul[0];
		/* Addon telepítettség ellenörzése */
		$addon = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE subdir='" .$addonneve. "'"));
		
		if ( $addon == FALSE ) // Ha nem található telepítve ez az addon
		{
			Hibauzenet("ERROR", "Nem telepített addon modulja kíván betöltődni", "A <i>" .$addonneve. "</i> addon egyik modulja (" .$name. ") lenne ide betöltve, ám ez az addon nincs telepítve!");
		} else { // Ha az addon létezik, betöltjük a modul kódját
			include("addons/" .$name);
		}
	}
}

class addons // Addonkezelő osztály
{
	function LoadAddons() // Addonok betöltése
	{
		global $cfg, $sql;
		// Az addonok betöltéséhez használjuk az addonokhoz mellékelt INCLUDES.PHP listafájlt
		// Ez néhány esetben hiányozhat, ezért hiánya esetén nem jelenítünk meg hibaüzenetet
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			@include_once("addons/" .$sor['subdir']. "/includes.php");
			@include_once("addons/" .$sor['subdir']. "/settings.cfg");
		}
	}
}

 // Létrehozzuk a globális $templates változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $templates;
 $templates = new templates();
 
 // Létrehozzuk a globális $addons változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $addons;
 $addons = new addons();
?>