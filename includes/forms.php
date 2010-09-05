<?php 
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/forms.php
   űrlap osztály
*/
class forms // Definiáljuk az osztályt
{
 function StartForm ( $method = "GET", $action = "self", $header = "" )
 {
	// Űrlap létrehozása
	
	if ( $action == "self" ) // Ha nem adunk meg értéket action-ként, a function alapparaméterét (self) átírjuk
	// A weblap nevére ( $action = $_SERVER['PHP_SELF'] hibát okozott)
		$action = $_SERVER['PHP_SELF'];
	
	if ($method == "NO")
	{
		// Lehetőségünk van NO methoddal formot készíteni, ilyenkor nem történik elküldés
		print("<form>");
	} else {
		print("<form action='" .$action. "' method='" .$method. "'>
");
	}
	
	if ( $header != "" )
	{
		// Ha van beállított fejléc, kiíratjuk azt is
		print("<span class='formHeader'>" .$header. "</span>
");
	}
 }
 
 function EndForm ()
 {
	// Űrlap zárása
	print("
</form>");
 }
 
 function Urlapelem ( $tipus, $nev, $ertek = "", $size = 25, $szoveg = "", $kotelezo = FALSE, $aHeader = "", $aText = "")
 {
	/* Ha kötelező adat hiányzik, értesítjük a felhasználót */
	if ( ($tipus == "") || ($nev == "") )
	{
		print("<center>Hiányoznak kötelező beviteli mező adatok</center>");
	}
	
	// Új űrlapelem hozzáadása az űrlaphoz
	
	/* Első lépésben a beviteli mező előtti szöveget írjuk ki */
	if ( $szoveg != "" )
	{
		print("<p class='formText'>" .$szoveg);
		
		/* Ha van felbukkanó szöveg, kitesszük */
		if ( ($aHeader != "") && ($aText != "") )
		{
			print("<a class='feature-extra'><span class='hover'><span class='h3'><center>" .$aHeader. "</center></span>" .$aText. "</span><sup>?</sup></a>");
		}
		
		/* Ha kötelező mező, a kötelezőséget mutató csillagot is kitesszük */
		if ( $kotelezo == TRUE )
		{
			print("<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>");
		}
		print(": ");
	}
	
	if ( $ertek == "post-get" )
	{
		// Ha az alapértelmezett értéket adjuk meg (vagy a paramétert elhagyjuk)
		// Az űrlapmező alap érték a POST-tal vagy GET-tel érkező, az űrlapmező előző értéke lesz
		// Hasznos pl. akkor, ha egy kötelező mezőt üresen hagytak, hogy megkíméljük a felhasználót a többi adat újragépelésétől
		if ( $_POST[$nev] != $NULL )
		{
			// Ha post-tal jött érték
			$ertek = $_POST[$nev];
		} else {
			// Ha nem post-tal jött, akkor vagy GET-tel jön, vagy nem jön
			if ( $_GET[$nev] != $NULL )
			{
				// GET-tel jön
				$ertek = $_GET[$nev];
			} else {
				// Sehogy nem jön
				$ertek = "";
			}
		}
	}
	
	/* Az űrlapmező létrehozása */
	switch ($tipus) // Típus alapján választunk
	{
		case "text":
		case "password":
			print("<input type='" .$tipus. "' name='" .$nev. "' value='" .$ertek. "' size='" .$size. "'>");
			break;
		case "hidden":
			print("<input type='" .$tipus. "' name='" .$nev. "' value='" .$ertek. "'>");
			break;
		case "submit":
			print("<input type='" .$tipus. "' value='" .$ertek. "'>");
			break;
		case "select":
			print("<select size='" .$size. "' name='" .$nev. "'>");
			break;
		case "option":
			print("<option value='" .$ertek. "'>" .$nev. "</option>");
			break;
		case "select-end":
			print("</select>");
			break;
		default:
			print("</p><center>Ismeretlen típusú beviteli mező: <b>" .$tipus. "</b> (a neve <i>" .$nev. "</i>, értéke <i>" .$ertek. "</i> lett volna)</center><p class='formText'>");
			break;
	}
	
	print("</p>
");
 }
}

 // Létrehozzuk a globális $forms változót
 // mellyel meghívhatjuk az osztály függvényeit
 global $forms;
 $forms = new forms();
?>