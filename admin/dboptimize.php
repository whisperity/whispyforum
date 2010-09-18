<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/dboptimize.php
   adatbázis optimalizáció
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Adatbázis optimalizáció</h2></center>
<?php

if ( $_POST['command'] == "optimize_all" )
{
	$wf_debug->RegisterDLEvent("Adatbázisoptimalizáció futtatása...");
	
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."addons");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."bannedips");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."chat");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."downloads");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."download_categ");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."forum");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."menuitems");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."modules");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."news");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."news_comments");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."plain");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."polls");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."poll_opinions");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."posts");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."siteconfig");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."statistics");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."topics");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."user");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."version");
	$sql->Lekerdezes("OPTIMIZE TABLE " .$cfg['tbprf']."votes_cast");
	
	$wf_debug->RegisterDLEvent("Optimalizáció kész");
	
	$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."siteconfig SET value='" .time(). "' WHERE variable='db_lastoptimize'");
	ReturnTo("A Táblák optimalizálása sikeres.", "admin.php?site=dboptimize", "Vissza", TRUE);
	
	// Nem fut le a további kód
	print("</td><td class='right' valign='top'>");
	Lablec();
	die();
}

$lastoptimizeT = mysql_fetch_row($sql->Lekerdezes("SELECT value FROM " .$cfg['tbprf']."siteconfig WHERE variable='db_lastoptimize'"));
$lastopt = $lastoptimizeT[0];

// Táblaméret kiszámítása
$tablameret = $sql->Lekerdezes("SELECT DATA_LENGTH, INDEX_LENGTH, DATA_FREE FROM information_schema.tables WHERE TABLE_SCHEMA='" .$cfg['dbname']. "'");
$adatmeret = 0;
$indexmeret = 0;
$osszmeret = 0;
$feluliras = 0;

while ( $sor = mysql_fetch_assoc($tablameret)) {
	$adatmeret = $adatmeret + $sor['DATA_LENGTH'];
	$indexmeret = $indexmeret + $sor['INDEX_LENGTH'];
	$feluliras = $feluliras + $sor['DATA_FREE'];
}
$osszmeret = $adatmeret + $indexmeret;

// Bevezetés kiírása
print("Az adatbázisban található táblák folyamatos módosítgatások után -hasonlóan a merevlemezekhez-, töredeznek. Ezeket a töredékeket időnként töredezettségmentesíteni kell, erre szolgál a táblák optimalizációja.
<br><br>
<form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
<input type='hidden' name='site' value='dboptimize'>
<span class='formHeader'>Adatbázis optimalizáció futtatása</span>
<p class='formText'>Adatbázis utoljára optimalizálva: " .Datum("normal", "kisbetu", "dL", "H", "i", "s", $lastopt). "<br>
Adattáblák mérete: " .DecodeSize($osszmeret). " (" .DecodeSize($adatmeret). " adat, " .DecodeSize($indexmeret). " index)<br>");

if ( $feluliras > 0 ) {
	print("<span style='color: red'>Felülírás: " .DecodeSize($feluliras). "</span></p>
	<input type='hidden' name='command' value='optimize_all'>
<input type='submit' value='Adatbázis optimalizálása'>");
} elseif ( $feluliras == 0 ) {
	print("Felülírás: " .DecodeSize($feluliras). "</p>
	<input type='button' value='Nincs szükség a táblák optimalizálására' disabled>");
}

print("</form>");

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=dboptimize");
}
?>