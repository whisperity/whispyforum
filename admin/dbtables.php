<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/dbtables.php
   adatbázis táblák adatai
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Adatbázis részletei</h2></center>
<?php

print("<div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<td><b>Tábla</b><a class='feature-extra'><span class='hover'><span class='h3'>Tábla neve</span>Azon soroknál, ahol a tábla neve nyers nevet tartalmaz (tehát nincs lokalizálva, pl. Letöltések, XYZ addon beállításai), a tábla valószínűleg egy addon táblája, vagy nem is a portálmotor része.</span><sup>?</sup></a></td>
				<td><b>Adatméret</b></td>
				<td><b>Index-méret</b></td>
				<td><b>Tárolt méret</b><a class='feature-extra'><span class='hover'><span class='h3'>Tárolt méret</span>A táblában tárolt összes adat és index mérete.</span><sup>?</sup></a></td>
				<td><b>Felülírás</b><a class='feature-extra'><span class='hover'><span class='h3'>Felülírás</span>A tábla &quot;töredezettségéből&quot; adódó, lefoglalt, de fel nem használt, felesleges terület.<br>A felülírás törléséhez optimalizáld a táblákat.</span><sup>?</sup></a></td>
				<td><b>Összes méret</b><a class='feature-extra'><span class='hover'><span class='h3'>Összes méret</span>Tárolt méret + Felülírás<br>A tábla számára ténylegesen lefoglalt hely a felesleggel együtt.</span><sup>?</sup></a></td>
			</tr>");

// Táblaméret kiszámítása
$tablameret = $sql->Lekerdezes("SELECT TABLE_NAME, DATA_LENGTH, INDEX_LENGTH, DATA_FREE FROM information_schema.tables WHERE TABLE_SCHEMA='" .$cfg['dbname']. "'");
$adatmeret = 0;
$indexmeret = 0;
$osszmeret = 0;
$feluliras = 0;

while ( $sor = mysql_fetch_assoc($tablameret)) {
	$adatmeret = $adatmeret + $sor['DATA_LENGTH'];
	$indexmeret = $indexmeret + $sor['INDEX_LENGTH'];
	$osszmeret = $osszmeret + $sor['DATA_LENGTH'] + $sor['INDEX_LENGTH'];
	$feluliras = $feluliras + $sor['DATA_FREE'];
	
	
	// A táblaprefixum miatti kavarodás megszüntetése
	$tblnexplode = explode($cfg['tbprf'], $sor['TABLE_NAME']);
	
	if ( ( $tblnexplode[0] == $NULL ) && ( $tblnexplode[1] != $NULL ) && ( count($tblnexplode) >=2 ) )
	{
		$tnev = $tblnexplode[1];
	} elseif ( ( $tblnexplode[0] != $NULL ) && ( count($tblnexplode) == 1 ) ) {
		$tnev = $tblnexplode[0];
	}
	
	$tablanev = "";
	switch ( $tnev ) {
		case "addons":
			$tablanev = "<a href='admin.php?site=addons'>Addonok</a>";
			break;
		case "bannedips":
			$tablanev = "<a href='admin.php?site=banip'>IP-kitiltások</a>";
			break;
		case "download_categ":
			$tablanev = "Letöltés kategóriák";
			break;
		case "chat":
			$tablanev = "<a href='admin.php?site=chat'>Chat</a>";
			break;
		case "downloads":
			$tablanev = "<a href='admin.php?site=downloads'>Letöltések</a>";
			break;
		case "forum":
			$tablanev = "Fórumok";
			break;
		case "menuitems":
			$tablanev = "Menüelemek";
			break;
		case "modules":
			$tablanev = "<a href='admin.php?site=moduleeditor'>Modulok</a>";
			break;
		case "news":
			$tablanev = "Hírek";
			break;
		case "news_comments":
			$tablanev = "Hírhozzászólások";
			break;
		case "plain":
			$tablanev = "<a href='admin.php?site=plain'>Statikus tartalmak</a>";
			break;
		case "poll_opinions":
			$tablanev = "Szavazati lehetőségek";
			break;
		case "polls":
			$tablanev = "<a href='admin.php?site=polls'>Szavazások</a>";
			break;
		case "posts":
			$tablanev = "Fórum hozzászólások";
			break;
		case "siteconfig":
			$tablanev = "<a href='admin.php?site=configs'>Portál beállítások</a>";
			break;
		case "statistics":
			$tablanev = "<a href='admin.php?site=statistics'>Statisztika</a>";
			break;
		case "topics":
			$tablanev = "Fórumtémák";
			break;
		case "user":
			$tablanev = "Felhasználók";
			break;
		case "version":
			$tablanev = "Verzióadatok";
			break;
		case "votes_cast":
			$tablanev = "Leadott szavazatok";
			break;
		default:
			$tablanev = $tnev;
	}
	
	// Az addonbeállítási tömbök megnézése
	$addonset = explode("addonsettings_", $tablanev);
	
	if ( ( $addonset[0] == $NULL ) && ( $addonset[1] != $NULL ) && ( count($addonset) >=2 ) )
	{
		$addonid = mysql_fetch_assoc($sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."addons WHERE subdir='" .mysql_real_escape_string($addonset[1]). "' LIMIT 1"));
		
		if ( file_exists("addons/" .$addonset[1]. "/settings.php") ) {
			$tablanev = "<a href='admin.php?site=addons&action=settings&id=" .$addonid['id']. "'>" .$addonset[1]. " addon beállításai</a>";
		} else {
			$tablanev = $addonset[1]." addon beállításai";
		}
		
	} elseif ( ( $addonset[0] != $NULL ) && ( count($addonset) == 1 ) ) {
		$tablanev = $addonset[0];
	}
	
	print("<tr>
		<td>" .$tablanev. "</td>
		<td>" .DecodeSize($sor['DATA_LENGTH']). "</td>
		<td>" .DecodeSize($sor['INDEX_LENGTH']). "</td>
		<td>" .DecodeSize($sor['DATA_LENGTH'] + $sor['INDEX_LENGTH']). "</td>");
		
		if ( $sor['DATA_FREE'] == 0 ) {
			print("<td>" .DecodeSize($sor['DATA_FREE']). "</td>
			<td>" .DecodeSize($sor['DATA_LENGTH'] + $sor['INDEX_LENGTH'] + $sor['DATA_FREE']). "</td>");
		} elseif ( $sor['DATA_FREE'] > 0 ) {
			print("<td><span style='color: red; font-weight: bold'>" .DecodeSize($sor['DATA_FREE']). "</span></td>
			<td><span style='color: red; font-weight: bold'>" .DecodeSize($sor['DATA_LENGTH'] + $sor['INDEX_LENGTH'] + $sor['DATA_FREE']). "</span></td>");
		}
		
	print("</tr>");
}
print("<tr>
		<td><b>Összesen</b></td>
		<td><b>" .DecodeSize($adatmeret). "</b></td>
		<td><b>" .DecodeSize($indexmeret). "</b></td>
		<td><b>" .DecodeSize($osszmeret). "</b></td>");
		
		if ( $feluliras == 0 ) {
			print("<td><b>" .DecodeSize($feluliras). "</b></td>
			<td><b>" .DecodeSize($adatmeret + $indexmeret + $feluliras). "</b></td>");
		} elseif ( $feluliras > 0 ) {
			print("<td><span style='color: red'><b>" .DecodeSize($feluliras). "</b></span></td>
			<td><span style='color: red'><b>" .DecodeSize($adatmeret + $indexmeret + $feluliras). "</b></span></td>");
		}
		
	print("
</tr>
</table></div>");

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=dbtables");
}
?>