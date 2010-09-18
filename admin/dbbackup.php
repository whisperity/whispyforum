<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/dbbackup.php
   adatbázis biztonsági mentés, helyes SQL syntaxú fájl létrehozása
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Adatbázis biztonsági mentése</h2></center>
<?php
global $fajlnev;
$md5h = md5("sqlbackup_" . "_" .date(M). "." . date(d). "_" . date(H). "." . date(i). "." . date(s) . ".sql");
$fajlnev = "sqlbackup_" .substr($md5h, 0, 8). "-" . substr($md5h, 24, 8). "_" .date(M). "." . date(d). "_" . date(H). "." . date(i). "." . date(s) . ".sql";

function BackupTable($table)
{
	global $cfg, $sql, $wf_debug; // Osztályok
	global $fajlnev; // Fájlnév
	
	file_put_contents($fajlnev, "\n\n# Tabla adatok mentese: " .$table, FILE_APPEND);
	file_put_contents($fajlnev, "\nTRUNCATE TABLE " .$table. ";", FILE_APPEND);
	
	$wf_debug->RegisterDLEvent("Tábla biztonsági mentése: " .$table);
	
	$resultC = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf'].$table); // Sorok lekérdezése (előbb szükséges)
	if ( mysql_fetch_row($resultC) == FALSE )
	{
		file_put_contents($fajlnev, "\n# A tabla ures", FILE_APPEND);
		$wf_debug->RegisterDLEvent("A tábla üres");
		// Amennyiben nincs megjeleníthető sor (értsd: a tábla üres)
		return 0; // Nem futtatjuk tovább
	}
	
	// Oszlopok
	file_put_contents($fajlnev, "\nINSERT INTO " .$cfg['tpbrf'].$table. "(", FILE_APPEND); // Bevezetés
	$wf_debug->RegisterDLEvent("Oszlopok létrehozása...");
	$result = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf'].$table); // Lekérdezés
	
	$i = 0;
	while ($i < mysql_num_fields($result)) { // Oszlopok kiírása
		$meta = mysql_fetch_field($result, $i);
		file_put_contents($fajlnev, $meta->name, FILE_APPEND);
		
		if ( $i != (mysql_num_fields($result) - 1) )
			file_put_contents($fajlnev, ", ", FILE_APPEND); // Tagolás
		$wf_debug->RegisterDLEvent("Oszlop #" .($i+1). " létrehozva");
		$i++;
	}
	$wf_debug->RegisterDLEvent("Oszlopok létrehozva");
	
	file_put_contents($fajlnev, ") VALUES", FILE_APPEND);
	$wf_debug->RegisterDLEvent("Sorok létrehozása...");
	
	// Sorok
	$resultD = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf'].$table); // Lekérdezés
	$rows = mysql_num_rows($resultD);
	while ( $row = mysql_fetch_row($resultD) ) { // Sorok kiírása
		$wf_debug->RegisterDLEvent("Sor #" .(mysql_num_rows($resultD)-$rows+1). " írása...");
		file_put_contents($fajlnev, "\n('", FILE_APPEND); // Bevezetés
		for ($iD = 0; $iD < count($row); $iD++) {
			file_put_contents($fajlnev, $row[$iD], FILE_APPEND);
			
			if ( $iD < (count($row) - 1) ) // Tagolás
				file_put_contents($fajlnev, "', '", FILE_APPEND);
			
			if ( $iD == (count($row) - 1) )
				file_put_contents($fajlnev, "')", FILE_APPEND);
		}
		$wf_debug->RegisterDLEvent("Sor #" .(mysql_num_rows($resultD)-$rows+1). " írása befejezve");
		$rows--;
		if ( $rows != 0 )
			file_put_contents($fajlnev, ",", FILE_APPEND); // Az utolsó sor kivételével minden sort egy vesszővel tagolunk
	}
	$wf_debug->RegisterDLEvent("Sorok létrehozva");
	
	file_put_contents($fajlnev, ";\n\n", FILE_APPEND);
}

if ( $_POST['command'] == "backup_all" )
{
	$wf_debug->RegisterDLEvent("Biztonsági mentés megkezdve");
	
	file_put_contents($fajlnev, "# SQL Biztonsagi mentes\n# Ekkor: " .Datum("normal", "n", "d", "H", "i", "s"));
	file_put_contents($fajlnev, "\n# Verzio: " .RELEASE_TYPE. " " .VERSION. " " .RELEASE_DATE, FILE_APPEND);
	file_put_contents($fajlnev, "\n\n\n\n\n", FILE_APPEND);
	
	$wf_debug->RegisterDLEvent("Fájlfejléc létrehozva");
	
	BackupTable("addons");
	BackupTable("bannedips");
	BackupTable("chat");
	BackupTable("downloads");
	BackupTable("download_categ");
	BackupTable("forum");
	BackupTable("menuitems");
	BackupTable("modules");
	BackupTable("news");
	BackupTable("news_comments");
	BackupTable("plain");
	BackupTable("polls");
	BackupTable("poll_opinions");
	BackupTable("posts");
	BackupTable("siteconfig");
	BackupTable("statistics");
	BackupTable("topics");
	BackupTable("user");
	BackupTable("version");
	BackupTable("votes_cast");
	
	$wf_debug->RegisterDLEvent("Biztonsági mentés befejezve");
	
	file_put_contents($fajlnev, "\n# Vege", FILE_APPEND);
	$wf_debug->RegisterDLEvent("Fájl lezárva");
	
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .time(). "' WHERE variable='db_lastbackup'");
	ReturnTo("A biztonsági mentés létrehozva", "admin.php?site=dbbackup", "Vissza", TRUE);
	
	// Nem fut le a további kód
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}
$lastbackupT = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='db_lastbackup'"));
$lastbck = $lastbackupT[0];

print("A biztonsági mentés az adatbázisban található táblákról hoz létre egy SQL-futtatásra képes fájlt a szerveren. A biztonsági mentés csak a táblák adatait menti, azok struktúráit nem!<br>A kapott fájl tartalmazza a rendszer összes, az adatbázisban tárolt adatát (az addon adatokat NEM!). Ezt le kell majd futtatni az új adatbázison, egy sikeres <b>telepítés után</b>, és az adatok átmentésre kerülnek. (Telepítéskor választható a rendszernek egy gyorsabb, kezdeti adatok nélküli telepítése)
<br><br>
<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
<input type='hidden' name='site' value='dbbackup'>
<span class='formHeader'>Adatbázis biztonsági mentés futtatása</span>
<p class='formText'>Adatbázis utoljára lementve: " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $lastbck). "<br>
<input type='hidden' name='command' value='backup_all'>
<input type='submit' value='Adatbázis biztonsági mentése'>
</form>");

print("\n\n\n\n\n</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=dbbackup");
}
?>