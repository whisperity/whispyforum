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
		global $cfg, $sql, $wf_debug;
		
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
		
		$wf_debug->RegisterDLEvent("Bal oldali modulok betöltve");
	}
	
	function DoRight() // Jobb oldali modulok létrehozása
	{
		global $cfg, $sql, $wf_debug;
		
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
		
		$wf_debug->RegisterDLEvent("Jobb oldali modulok betöltve");
	}
	
	function Menu($id, $menuName) // Menü generálása
	{
		global $cfg, $sql, $wf_debug;
		
		print("<div class='menubox'><span class='menutitle'>" .$menuName. "</span><br><p>"); // Menü fejléc
		$wf_debug->RegisterDLEvent("Menü " .$menuName. " létrehozása");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuItems WHERE menuId='" .$id. "' ORDER BY hOrder");
		
		while ($sor = mysql_fetch_array($adat, MYSQL_ASSOC)) {
			$http = explode('://', $sor['href']); // Kivágjuk az esetleges HTTP előtagot
			
			print("<a class='menuitem' href='" .$sor['href']. "'"); // Bevezetés (stílus, a href)
			
			if ( $http[0] == "http" ) // Ha ott a HTTP előtag, új ablakban nyílik meg a link
				print(" target='_blank'");
			
			
			if ( $sor['text'] == "[plain]")
			{
				// Ha a szöveg helyén [plain] áll, akkor 
				// lecseréljük a szöveget a statikus tartalom aktuális címére
				$href = explode("plain.php?id=", $sor['href']); // ID kivágása ($href[1] az id)
				$staticcim = mysql_fetch_assoc($sql->Lekerdezes("SELECT title FROM " .$cfg['tbprf']."plain WHERE id='" .$href[1]. "'")); // Címsor bekérése
				print(">" .$staticcim['title']. "</a>"); // Kiírás
				$wf_debug->RegisterDLEvent("Hivatkozás [plain] " .$staticcim['title']. " hozzáadva a menühöz: " .$menuName);
			} else {
				// Egyébként a szöveg az adatbázisban tárolt szöveg
				print(">" .$sor['text']. "</a>");
				$wf_debug->RegisterDLEvent("Hivatkozás " .$sor['text']. " hozzáadva a menühöz: " .$menuName);
			}
			
			if ( $http[0] == "http" ) // Ha ott a HTTP előtag, kiteszünk egy képet róla
				print("<img src='/themes/" .$_SESSION['themeName']. "/external_href.jpg' alt='Külső hivatkozás' border='0'>");
			
			print("<br>");
		}
		
		print("</p></div><br>"); // Doboz lezárása
	}
	
	function LoadAddonModule($id, $name) // Addonmodul betöltése
	{
		global $cfg, $sql, $wf_debug;
		
		/* Addon nevének megállapítása ($name mindig /addonsubdir [/almappa/...] /fájl.php) */
		$pereknelkul = explode('/', $name);
		$addonneve = $pereknelkul[0];
		/* Addon telepítettség ellenörzése */
		$addon = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons WHERE subdir='" .$addonneve. "'"));
		
		if ( $addon == FALSE ) // Ha nem található telepítve ez az addon
		{
			Hibauzenet("ERROR", "Nem telepített addon modulja kíván betöltődni", "A <i>" .$addonneve. "</i> addon egyik modulja (" .$name. ") lenne ide betöltve, ám ez az addon nincs telepítve!");
			$wf_debug->RegisterDLEvent("Addon modul " .$name. " nem tölthető be, mivel a " .$addonneve. " addon nincs telepítve");
		} else { // Ha az addon létezik, betöltjük a modul kódját
			include("addons/" .$name);
			$wf_debug->RegisterDLEvent("Addon modul " .$name. " betöltve");
		}
	}
}

