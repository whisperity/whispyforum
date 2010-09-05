<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/checks.php
   ellenőrzés
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Ellenőrzés</h2></center>
A jelenlegi modul segítségével egy önellenőrzést hajtatsz végre a portálrendszeren. A modul megvizsgálja a legtöbb döntő befolyású változót, struktúrát, beállítást, majd egy összegző jelentést készít róla. <b>Az ellenörzés 100%-os eredménye sem garantálhatja a teljes biztonságot, hatásfokot!</b><br>Bizonyos esetekben előfordul, hogy az ellenörzés pár percet igénybe vesz. Az ellenörzés elkezdve  
<?php
 print(Datum("normal", "kisbetu", "dL", "H", "i", "s"). "-kor.<br>");
 /* Az $ellenorzes tömb egyfajta statisztikát tárol */
 global $ellenorzes;
	$ellenorzes = array(
	'szama' => 0,
	'OK' => 0,
	'WARNING' => 0,
	'FAILED' => 0,
 );
 print("<br>");
function Eredmeny($status, $title, $message)
{
	global $ellenorzes, $cfg;
	/* Egy biztonsági ellenörzés eredményének kiírása */
	$ellenorzes['szama']++; // Ellenörzések számának növelése
	$ellenorzes[$status]++; // Ellenörzés eredményének megfelelő számláló növelése
	switch ($status) {
		case OK:
			$status = 'Rendben';
			$icon = 'passed';
		break;
		case WARNING :
			$status = 'Figyelmeztetés';
			$icon = 'warning';
		break;
		case FAILED :
			$status = 'Kritikus biztonsági rés';
			$icon = 'failed';
		break;
	} 
	print("<img src='admin/" .$icon. ".gif' height='24' width='24' alt='" .$status. "' align='left'>
	<h3 style='border-bottom: 1px dotted black; margin: 0px 0px 0px 24px;'>" .$title. "</h3>
		<div align='right'>
			<b>" .$status. "</b>	
		</div>
	<p>" .$message. "</p></div>");
}
	
	/* Telepítőscriptek meglétének ellenörzése 
		meglétük esetén FAILED
		hiányuk estén OK */
	if ( ( is_dir('install') ) || ( file_exists('install.php') ) )
	{
		Eredmeny(FAILED, "Telepítőscriptek megléte", "A portálrendszer telepítőfájljai még mindig megtalálhatóak a szerveren. Sikeres telepítés után ezen fájlok már nem szükségesek, és komoly biztonsági rést nyitnak. Kérlek távolítsd el őket!");
	} else {
		Eredmeny(OK, "Telepítőscriptek törlése", "");
	}
	
	/* Konfigurációs fájl írhatósága
		írhatóság esetén WARNING
		zároltsága esetén OK */
	if ( is_writeable('config.php') )
	{
		Eredmeny(WARNING, "<code>config.php</code> írásvédettsége", "A telepítés után már nem szükséges a fájlnak írhatónak lennie.");
	} else {
		Eredmeny(OK, "<code>config.php</code> írásvédettsége", "");
	}
	
	/* mysql felhasználó neve
		root esetén FAILED
		egyéb esetben OK */
	if ( $cfg['dbuser'] == "root" )
	{
		Eredmeny(FAILED, "mysql felhasználóinév: <code>root</code>", "A <i>root</i> felhasználó teljes jogkörrel rendelkezik a mySQL szerveren, valamint természeténél fogva nem távolítható el, ezért erősen ellenjavallot a használata!");
	} else {
		Eredmeny(OK, "mysql felhasználóinév", "");
	}
	
	/* adatbázisnév ellenörzése
		mysql esetén FAILED
		information_schema esetén FAILED
		egyéb esetben OK */
	if ( ($cfg['dbname'] == "mysql") || ($cfg['dbname'] == "INFORMATION_SCHEMA") )
	{
		Eredmeny(FAILED, "Adatbázis neve: <code>" .$cfg['dbname']. "</code>", "A használt adatbázis az adatbázisszerver kritikus adatbázisa, használata a szerver összeomlásához vezethet!");
	} else {
		Eredmeny(OK, "Adatbázis neve: <code>" .$cfg['dbname']. "</code>", "A portál saját adatbázist használ");
	}
	
	/* táblaprefixum ellenörzése
		ha van, OK
		ha nincs, WARNING */
	if ( $cfg['tbprf'] == $NULL )
	{
		Eredmeny(WARNING, "Nincs táblaprefixum", "A táblaprefixum használata megelőzi az azonos adatbázist használó rendszerek összeakadását. <b>A táblaprefixum hiánya nem számít nagy hibának, ha egy adatbázist csak egy rendszer használ!</b>");
	} else {
		Eredmeny(OK, "Táblaprefixum beállítva: <code>" .$cfg['tbprf']. "</code>", "A táblaprefixum használata megelőzi az azonos adatbázist használó rendszerek összeakadását.");
	}
	
	/* GLOBAL tömbök regisztrálása
		0 esetén OK
		1 esetén FAILED */
	if ( (ini_get("register_globals") == 0) || (ini_get("register_globals") == "Off") )
	{
		Eredmeny(OK, "<code>\$_GLOBALS</code> változók regisztrálása", "A globális változók regisztrálása ki van kapcsolva.");
	} else {
		Eredmeny(FAILED, "<code>\$_GLOBALS</code> változók regisztrálása", "A globális változók regisztrálása be van kapcsolva. Kikapcsolása erősen ajánlott, mivel szükségtelen biztonsági rést nyit. (A rendszer 100%-ig működik kikapcsolt register_globals esetén is)<br>Kikapcsolásához állítsd a php.ini register_globals tartalmát Off-ra. (<code>ini_set(<span style='color: grey'>'register_globals'</span>, <span style='color: grey'>'Off'</span>);)");
	}
	
	/* Hibák megjelenítése
		0 esetén OK
		1 esetén FAILED */
	if ( (ini_get("display_errors") == 0) || (ini_get("display_errors") == "Off") )
	{
		Eredmeny(OK, "Hibák megjelenítése kikapcsolva", "A hibák megjelenítése ki van kapcsolva. A hibákról a keretrendszer hibakezelője értesíti a felhasználókat.");
	} else {
		Eredmeny(FAILED, "Hibák megjelenítése bekapcsolva", "A hibák megjelenítése a PHP-értelmezőnek nem szükséges, mivel a keretrendszer tartalmaz egy beépített hibaüzenet-megjelenítőt. Emelett a PHP hibaüzenetei tartalmazhatnak utalásokat az adatbázis struktúrájára, tartalmára, a webszerver elérési útjára, és egyéb érzékeny információkra! (<code>ini_set(<span style='color: grey'>'display_errors'</span>, <span style='color: grey'>'Off'</span>);)");
	}
	
	/* Hibák megjelenítése ($wf_debug osztály)
		0 esetén OK
		1 esetén FAILED */
	if ( SHOWDEBUG == 0 )
	{
		Eredmeny(OK, "Hibakeresési információk kikapcsolva", "");
	} else {
		Eredmeny(FAILED, "Hibakeresési információk bekapcsolva", "A hibakeresési információk megjelenítése be van kapcsolva. A megjelenítés kikapcsolása éles rendszerben <b>KIMONDOTTAN AJÁNLOTT</b>, mivel a megjelenítő által a képernyőre írt adat tartalmaz minden rejtett SQL-kérést, generálási naplót, munkamenetinformációt és a konfigurációs tábla adatait (ahonnan kiolvasható például a mysql-hozzáférési nevet és jelszót!");
	}
	
	print("Az ellenörzés véget ért " .Datum("normal","kisbetu","dL","H","i","s"). "-kor. A végeredmény: <b>" .$ellenorzes['szama']. "</b> ellenörzésből <b>" .$ellenorzes['FAILED']. "</b> kritikus biztonsági rés került felfedezésre, <b>" .$ellenorzes['WARNING']. "</b> kisebb hiányosságra (figyelmeztetések) derült fény, és <b>" .$ellenorzes['OK']. "</b> ponton kiválóan teljesített a weboldal.");
	
	/* Arány kiszámítása
		az arány a kritikus hibák százaléka, valamint a figyelmeztetések százalékának fele összeadva, 100-ból kivonva */
	$Farany = ($ellenorzes['FAILED'] / $ellenorzes['szama']) * 100;
	$Warany = (($ellenorzes['WARNING'] / $ellenorzes['szama']) * 100) / 2;
	$arany = 100 - ($Farany + $Warany);
	print("<br>A végeredmény: <b>" .$arany. "%</b> ");
	
	if ( $arany >= 75 )
		print("<img src='admin/passed.gif' height='24' width='24' alt='Rendben'> Rendben");
	if ( ($arany >= 40) && ($arany <= 74) )
		print("<img src='admin/warning.gif' height='24' width='24' alt='Figyelmeztetés'> Megfelelő");
	if ( $arany <= 39 )
		print("<img src='admin/failed.gif' height='24' width='24' alt='Kritikus biztonsági rés'> Kritikus");
		
	print("&nbsp;&nbsp;&nbsp;(100% esetén minden ponton RENDBEN eredményt kap a rendszer, 0% esetén minden ponton kritikus biztonsági rés található)");
print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=securitycheck");
}
?>