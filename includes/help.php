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

switch ($_GET['cmd'])
{
	case "Update":
		UpdateHelp();
		break;
	case "BB":
		BBCodeHelp();
		break;
}

?>