class addons // Addonkezelő osztály
{
	function LoadAddons() // Addonok betöltése
	{
		global $cfg, $sql, $wf_debug;
		// Az addonok betöltéséhez használjuk az addonokhoz mellékelt INCLUDES.PHP listafájlt
		// Ez néhány esetben hiányozhat, ezért hiánya esetén nem jelenítünk meg hibaüzenetet
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."addons");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			@include_once("addons/" .$sor['subdir']. "/includes.php");
			$wf_debug->RegisterDLEvent("Addon " .$sor['subdir']. " betöltve");
		}
		
		$wf_debug->RegisterDLEvent("Az addonok betöltése befejeződött");
	}
	
	/* Addon telepítési adatbázis funkciók */
	function RegisterAddon($subdir, $nev, $leiras, $szerzo, $szerzoemail) // Addon regisztrálása (addons táblába felvétel)
	{
		global $cfg, $sql, $wf_debug;
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."addons(subdir, name, descr, author, authoremail) VALUES ('" .mysql_real_escape_string($subdir). "', '" .mysql_real_escape_string($nev). "', '" .mysql_real_escape_string($leiras). "', '" .mysql_real_escape_string($szerzo). "', '" .mysql_real_escape_string($szerzoemail). "')");
		
		$wf_debug->RegisterDLEvent("Addon " .$subdir. " regisztrálva");
	}
	
	function InstallModule($href, $side) // Modul telepítése
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."modules(name, type, side) VALUES ('" .mysql_real_escape_string($href). "', 'addonmodule', " .mysql_real_escape_string($side). ")");
		
		$wf_debug->RegisterDLEvent("Addon-modul " .$href. " hozzáadva");
	}
	
	function CreateAddonTable($subdir) // Addon konfigurációs tábla létrehozása
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("CREATE TABLE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " (
  `variable` VARCHAR(64) COLLATE utf8_unicode_ci NOT NULL,
  `value` VARCHAR(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
	
		$wf_debug->RegisterDLEvent("Addon adattábla " .$cfg['tbprf']. "addonsettings_"  .$subdir. " létrehzova");
	}
	
	function AddCFG($subdir, $variable, $value) // Addon konfigurációs tábla kezdőérték hozzáadása
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). "(variable, value) VALUES ('" .mysql_real_escape_string($variable). "', '" .mysql_real_escape_string($value). "')");
		
		$wf_debug->RegisterDLEvent("Konfigurációs érték " .$variable. " a(z) " .$value. " kezdőértékkel hozzáadva az addon konfigurációs táblához: " .$cfg['tbprf']. "addonsettings_" .$subdir);
	}
	
	/* Addon eltávolítási adatbázis funkciók */
	function RemoveAddonTable($subdir) // Addon konfigurációs tábla törlése
	{
		global $cfg, $sql, $wf_debug;
		$sql->Lekerdezes("DROP TABLE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir));
		$wf_debug->RegisterDLEvent("Addon konfigurációs tábla " .$cfg['tbprf']. "addonsettings_" .$subdir. " eltávolítva");
	}
	
	function UnregisterAddon($subdir) // Addon bejegyzés törlése
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."addons WHERE subdir='" .mysql_real_escape_string($subdir). "'");
		$wf_debug->RegisterDLEvent("Addon " .$subdir. " törölve");
	}
	
	function RemoveModule($href) // Modul eltávolítása
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."modules WHERE name='" .mysql_real_escape_string($href). "'");
		$wf_debug->RegisterDLEvent("Addon-modul " .$href. " eltávolítva");
	}
	
	/* Addon működési adatbázis funkciók */
	function GetCFG($subdir, $variable) // Addon konfigurációs érték bekérése
	{
		global $cfg, $sql, $wf_debug;
		
		$konfigtomb = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " WHERE variable='" .mysql_real_escape_string($variable). "'"));
		
		$wf_debug->RegisterDLEvent("Az addon " .$subdir. " lekérte a " .$variable. " változót a konfigurációs táblájából.<br>Visszatért érték: " .$konfigtomb[0]);
		
		return $konfigtomb[0];
	}
	function SetCFG($subdir, $variable, $value) // Addon konfigurációs érték módosítása
	{
		global $cfg, $sql, $wf_debug;
		
		$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."addonsettings_" .mysql_real_escape_string($subdir). " SET value='" .mysql_real_escape_string($value). "' WHERE variable='" .mysql_real_escape_string($variable). "'");
		$wf_debug->RegisterDLEvent("Az addon " .$subdir. " a(z) " .$variable. " konfigurációs adat értékét módosította:" .$value);
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