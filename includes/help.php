<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/help.php
   output leírásokat tartalmazó script
*/ 
 
 @include("functions.php");
 
function DatumHelp()
 {
	print("<h2>Dátum</h2><br>A függvény használata: <b>Datum(év,hónap,nap,ora,perc,masodperc,[epoch])</b><br>Év:<ul><li><b>normal</b> - normál megjelenítés (Y, pl. " .Datum("normal","","","","",""). ")</li></ul>");
	print("Hónap:<ul><li><b>n</b> - hónap száma (0-k nélkül, pl: " .Datum("","n","","","",""). ")</li><li><b>m</b> - hónap száma (nullákkal, pl: " .Datum("","m","","","",""). ")</li><li><b>kisbetu</b> - hónap neve kisbetűvel (pl: " .Datum("","kisbetu","","","",""). ")</li><li><b>nagybetu</b> - hónap neve nagybetűvel (pl. " .Datum("","nagybetu","","","",""). ")</li></ul>");
	print("Nap:<ul><li><b>d</b> - nap száma vezető nullákkal (pl: " .Datum("","","d","","",""). ")</li><li><b>j</b> - nap száma vezetőnullák nélkül (pl: " .Datum("","","j","","",""). ")</li><li><b>l</b> - a nap neve kisbetűvel (pl: " .Datum("","","l","","",""). ")</li><li><b>L</b> - a nap neve nagybetűvel (pl: " .Datum("","","L","","",""). ")</li><li><b>dl</b> - a nap száma és neve kisbetűvel (pl: " .Datum("","","dl","","",""). ")</li><li><b>dL</b> - a nap száma és neve nagybetűvel (pl: " .Datum("","","dL","","",""). ")</li></ul>");
	print("Ora:<ul><li><b>H</b> - óra (vezető nullákkal, 24 órás formátum, pl: " .Datum("","","","H","",""). ")</li></ul>");
	print("Perc:<ul><li><b>i</b> - perc (vezető nullákkal, pl: " .Datum("","","","","i",""). ")</li></ul>");
	print("Másodperc:<ul><li><b>s</b> - másodperc (vezető nullákkal, pl: " .Datum("","","","","","s"). ")</li></ul>");
	print("[Epoch]:<ul><li>A kívánt dátum unix-epoch óta eltelt másodpercben<br>Megadásával egy kívánt időpont kérhető le, megadása nem kötelező, alapesetben a meghíváskori epoch-időt (<code>time();</code>) veszi alapul</li></ul>");
	print('Az értétek STRING típusúak!<br>Például, hogy ezt a dátumot kapjuk: <i>' .Datum('normal','kisbetu','dL', 'H', 'i', 's'). '</i> , a következőt kell beírni: <b>Datum("normal","kisbetu","dL","H","i","s")</b>');
 }
 
function HibauzenetHelp()
{
	print("<h2>Hibaüzenet</h2><br>A függvény használata: <b>Hibauzenet(tipus, cim, leiras, fajl, sor)</b><br>Típus:<ul><li><b>WARNING</b> - figyelmeztetés (sárga felkiáltójel háromszögben</li><li><b>ERROR</b> - hiba (piros körben fehér X)</li><li><b>CRITICAL</b> - hiba, scriptmegszakítással (<i>exit;</i>, piros körben fehér X)</li></ul>");
	print("Cim: a hiba címsorában megjelenő szöveg<br>Leiras: a hiba leírása (részletes szöveg)<br><b>fajl</b> a megszakítást okozó fájl neve (egyszerűen postolhatod a fájl nevét a <b>__FILE__</b> beírásával)<br><b>sor</b> a megszakítást okozó fájlban lévő sor száma (egyszerűen postolhatod a sort a <b>__LINE__</b> beírásával");
	print('A következő beírásával: <b>Hibauzenet("CRITICAL", "Próba", "Csak meghalok!", __FILE__, __LINE__)</b> beírásával a következő hibaüzenet kapod:');
	Hibauzenet("CRITICAL", "Próba", "Csak meghalok!", __FILE__, __LINE__);
}

