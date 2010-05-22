<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* Help.php
   output leírásokat tartalmazó script
*/
 
function DatumHelp()
 {
	print("<h2>Dátum</h2><br>A függvény használata: <b>Datum(év,hónap,nap)</b><br>Év:<ul><li><b>normal</b> - normál megjelenítés (Y, pl. " .Datum("normal","",""). ")</li></ul>");
	print("Hónap:<ul><li><b>n</b> - hónap száma (0-k nélkül, pl: " .Datum("","n",""). ")</li><li><b>m</b> - hónap száma (nullákkal, pl: " .Datum("","m",""). ")</li><li><b>kisbetu</b> - hónap neve kisbetűvel (pl: " .Datum("","kisbetu",""). ")</li><li><b>nagybetu</b> - hónap neve nagybetűvel (pl. " .Datum("","nagybetu",""). ")</li></ul>");
	print("Nap:<ul><li><b>d</b> - nap száma vezető nullákkal (pl: " .Datum("","","d"). ")</li><li><b>j</b> - nap száma vezetőnullák nélkül (pl: " .Datum("","","j"). ")</li><li><b>l</b> - a nap neve kisbetűvel (pl: " .Datum("","","l"). ")</li><li><b>L</b> - a nap neve nagybetűvel (pl: " .Datum("","","L"). ")</li><li><b>dl</b> - a nap száma és neve kisbetűvel (pl: " .Datum("","","dl"). ")</li><li><b>dL</b> - a nap száma és neve nagybetűvel (pl: " .Datum("","","dL"). ")</li></ul>");
	print('Az értétek STRING típusúak!<br>Például, hogy ezt a dátumot kapjuk: <i>' .Datum('normal','kisbetu','dL'). '</i> , a következőt kell beírni: <b>Datum("normal","kisbetu","dL")</b>');
 }
 
function HibauzenetHelp()
{
	print("<h2>Hibaüzenet</h2><br>A függvény használata: <b>Hibauzenet(tipus, cim, leiras, fajl, sor)</b><br>Típus:<ul><li><b>WARNING</b> - figyelmeztetés (sárga felkiáltójel háromszögben</li><li><b>ERROR</b> - hiba (piros körben fehér X)</li><li><b>CRITICAL</b> - hiba, scriptmegszakítással (<i>exit;</i>, piros körben fehér X)</li></ul>");
	print("Cim: a hiba címsorában megjelenő szöveg<br>Leiras: a hiba leírása (részletes szöveg)<br><b>[fajl]</b> a megszakítást okozó fájl neve (egyszerűen postolhatod a fájl nevét a <b>__FILE__</b> beírásával)<br><b>sor</b> a megszakítást okozó fájlban lévő sor száma (egyszerűen postolhatod a sort a <b>__LINE__</b> beírásával");
	print('A következő beírásával: <b>Hibauzenet("CRITICAL", "Próba", "Csak meghalok!", __FILE__, __LINE__)</b> beírásával a következő hibaüzenet kapod:');
	Hibauzenet("CRITICAL", "Próba", "Csak meghalok!", __FILE__, __LINE__);
}
?>