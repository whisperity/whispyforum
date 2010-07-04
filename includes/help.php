<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/help.php
   output leírásokat tartalmazó script
*/ 
 
 @include("functions.php");
 @include('forms.php'); // Modul betöltéseű
 @include('../config.php'); // Konfigurációs fájl (THEME_NAME-hez szükséges)
 print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>"); // Téma betöltése
 global $forms; // Osztály betöltése
 
function FunctionsHelp()
{
	print("<h2>Az alábbi részben a portálrendszer egyéb funkcióiról kapsz segítséget. Használatukhoz szükséges, hogy a fájl a portálrendszer keretében fusson</h2>");
	
	
	print("<h2>Dátum</h2><br>A függvény használata: <b>Datum(év,hónap,nap,ora,perc,masodperc,[epoch])</b><br>Év:<ul><li><b>normal</b> - normál megjelenítés (Y, pl. " .Datum("normal","","","","",""). ")</li></ul>");
	print("Hónap:<ul><li><b>n</b> - hónap száma (0-k nélkül, pl: " .Datum("","n","","","",""). ")</li><li><b>m</b> - hónap száma (nullákkal, pl: " .Datum("","m","","","",""). ")</li><li><b>kisbetu</b> - hónap neve kisbetűvel (pl: " .Datum("","kisbetu","","","",""). ")</li><li><b>nagybetu</b> - hónap neve nagybetűvel (pl. " .Datum("","nagybetu","","","",""). ")</li></ul>");
	print("Nap:<ul><li><b>d</b> - nap száma vezető nullákkal (pl: " .Datum("","","d","","",""). ")</li><li><b>j</b> - nap száma vezetőnullák nélkül (pl: " .Datum("","","j","","",""). ")</li><li><b>l</b> - a nap neve kisbetűvel (pl: " .Datum("","","l","","",""). ")</li><li><b>L</b> - a nap neve nagybetűvel (pl: " .Datum("","","L","","",""). ")</li><li><b>dl</b> - a nap száma és neve kisbetűvel (pl: " .Datum("","","dl","","",""). ")</li><li><b>dL</b> - a nap száma és neve nagybetűvel (pl: " .Datum("","","dL","","",""). ")</li></ul>");
	print("Ora:<ul><li><b>H</b> - óra (vezető nullákkal, 24 órás formátum, pl: " .Datum("","","","H","",""). ")</li></ul>");
	print("Perc:<ul><li><b>i</b> - perc (vezető nullákkal, pl: " .Datum("","","","","i",""). ")</li></ul>");
	print("Másodperc:<ul><li><b>s</b> - másodperc (vezető nullákkal, pl: " .Datum("","","","","","s"). ")</li></ul>");
	print("[Epoch]:<ul><li>A kívánt dátum unix-epoch óta eltelt másodpercben<br>Megadásával egy kívánt időpont kérhető le, megadása nem kötelező, alapesetben a meghíváskori epoch-időt (<code>time();</code>) veszi alapul</li></ul>");
	print('Az értétek STRING típusúak!<br>Például, hogy ezt a dátumot kapjuk: <i>' .Datum('normal','kisbetu','dL', 'H', 'i', 's'). '</i> , a következőt kell beírni: <b>Datum("normal","kisbetu","dL","H","i","s")</b>');
	
	print("<hr>");
	
	print("<h2>Hibaüzenet</h2><br>A függvény használata: <b>Hibauzenet(tipus, cim, leiras, fajl, sor)</b><br>Típus:<ul><li><b>WARNING</b> - figyelmeztetés (sárga felkiáltójel háromszögben</li><li><b>ERROR</b> - hiba (piros körben fehér X)</li><li><b>CRITICAL</b> - hiba, scriptmegszakítással (<i>exit;</i>, piros körben fehér X)</li></ul>");
	print("Cim: a hiba címsorában megjelenő szöveg<br>Leiras: a hiba leírása (részletes szöveg)<br><b>fajl</b> a megszakítást okozó fájl neve (egyszerűen postolhatod a fájl nevét a <b>__FILE__</b> beírásával)<br><b>sor</b> a megszakítást okozó fájlban lévő sor száma (egyszerűen postolhatod a sort a <b>__LINE__</b> beírásával");
	print('A következő beírásával: <b>Hibauzenet("ERROR", "Próba", "Hibaüzenet!")</b> beírásával a következő hibaüzenet kapod:');
	Hibauzenet("ERROR", "Próba", "Hibaüzenet!");
	
	print("<hr>");
	
	print("<h2>BB-kód és emoticon értelmezések</h2><br>A függvények használati sorrendje:<br>");
?>
<code>
	<i>//$szoveg az átalakítani kívánt szöveg nyers tartalma</i><br>
	$szoveg = EmoticonParse($szoveg); // Hangulatjelek átalakítása [img]kép[/img]-s BB-kóddá<br>
	$szoveg = HTMLDestroy($szoveg); // HTML kódok átalakítása látható, de működésképtelen szöveggé<br>
	$szoveg = BBDecode($szoveg); // BB kódok átalakítása HTML-kóddá (hangulatjeleket képpé)<br>
	<i>//$szoveg mostmár az átalakult szöveget tartalmazza</i>
</code>
<?php
	print("<br>A három function <b>megadott sorrendű</b> egymásutáni használatával az adatbázisban BB-kódként tárolt szöveget (pl. fórum) láthatóvá tesszük, HTML tagekké alakítva megjelenítjük.<br><br>
	<table border='1' cellspacing='1' cellpadding='1'>
	<tr>
		<td></td>
		<th>Változók</th>
		<th>Visszatérési érték</th>
	</tr>
	<tr>
		<th>EmoticonParse</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$emotText</th>
				<td>A bemeneti BB-kódolt szöveg</td>
			</tr>
			</table>
		</td>
		<td>A bemenő szöveg hangulatjelei <code>[img]képlink[/img]</code> formátumban</td>
	</tr>
	<tr>
		<th>HTMLDestroy</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$HTMLText</th>
				<td>A bemeneti szöveg, tele HTML kódokkal</td>
			</tr>
			</table>
		</td>
		<td>A bemenő szöveg HTML-kódok eltüntetve a <tt>htmlspecialchars()</tt> segítségével</td>
	</tr>
	<tr>
		<th>BBDecode</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$BBText</th>
				<td>A bemeneti szöveg, tele BB kódokkal</td>
			</tr>
			</table>
		</td>
		<td>A bemenő szöveg BB kódjai értelmezve</td>
		<td><a href='help.php?cmd=BB' onClick=\"window.open('help.php?cmd=BB', 'popupwindow', 'width=960,height=750,scrollbars=yes'); return false;\">BB-kódok</a></td>
	</tr>
	</table>");
	
	print("<hr>");
	
	print("<h2>Naplóba írás</h2><br>A függvény használata: <b>WriteLog(\$micsoda, \$mit)</b><br>A függvény a szükséges naplómélység ellenőrzése után a megadott eseményt a naplóba vési.<br><br><b>\$micsoda</b>: A naplóba írandó esemény típusa<br><b>\$mit</b>: A beírandó esemény paraméterei (a paramétereket <tt>,</tt>-vel (vessző) kell elválasztani)");
	
	print("<hr>");
	
	print("<h2>Meghajtó tárterület</h2><br><b>A függvények használaton kívül szerepelnek a kódbázisban!</b><br>");
	print("<br>A függvények segítségével megtekinthetjük a megadott meghajtó tárterületét<br><br>
	<table border='1' cellspacing='1' cellpadding='1'>
	<tr>
		<td></td>
		<th>Paraméterek</th>
		<th>Visszatérési érték</th>
	</tr>
	<tr>
		<th>DecodeSize</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$bytes</th>
				<td>Az átváltani kívánt méret bájtban</td>
			</tr>
			</table>
		</td>
		<td>A megadott méret átváltva KB, MB, GB, TB-ra</td>
	</tr>
	<tr>
		<th>UresTerulet</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$drive</th>
				<td>A választott meghajtó betűjele</td>
			</tr>
			</table>
		</td>
		<td>A megadott meghajtó üres területe a <tt>DecodeSize()</tt> használatával</td>
	</tr>
	<tr>
		<th>TeljesTerulet</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$drive</th>
				<td>A választott meghajtó betűjele</td>
			</tr>
			</table>
		</td>
		<td>A megadott meghajtó teljes tárterülete a <tt>DecodeSize()</tt> használatával</td>
	</tr>
	<tr>
		<th>HasznaltTerulet</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$drive</th>
				<td>A választott meghajtó betűjele</td>
			</tr>
			</table>
		</td>
		<td>A megadott meghajtó felhasznált (foglalt) tárterülete a <tt>DecodeSize()</tt> használatával</td>
	</tr>
	<tr>
		<th>Terulet</th>
		<td>
			<table border='0' cellspacing='1' cellpadding='2.5'>
			<tr>
				<th>\$drive</th>
				<td>A választott meghajtó betűjele</td>
			</tr>
			</table>
		</td>
		<td><b>Nincs</b><br>A funckió a <tt>print()</tt> parancs használatával a képernyőre írja az meghajtó <tt>UresTerulet()</tt>, <tt>TeljesTerulet()</tt> és <tt>HasznaltTerulet()</tt> adatait.</td>
	</tr>
	<tr>
		<th>OsszTerulet</th>
		<td></td>
		<td><b>Nincs</b><br>Direkt <tt>print()</tt>-tel kiíródik minden, a gépen lévő meghajtó területértéke</td>
	</tr>
	</table>");
	
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
	<tr>
		<td>Idézet</td>
		<td><code>[quote][quoter]idézet eredete[/quoter]idézet szövege[/quote]</code></td>
		<td><code>[quote][quoter]Winston Churchill[/quoter]A siker nem végleges, a kudarc nem végzetes. A bátorság, hogy folytasd, ez az, ami számít! - Winston Churchill[/quote]</code></td>
		<td>" .BBDecode("[quote][quoter]Winston Churchill[/quoter]A siker nem végleges, a kudarc nem végzetes. A bátorság, hogy folytasd, ez az, ami számít! - Winston Churchill[/quote]"). "</td>
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

function SQLHelp()
{
	print("<h2>MySQL-kezelés (\$sql)</h2>
	Az osztály kezeli a MYSQL adatbázist.<br>Az oszály függvényei:<br><br><b>Connect ()</b><br>Kapcsolódás az adatbázisszerverhez<br><pre>Megjegyzés:<br>Az kapcsolódáshoz a <i>config.php</i>-ban megadott adatokat használjuk fel</pre><hr>
	<b>Disconnect()</b><br>Kapcsolat zárása a szerverrel<br><hr>");
	print("<b>Lekerdezes ( \$lekerd, [\$tipus = 'NORMAL'])</b><br>
	<b>\$lekerd</b>: a mysql lekérdezés
	<br><b>\$tipus</b> ['NORMAL'] - a lekérdezés típusa<ul><li><b>NORMAL</b> - szabványos lekérdezés</li><li><b>INSTALL</b> - telepítéssel kapcsolatos lekérdezés (<i>install.php</i> esetén)</li></ul>
	<br>Visszatérő érték: a lekérdezés eredménye a <tt>mysql_query(\$lekerd);</tt> használatával");
}

/* function UserSessionHelp()
{
	print("<h2>Felhasználó (\$user) és munkamenetkezelés (<abbr title='Nem összetévesztendő az \$_SESSION szuperglobállal'>\$session</abbr>)</h2>
	Az osztály segítségével kezelhetünk felhasználói adatokat és munkameneteket.<br>Az osztály függvényei:<br><br><b>func ()</b><br>funcdesc<br><pre>Megjegyzés:<br>funcnote</pre><hr>");
} */
function AddonHelp($tipus)
{
	switch ($tipus)
	{
		case "admin":
			print("<h2>Addonok</h2>
	Ha bővebben érdekel az addonok struktúrája, olvasd el a <a href='help.php?cmd=Addons-developer'>fejlesztőknek szóló</a> leírást is.<br><br>
	Az addonok különböző bővítmények, melyeket a portál keretrendszere tölt be, és jelenít meg a felhasználóknak.<br><b>Az addonokat majdnem minden esetben <u>NEM</u> a portálrendszer fejlesztői írják, ezért mielőtt egy addont telepítesz, győződj meg róla, hogy az addon használata nem jár biztonsági kockázattal.</b><br>Az addonok telepítése előtt mindig készíts <b>biztonsági mentés</b>t a portálrendszer fájlairól, és az adatbázis adatairól!
	<br><br>
	A bővítmények változatos funkciókat tartalmazhatnak, lehetnek megjelenő modulok, funkciógyűjtemények, segédscriptek, stb. Pár dolog azonban közös bennük: a fejlesztők az addonokat a portálrendszerhez írták, és a keretrendszer funkcióit használja, ezért legtöbbször a keretrendszeren kívül futtatva életképtelenek. Az addonok <b>MINDIG</b> csak a /addons mappán belüli, saját maguknak fenntartott mappából futnak, és a portálrendszer osztályaiba nem tölthetik bele magukat. Ezért, ha egy addon a /addons/<i>addon-almappa</i> mappán kívüli (pl. az includes) mappába kíván fájlt tenni, az addonra már gyanúval kell nézni!
	<br><br>
	Az addonok karbantartását az <a href='../admin.php?site=addons'>admin menü</a>ből érheted el. A lista tartalmazza minden telepített addon nevét, almappája nevét, szerzőjét (a szerző nevére kattintva e-mailt küldhetsz a szerzőnek), teljes tárterületigényét (a fájlok méretét), valamint leírását. Ezek mellett minden sorban helyet kap egy <b>Eltávolítás</b> gomb is, melyre kattintva betöltődik az addon telepítőkódjának eltávolító része, és az addon törlődik a rendszerből. Előfordulhat, hogy az eltávolítóscript további adatokat kér, vagy információkat jelenít meg.
	<br>Ha az addon fejlesztői szükségesnek tartják, az addon tartalmazhat egy, a beállításait módosító fájlt is. Ha az addon rendelkezik speciális beállításokkal (melyeket egy fájlba ír), az Eltávolítás gomb mellett megtalálható egy <b>Beállítások</b> gomb is. Erre kattintva betöltődik a beállításokat módosító kód, mely segítségével az addon beállításai módosíthatóak.
	<br><br>
	A lista alatt helyet kap egy <b>Új addon telepítése</b> gomb is, melyre kattintva új addont telepíthetünk. A gombra kattintva először megjelennek a főbb biztonsági intézkedésre felszólító figyelmeztetések, majd meg kell adni az addon almappájának nevét, vagy fel kell tölteni a kötegelt addonfájlt. Ezután betöltődik az addon telepítőscriptje, mely telepíti a kívánt addont. Előfordulhat, hogy a telepítőscript további adatokat kér el.");
			break;
		case "developer":
			print("<h2>Addonok</h2>
	Ha az addonokról szeretnél egy átfogó leírást kapni, olvasd el az <a href='help.php?cmd=Addons-admin'>adminisztrátoroknak/webmestereknek szóló</a> leírást is.<br><br>
	Az addonokat mindig egy külön almappába kell létrehozni, mely mappa a /addons/<i>almappanév</i> elérési úton foglal helyet. Ez a mappa a következő fájlokat tartalmazhatja:<br>
		<dl>
		<dt><b>index.php</b></dt>
			<dd>Nem szükséges fájl, ám ajánlatos az addon fájlait a direkt módon történő elérés (addon mappa megnyitása az elérési útnak kézi begépelésével a böngészőbe) ellen levédeni, például egy átirányítással. A következő szkript az admin menü addon kezelőjébe irányítja át a kíváncsi felhasználót:<br><code>
			header('Location: ../admin.php?site=addons');<br>
			</code></dd>
		<br>
		<dt><b>install.php</b></dt>
			<dd>A portálrendszer telepítő/eltávolító kódja. A fájl két php <span style='color: blue'>function</span>t kell, hogy tartalmazzon, ezeken kívül semmilyen egyéb szöveget (megjegyzéseket kivéve). A telepítéskor az adatbázis <b>addons</b> táblájába kell írni az addon adatait.<br>Átalános struktúrája:<br><code>
			<span style='color: blue'>function</span> Install()<br>
			{<br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkgreen'>/* A portálrendszer telepítőscriptje */</span><br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkblue'>global</span> <span style='color: darkblue'>\$cfg, \$sql</span>;<br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkblue'>\$sql</span><span style='color: purple'>-></span>Lekerdezes(<span style='color: grey'>'INSERT INTO '</span> .<span style='color: darkblue'>\$cfg</span>[<span style='color: grey'>'tbprf'</span>]. <span style='color: grey'>'addons(subdir, name, descr, author, authoremail) VALUES (...)'</span>);<br>
			<br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkgreen'>/* Létrehozunk egy saját modult is */</span><br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkblue'>\$sql</span><span style='color: purple'>-></span>Lekerdezes(<span style='color: grey'>'INSERT INTO '</span> .<span style='color: darkblue'>\$cfg</span>[<span style='color: grey'>'tbprf'</span>]. <span style='color: grey'>'modules(name, type, side) VALUES (...)'</span>);<br>
			}<br>
			<br>
			<span style='color: blue'>function</span> Uninstall()<br>
			{<br>
			&nbsp;&nbsp;&nbsp;<span style='color: darkgreen'>/* A portálrendszer eltávolítóscriptje */</span><br>
			}</code><br>
			A telepítőscriptben található értékek jelentései (<i>addon</i> tábla):<br>
			<dl>
				<dt><b>subdir</b></dt>
					<dd>Az addon almappájának a neve (ha az addonnak lényegtelen milyen almappába települ, lehet dinamikus érték is)</dd>
				<dt><b>name</b></dt>
					<dd>Az addon rövid neve</dd>
				<dt><b>descr</b></dt>
					<dd>Leírás</dd>
				<dt><b>author</b></dt>
					<dd>Szerző neve</dd>
				<dt><b>authoremail</b></dt>
					<dd>Szerző e-mail címe</dd>
			</dl><br>
			A telepítőscriptben található értékek jelentései (<i>modules</i> tábla):<br>
			<dl>
				<dt><b>name</b></dt>
					<dd>A modul hivatkozása az <b>/addons/</b> mappához relatívan, például: <i>sample/samplemodule.php</i></dd>
				<dt><b>type</b></dt>
					<dd>A modul típusa, jelen esetben: <b>addonmodule</b></dd>
				<dt><b>side</b></dt>
					<dd>A megjelenítéshez használt oldalsáv oldala: <b>0</b> - bal, <b>1</b> - jobb</dd>
			</dl>
			<br>Ha az telepítés több lépcsőből épül fel, az addonkezelési keretrendszer támogatásához a <i>HTTP FORM POST</i> segítségével el kell küldeni pár változót, hogy a telepítés ne csússzon át másik addonra. Ezen felül a programozó az script felépítését elhatározása szerint megírhatja. Az eltávolítási rész <b>NEM</b> épülhet fel különböző lépcsőkből.<br>Az átküldéshez szükséges változók egy űrlapban:<br><code>
			&lt;form method='POST' action='admin.php'&gt;<br>
			&nbsp;&nbsp;&nbsp;... további kódok ...<br><br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='site' value='addons'&gt;<br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='action' value='install_script'&gt;<br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='addonsubdir' value='<i>aktuális addon almappa neve</i>'&gt;<br><br>
			&nbsp;&nbsp;&nbsp;... további kódok ...<br>
			&lt;/form&gt;
			</code></dd>
			<br>
		<dt><b>includes.php</b></dt>
			<dd>Ha az addon tartalmaz különböző betöltendő függvénytárakat (az oldalsávban megjelenő modulokat <b>NEM</b> kell ide beírni), ez a fájl a php <span style='color: blue'>include</span><span style='color: grey'>('<i>fájlneve</i>');</span> kódjával betölthető. A fájlra az addon almappájához relatívan kell hivatkozni.</dd>
			<br>
		<dt><b>settings.php</b></dt>
			<dd>Az addon beállításait állító fájl. A beállításokat a <i>HTTP FORM POST</i> használatával kell módosítani, és átküldéskor szükséges a következő változók átadása: <br><code>
			&lt;form method='POST' action='admin.php'&gt;<br>
			&nbsp;&nbsp;&nbsp;... további kódok ...<br><br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='site' value='addons'&gt;<br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='action' value='settings'&gt;<br>
			&nbsp;&nbsp;&nbsp;&lt;input type='hidden' name='id' value=' <span style='color: darkblue'>\$addonid</span> '&gt;<br><br>
			&nbsp;&nbsp;&nbsp;... további kódok ...<br>
			&lt;/form&gt;
			</code><br>Az <span style='color: darkblue'>\$addonid</span> változó a fájl megnyitásakor automatikusan generálódik az addonkezelő által.</dd><br>
		<dt><b>settings.cfg</b></dt>
			<dd>Alapjában véve PHP-nyelvű fájl, azonban ezt sok esetben a <i>settings.php</i>-nek ajánlatos létrehozni. Az addon konfigurációját tartalmazza.</dd><br>
		<dt><b>files.lst</b></dt>
			<dd>Ha az addon a fenti fájlokon kívül tartalmaz más fájlokat is (és általában tartalmaz), akkor azokat ebben a fájlban kell felsorolni, soronként egy-egy fájlt. A fájl szükséges a karbantartási és integritásellenörzési folyamatokhoz.<br><code>
			sample.php<br>
			...</code><br><br>
		<dt>egyéb fájlok</dt>
			<dd>Ezen felül a mappa tartalmazza a további szükséges fájlokat</dd>
	</dl><br>A fejlesztés előtt ajánlott átnézni a portálrendszerrel együtt szállított sample addon fájlait, mely példaként minden szükséges fájlt tartalmaz.<br>Az addon fájljait egy úgynevezett kötegelt addonfájlba (.baa fájl) is lehet konvertálni, és azzal szállítani. Ezen fájlok a webböngészőn keresztül a szerverre feltöltve önkicsomagolódnak, és elindítják saját telepítőjüket.<br>A BAA-fájlokat létrehozó kód <a href='/baacreator/'>itt</a> érhető el.");
			break;
	}
}

switch ($_GET['cmd'])
{
	case "Update":
		UpdateHelp();
		break;
	case "BB":
		BBCodeHelp();
		break;
	case "Functions":
		FunctionsHelp();
		break;
	case "Urlap":
		UrlapHelp();
		break;
	case "SQL":
		SQLHelp();
		break;
	//case "UserSession":
		//UserSessionHelp();
		//break;
	case "Addons-admin":
		AddonHelp('admin');
		break;
	case "Addons-developer":
		AddonHelp('developer');
		break;
	case "adminTools":
		/* Választási űrlap létrehozása */
		
		$forms->StartForm("GET", "self", "Kérlek válassz, melyik adminisztrátori eszközről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából adminisztrátori eszközt", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "A portálrendszer frissítése", "Update");
		$forms->UrlapElem("option", "Addonok", "Addons-admin");
		$forms->UrlapElem("option", "", "adminTools");
		$forms->UrlapElem("option", "<< Vissza a főmenübe", $NULL);
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Tovább >> (Választás megerősítése)");
		$forms->EndForm();
		
		break;
	case "DeveloperTools":
		/* Választási űrlap létrehozása */
		
		$forms->StartForm("GET", "self", "Kérlek válassz, melyik fejlesztői eszközről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából fejlesztői eszközt", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "Funkciók", "Functions");
		$forms->UrlapElem("option", "Űrlapgenerálási osztály", "Urlap");
		$forms->UrlapElem("option", "MySQL-kezelő osztály", "SQL");
		//$forms->UrlapElem("option", "Felhasználó és munkamenetkezelés", "UserSession");
		$forms->UrlapElem("option", "Addonok", "Addons-developer");
		$forms->UrlapElem("option", "", "DeveloperTools");
		$forms->UrlapElem("option", "<< Vissza a főmenübe", $NULL);
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Tovább >> (Választás megerősítése)");
		$forms->EndForm();
		
		break;
	default:
		/* Választási űrlap létrehozása */

		$forms->StartForm("GET", "self", "Kérlek válassz, miről szeretnél információt kapni");
		$forms->UrlapElem("select", "cmd", "", 1, "Kérlek válassz a lenyíló listából", TRUE, "Súgólehetőségek", "Válassz a lenyíló listából, hogy a kívánt súgó oldalára juss");
		$forms->UrlapElem("option", "BB-kódok", "BB");
		$forms->UrlapElem("option", "Adminisztrátori eszközök >>", "adminTools");
		$forms->UrlapElem("option", "Fejlesztői segítségnyújtás >>", "DeveloperTools");
		$forms->UrlapElem("select-end", "", "");
		$forms->UrlapElem("submit", "submit", "Tovább >> (Választás megerősítése)");
		$forms->EndForm();
		
		break;
}
?>