function UpdateHelp()
{
	print("<h2>Frissítéssel kapcsolatos információk</h2>\n
		Ha azt a hibaüzenetet kapod, hogy a futtatott és a telepített verzió nem egyezik, nem feltétlenül kell aggódnod.
		<br>Az eltérő verziók használata a legtöbb esetben csak apró kompatibilitási problémákat okoz, azonban, ha a hibaüzenet elszaporodnak, meg kell fontolni a rendszer újratelepítését.<br>A fejlesztők dolgoznak egy egyesítőscripten, mellyel az újratelepítés nélkül lehetne az adatbázist frissíteni, azonban ez még a jövő zenéje. Addig is, az adatbázist a telepítőscripttel (<a href='../install/index.php'>itt elérhető</a>) telepítheted újra.");
}

function BBCodeHelp()
{
	print("<h2>BB-kódok</h2>\n
		A BB-kódok az internetes fórumok újításai, melyben a szövegszerkesztési elemeket a HTML-kód helyett BB-kódokban írjuk. Ez a legtöbb esetben biztonsági intézkedésként került bele a rendszerekbe, hogy a HTML-kódok értelmezését letiltsuk.
	<br><br>
	<table>
	<tr>
		<th></th>
		<th>Használat</th>
		<th>Példa</th>
		<th>Példa (ahogy megjelenik)</th>
	</tr>
	<tr>
		<td>Félkövér</td>
		<td><code>[b]szöveg[/b]</code></td>
		<td><code>[b]ez egy félkövér szöveg[/b]</code></td>
		<td>" .BBDecode("[b]ez egy félkövér szöveg[/b]"). "</td>
	</tr>
	<tr>
		<td>Dőlt</td>
		<td><code>[i]szöveg[/i]</code></td>
		<td><code>[i]ez egy dőlt szöveg[/i]</code></td>
		<td>" .BBDecode("[i]ez egy dőlt szöveg[/i]"). "</td>
	</tr>
	<tr>
		<td>Aláhúzott</td>
		<td><code>[u]szöveg[/u]</code></td>
		<td><code>[u]ez egy aláhúzott szöveg[/u]</code></td>
		<td>" .BBDecode("[u]ez egy aláhúzott szöveg[/u]"). "</td>
	</tr>
	<tr>
		<td>URL hivatkozás</td>
		<td><code>[url]url-link[/url]megjelenítendő szöveg[/a]</td>
		<td><code>[url]http://hu.wikipedia.org[/url]Wikipédia[/a]</td>
		<td>" .BBDecode("[url]http://hu.wikipedia.org[/url]Wikipédia[/a]"). "</td>
	</tr>
	<tr>
		<td>Kép</td>
		<td><code>[img]képhivatkozás[/img]</td>
		<td><code>[img]http://www.google.hu/logos/worldcupopen10-hp.gif[/img]</td>
		<td>" .BBDecode("[img]http://www.google.hu/logos/worldcupopen10-hp.gif[/img]"). "</td>
	</tr>
	</table>
Bizonyos BB-kódok mixelhetőek, azonban a weboldalon a BB-kódokat a jelenleg megadott formában kell használni!
<a href='javascript: self.close()'>Ablak bezárása</a>");
}

function UrlapHelp()
{
	print("<h2>Űrlap</h2>
	
	<b><span class='star'>Az űrlapkezelő még nem képezi a weboldal szerves részét</span></b> (értsd: nincsen a magkódba töltve), <b><span class='star'>ezért az űrlapmodul használatához külön be kell tölteni az <i>/includes/forms.php</i> fájlt, valamint a globális \$forms változót</span></b> (mely az osztályt kezeli).
	
	<br><br>Az osztály segítségével egyszerűbben lehet űrlapokat létrehozni. Az oszály függvényei:<br><br><b>StartForm ( [\$method = GET], [\$action = self], [\$header = ''])</b><br>
<b>[\$method]</b> [GET] - az űrlap elküldésének metódusa:<ul><li><b>GET</b> - HTTP GET</li><li><b>POST</b> - HTTP POST</li><li><b>NO</b> - nincs elküldés</li></ul>
<b>[\$action]</b> [self] - az űrlap célja<ul><li><b>self</b> - automatikus átállás a <b>\$_SERVER['PHP_SELF']</b> értékére</li><li><i>egyéb esetben</i> a megadott weboldal</li></ul>
<b>[\$header]</b> [''] - az űrlap fejléce<hr>");
	print("<b>Urlapelem ( \$tipus, \$nev, \$ertek, \$size = 25, [\$szoveg = ''], [\$kotelezo = FALSE], [\$aHeader = ''], [\$aText = ''])</b><br>
<b>\$tipus</b> - az űrlapmező típusa<ul><li><b>text</b> - szövegmező</li><li><b>password</b> - jelszómező</li><li><b>hidden</b> - rejtett mező (vizuálisan nem változtatható az értéke, a kód adja meg)</li><li><b>select</b> - választólista létrehozása</li><li><b>option</b> - a létrehozott listához új opció hozzáadása</li><li><b>select-end</b> - létrehozott választási lista lezárása</li><li><b>submit</b> - elküldőgomb</li></ul>
<b>\$nev</b> - az űrlapmező neve<br>
<b>\$ertek</b> - az űrlapmező értéke<blockquote>Ha az űrlapmezőnek tartalmaznia kell a benne előzőleg megadott értéket (pl. egy nem kitöltött kötelező mező esetén, a felhasználót megkíméleni az újra beírogatástól), akkor értéknek <b><code>post-get</code></b> -et kell megadni</blockquote>
<b>[\$size]</b> [25] - az űrlapmező hossza<blockquote>Csak <b><code>\$tipus = 'text'</code></b> és <b><code>\$tipus = 'password'</code></b> esetén</blockquote>
<b>[\$szoveg]</b> - az űrlapmező előtt megjelenítendő szöveg<br>
<b>[\$kotelezo]</b> [FALSE]<ul><li><b>TRUE</b> - A kötelezőséget megjelenítő csillag és a csillagra ráhúzott egérnél megjelenő doboz megjelenítése</li><li><b>FALSE</b> - a mező nem kötelező, a csillag nem jelenik meg</ul>
<b>[\$aHeader]</b> és <b>[\$aText]</b><blockquote>Ha szeretnénk megjeleníteni egy <sup>?</sup>-ra ráhúzott egér melett egy kis információs dobozt a beviteli mezőnél, a két paraméternek értéket kell adni (<i>\$aHeader</i> a címsor, <i>\$aText</i> a megjelenítendő szöveg). A funkció csak akkor hatásos, ha <b>\$szoveg</b> is kapott értéket!</blockquote><hr>");
	print("<b>EndForm ()</b> <blockquote>Az űrlap zárása</blockquote><hr>");
	print("Például: a következő pár sor:<br><br>");
?>
<code>
	$forms->StartForm("GET", "self", "Űrlap");<br>
	$forms->UrlapElem("text", "fnev", "post-get", 25, "Felhasználói név", TRUE, "Felhasználói név", "Ez lesz később a bejelentkezési neved");<br>
	$forms->UrlapElem("password", "fpass", "post-get", 25, "Jelszó", TRUE, "Jelszavad", "A jelszó szükséges a belépéshez<br><b>A jelszavadat őrizd biztonságos helyen, rajtad kívül ne tudja senki</b>");<br>
	$forms->UrlapElem("hidden", "cmd", "Urlap");<br>
	$forms->UrlapElem("select", "szelekt", "", 1, "Választás", FALSE, "Válassz valamit", "Haha:)");<br>
	$forms->UrlapElem("option", "Lehetőség 1", "l1");<br>
	$forms->UrlapElem("option", "Lehetőség 2", "l2");<br>
	$forms->UrlapElem("select-end", "", "");<br>
	$forms->UrlapElem("submit", "submit", "Elküldés", 10, "Az űrlap elküldése", FALSE, "Elküldés", "A gombra kattintva elküldheted az űrlapot");<br>
	$forms->EndForm();<br>
</code>
<?php
	print("<br>a következő űrlapot hozza létre:<br>");
	
	@include('forms.php'); // Modul betöltéseű
	@include('../config.php'); // Konfigurációs fájl (THEME_NAME-hez szükséges)
	print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>"); // Téma betöltése
	global $forms; // Osztály betöltése
	
	$forms->StartForm("GET", "self", "Űrlap");
	$forms->UrlapElem("text", "fnev", "post-get", 25, "Felhasználói név", TRUE, "Felhasználói név", "Ez lesz később a bejelentkezési neved");
	$forms->UrlapElem("password", "fpass", "post-get", 25, "Jelszó", TRUE, "Jelszavad", "A jelszó szükséges a belépéshez<br><b>A jelszavadat őrizd biztonságos helyen, rajtad kívül ne tudja senki</b>");
	$forms->UrlapElem("hidden", "cmd", "Urlap");
	$forms->UrlapElem("select", "szelekt", "", 1, "Választás", FALSE, "Válassz valamit", "Haha:)");
	$forms->UrlapElem("option", "Lehetőség 1", "l1");
	$forms->UrlapElem("option", "Lehetőség 2", "l2");
	$forms->UrlapElem("select-end", "", "");
	$forms->UrlapElem("submit", "submit", "Elküldés", 10, "Az űrlap elküldése", FALSE, "Elküldés", "A gombra kattintva elküldheted az űrlapot");
	$forms->EndForm();
}

switch ($_GET['cmd'])
{
	case "Update":
		UpdateHelp();
		break;
	case "BB":
		BBCodeHelp();
		break;
	case "Hibauzenet":
		HibauzenetHelp();
		break;
	case "Datum":
		DatumHelp();
		break;
	case "Urlap":
		UrlapHelp();
		break;
	case "adminTools":
		/* Választási űrlap létrehozása */
		@include('forms.php'); // Modul betöltéseű
		@include('../config.php'); // Konfigurációs fájl (THEME_NAME-hez szükséges)
		print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>"); // Téma betöltése
		global $forms; // Osztály betöltése
		
		$forms->StartForm("GET", "self", "Kérlek válassz, melyik adminisztrátori eszközről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából adminisztrátori eszközt", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "A portálrendszer frissítése", "Update");
		$forms->UrlapElem("option", "", "adminTools");
		$forms->UrlapElem("option", "<< Vissza a főmenübe", $NULL);
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Elküldés");
		$forms->EndForm();
		
		break;
	case "DeveloperTools":
		/* Választási űrlap létrehozása */
		@include('forms.php'); // Modul betöltéseű
		@include('../config.php'); // Konfigurációs fájl (THEME_NAME-hez szükséges)
		print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>"); // Téma betöltése
		global $forms; // Osztály betöltése
		
		$forms->StartForm("GET", "self", "Kérlek válassz, melyik fejlesztői eszközről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából fejlesztői eszközt", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "Hibaüzenet funkció", "Hibauzenet");
		$forms->UrlapElem("option", "Dátum funkció", "Datum");
		$forms->UrlapElem("option", "Űrlapgenerálási osztály", "Urlap");
		$forms->UrlapElem("option", "", "DeveloperTools");
		$forms->UrlapElem("option", "<< Vissza a főmenübe", $NULL);
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Elküldés");
		$forms->EndForm();
		
		break;
	default:
		/* Választási űrlap létrehozása */
		@include('forms.php'); // Modul betöltéseű
		@include('../config.php'); // Konfigurációs fájl (THEME_NAME-hez szükséges)
		print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>"); // Téma betöltése
		global $forms; // Osztály betöltése
		
		$forms->StartForm("GET", "self", "Kérlek válassz, miről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "BB-kódok", "BB");
		$forms->UrlapElem("option", "", $NULL);
		$forms->UrlapElem("option", "[Adminisztrátori eszközök]", "adminTools");
		$forms->UrlapElem("option", "A portálrendszer frissítése", "Update");
		$forms->UrlapElem("option", "", $NULL);
		$forms->UrlapElem("option", "[Fejlesztőknek]", "DeveloperTools");
		$forms->UrlapElem("option", "Hibaüzenet funkció", "Hibauzenet");
		$forms->UrlapElem("option", "Dátum funkció", "Datum");
		$forms->UrlapElem("option", "Űrlapgenerálási osztály", "Urlap");
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Elküldés");
		$forms->EndForm();
		
		break;
}

?>