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
				print("<img src='/themes/" .$_SESSION['themeName']. "/external_href.jpg' alt='Külső hivatkozás' border='0'>");
			
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
		}
	}
	
	/* Addon telepítési adatbázis funkciók */
	function RegisterAddon($subdir, $nev, $leiras, $szerzo, $szerzoemail) // Addon regisztrálása (addons táblába felvétel)
	{
		global $cfg, $sql;
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."addons(subdir, name, descr, author, authoremail) VALUES ('" .mysql_real_escape_string($subdir). "', '" .mysql_real_escape_string($nev). "', '" .mysql_real_escape_string($leiras). "', '" .mysql_real_escape_string($szerzo). "', '" .mysql_real_escape_string($szerzoemail). "')");
	}
	
	function InstallModule($href, $side) // Modul telepítése
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."modules(name, type, side) VALUES ('" .mysql_real_escape_string($href). "', 'addonmodule', " .mysql_real_escape_string($side). ")");
	}
	
	function CreateAddonTable($subdir) // Addon konfigurációs tábla létrehozása
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " (
  `variable` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` VARCHAR(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
	}
	
	function AddCFG($subdir, $variable, $value) // Addon konfigurációs tábla kezdőérték hozzáadása
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). "(variable, value) VALUES ('" .mysql_real_escape_string($variable). "', '" .mysql_real_escape_string($value). "')");
	}
	
	/* Addon eltávolítási adatbázis funkciók */
	function RemoveAddonTable($subdir) // Addon konfigurációs tábla törlése
	{
		global $cfg, $sql;
		$sql->Lekerdezes("DROP TABLE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir));
	}
	
	function UnregisterAddon($subdir) // Addon bejegyzés törlése
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."addons WHERE subdir='" .mysql_real_escape_string($subdir). "'");
	}
	
	function RemoveModule($href) // Modul eltávolítása
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."modules WHERE name='" .mysql_real_escape_string($href). "'");
	}
	
	/* Addon működési adatbázis funkciók */
	function GetCFG($subdir, $variable) // Addon konfigurációs érték bekérése
	{
		global $cfg, $sql;
		
		$konfigtomb = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " WHERE variable='" .mysql_real_escape_string($variable). "'"));
		
		return $konfigtomb[0];
	}
	function SetCFG($subdir, $variable, $value) // Addon konfigurációs érték módosítása
	{
		global $cfg, $sql;
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " SET value='" .mysql_real_escape_string($value). "' WHERE variable='" .mysql_real_escape_string($variable). "'");
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