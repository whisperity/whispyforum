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
 $sorokD = explode("\r\n", $dataD); // Soronkénti tördelés
 foreach ($sorokD as &$ertekD) { 
	print("<div class='messagebox'>");
	@mkdir('export_' .$maszk. '/' .$ertekD); // Mappa létrehozása
	print("• Mappa <b>export_" .$maksz. "/" .$ertekD. "</b> létrehozva</div>
");
	$mappaletrehozva++; // +1 mappa
 }
 
 /* Fájlok másolása */
 $dataF = @file_get_contents('fajl.lst'); // Fájllista bekérése
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
	print("• <b>" .$forras. "</b> (" .DecodeSize(@filesize($forras)). ") --->> <b>/" .$a[1]. "</b> (" .DecodeSize(@filesize($cel)). ")");
	print("</div>
");
	$forrasmeret += @filesize($forras); // Össz forrásméret hozzáadva
	$celmeret += @filesize($cel); // Össz célméret hozzáadva
	$fileletrehozva++; // +1 fájl
}

 print("<div class='messagebox'>Az exportálás befejőzödtt!<br><b>" .$mappaletrehozva. "</b> mappa került létrehozásra, összesen <b>" .$fileletrehozva. "</b> fájlt másoltunk át. Az átmásolt fájlok forrásának mérete <b>" .DecodeSize($forrasmeret). "</b> volt, az új méret pedig <b>" .DecodeSize($celmeret). "</b>-tal egyenlő.<br>A fájlaidat az <b>export_" .$maszk. "</b> mappában találod meg.</div>");
 print("
<h2>Másolás után");
 Terulet($meghajto);
 print("</h2>");
?>