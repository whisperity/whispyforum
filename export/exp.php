<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* export/exp.php
   kód exportáló
*/

/*
   szükséges fájlok:
    * fajllista
*/

 /* A Weboldal megnyitásával a fajllista-ban megjelölt fájlokat kimásoljuk */
 /* A weboldalt NE használjátok éles rendszeres, csak SVN segédeszközként */
 
 include("../includes/functions.php");
 include("../config.php");
 print("<link rel='stylesheet' type='text/css' href='../themes/" .THEME_NAME. "/style.css'>
");
 
 if ( $_GET['diff'] == 1)
 {
	// Fájlok összehasonlítása
	print("<table border='1' cellspacing='1' cellpadding='1' style='width: 100%; height: 95%'>
		<tr style='width: 100%'>
			<th style='width: 100%'>
				Diff
			</th>
		</tr>
		<tr style='width: 100%'>
			<th style='width: 50%'>
				" .$_GET['from']. "
			</th>
			<th style='width: 50%'>
				" .$_GET['to']. "
			</th>
		<tr>
		<tr style='width: 100%'>
			<td style='width: 50%'>
				<textarea style='width: 100%; height: 100%'>
					" .file_get_contents($_GET['from']). "
				</textarea>
			</td>
			<td style='width: 50%'>
				<textarea style='width: 100%; height: 100%'>
					" .file_get_contents($_GET['to']). "
				</textarea>
			</td>
		</tr>
	</table>
");
	
	die(); // Az exportálás ne fusson le újra
 }
 
 $mappaletrehozva = 0;
 $fileletrehozva = 0;
 $forrasmeret = 0;
 $celmeret = 0;
 
 print("<h2><center>WhispyFórum exportáció</center></h2>");
 
 $meghajto = "W:"; // Ide írd be a használt meghajtód betűjelét!
 
 print("<h2>Másolás előtt");
 Terulet($meghajto);
 print("</h2>
");
 
 $maszk = time(); // Mappamaszk
 print("<div class='messagebox'>");
 @mkdir('export_' .$maszk); // Gyökérmappa létrehozása
 $mappaletrehozva++; // +1 mappa
 print("• Gyökérmappa <b>export_" .$maszk. "</b> létrehozva</div>
");

 /* Mappák létrehozása */
 $dataD = @file_get_contents('dir.lst'); // Mappalista bekérése
 if ( $dataD == "" )
 {
	// Ha üres a mappalista
	print("<div class='messagebox'><span class='star'>A mappalista üres!</span></div>");
 } else {
	$sorokD = explode("\r\n", $dataD); // Soronkénti tördelés
	foreach ($sorokD as &$ertekD) { 
		print("<div class='messagebox'>");
		@mkdir('export_' .$maszk. '/' .$ertekD); // Mappa létrehozása
		print("• Mappa <b>export_" .$maksz. "/" .$ertekD. "</b> létrehozva</div>
	");
		$mappaletrehozva++; // +1 mappa
	}
 }
 
 /* Fájlok másolása */
 $dataF = @file_get_contents('fajl.lst'); // Fájllista bekérése
 if ( $dataF == "" )
 {
	// Ha üres a fájllista
	print("<div class='messagebox'><span class='star'>A fájllista üres!</span></div>");
 } else {
	$sorokF = explode("\r\n", $dataF);
	foreach ($sorokF as &$ertekF) { // Soronkénti értelmezés
		$sorF = explode(',', $ertekF); // A sorokat szétvagdossuk a ,-k (vesszők) mentén
		
		$forras = $sorF[0]; // Forrásfájl URL
		$a = explode("../", $sorF[0]); // a ../ levágása
		$cel = 'export_' .$maszk.'/' .$a[1]; // Cél URL
		
		print("
	<div class='messagebox'>");
		$aktualis = @file_get_contents($forras); // Fájl bekérése
		file_put_contents($cel, $aktualis); // Fájl kiírása az új helyre
		print("• <b>" .$forras. "</b> (" .DecodeSize(@filesize($forras)). ") --->> <b>/" .$a[1]. "</b> (" .DecodeSize(@filesize($cel)). ") <sup><a href='exp.php?diff=1&from=" .$forras. "&to=" .$cel. "'>megnyitás</a></sup>");
		print("</div>
");
		$forrasmeret += @filesize($forras); // Össz forrásméret hozzáadva
		$celmeret += @filesize($cel); // Össz célméret hozzáadva
		$fileletrehozva++; // +1 fájl
	}
}

 print("<div class='messagebox'>Az exportálás befejőzödtt!<br><b>" .$mappaletrehozva. "</b> mappa került létrehozásra, összesen <b>" .$fileletrehozva. "</b> fájlt másoltunk át. Az átmásolt fájlok forrásának mérete <b>" .DecodeSize($forrasmeret). "</b> volt, az új méret pedig <b>" .DecodeSize($celmeret). "</b>-tal egyenlő.<br>A fájlaidat az <b>export_" .$maszk. "</b> mappában találod meg.</div>");
 print("
<h2>Másolás után");
 Terulet($meghajto);
 print("</h2>");
?>