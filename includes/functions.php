<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* includes/functions.php
   funkciótár
*/
 
function Datum( $ev, $honap, $nap, $ora, $perc, $masodperc, $epoch = '' ) // A megadott formátum alapján egy dátumérték létrehozása
{ 
 if ($epoch == '') // Ha nincs megadva a unix-epoch stíilusú időérték, akkor az aktuális időt mutatjuk
	$epoch = time(); // Ez biztosítja, hogy a függvény képes legyen az adatbázisban tárolt dátumot magyar típussal megjeleníteni
	
 $honapok = array(  // Létrehozzuk a hónapok neveit
  1 => "Január",
  2 => "Február",
  3 => "Március",
  4 => "Április",
  5 => "Május",
  6 => "Június",
  7 => "Július",
  8 => "Augusztus",
  9 => "Szeptember",
  10 => "Október",
  11 => "November",
  12 => "December"
 );
 $honapokKB = array(  // Létrehozzuk a hónapok neveit (kisbetűvel)
  1 => "január",
  2 => "február",
  3 => "március",
  4 => "április",
  5 => "május",
  6 => "június",
  7 => "július",
  8 => "augusztus",
  9 => "szeptember",
  10 => "október",
  11 => "november",
  12 => "december",
  );
 $hetNapjai = array(  // Létrehozzuk a hét napjainak neveit tartalmazó tömböt
  1 => "Hétfő",
  2 => "Kedd",
  3 => "Szerda",
  4 => "Csütörtök",
  5 => "Péntek",
  6 => "Szombat",
  7 => "Vasárnap"
  );
 $hetNapjaiKB = array( // Létrehozzuk a hét napjainak neveit tartalmazó tömböt (kisbetűvel)
  1 => "hétfő",
  2 => "kedd",
  3 => "szerda",
  4 => "csütörtök",
  5 => "péntek",
  6 => "szombat",
  7 => "vasárnap"
  );

 switch ($ev) { // Év
	case "normal": // Normal(?)
		$Aev = date(Y, $epoch) . ". ";
		break;
 }
 switch ($honap) { // Hónap
	case "n": // Vezető nullák nélkül
		$Ahonap = date(n, $epoch).". ";
		break;
	case "m": // Vezető nullákkal
		$Ahonap = date(m, $epoch).". ";
		break;
	case "kisbetu":  // Kisbetűvel
		$Ahonap = $honapokKB[date(n, $epoch)]." ";
		break;
	case "nagybetu": // Nagybetűvel
		$Ahonap = $honapok[date(n, $epoch)]." ";
		break;
 }
 switch ($nap) {
	case "d": // Vezető nullákkal
		$Anap = date(d, $epoch) .".";
		break;
	case "j": // Vezető nullák nélkül
		$Anap = date(j, $epoch) .".";
		break;
	case "l": // Nap neve kisbetűvel
		$Anap = $hetNapjaiKB[date(N, $epoch)];
		break;
	case "L": // Nap neve nagybetűvel
		$Anap = $hetNapjai[date(N, $epoch)];
		break;
	case "dl": // Nap száma vezető nullákkal és neve kisbetűvel
		$Anap = date(d) .". ". $hetNapjaiKB[date(N, $epoch)];
		break;
	case "dL": // Nap száma vezető nullákkal és neve nagybetűvel
		$Anap = date(d) .". ". $hetNapjai[date(N, $epoch)];
		break;
 }
 
 switch ($ora) {
	case "H": // 24 órás formátum, vezetőnullákkal
		$Aora = date(H, $epoch) .":";
		break;
	}
 switch ($perc) {
	case "i": // Vezető nullákkal
		$Aperc = date(i, $epoch);
		break;
	}
	
 switch ($masodperc) {
	case "s": // Vezető nullákkal, kettősponttal
		$Amp = ":". date(s, $epoch);
	}
	
	$visszater = $Aev.$Ahonap.$Anap; // A dátum alapértelmezésben az év-hó-nap formátum
	
	if ( ($Aora != '') || ($Aperc != '') ||($Amp != '') ) { // Ha időpontot is megadtunk, hozzácsapjuk
		
		$visszater = $visszater. " ".$Aora.$Aperc.$Amp;
	}
		
	return $visszater; // A függvény visszaküldi az egymás mellé rakott értékeket
}

/* Meghajtó tárterület */
 function DecodeSize( $bytes ) // Fájlméret dekódolása emberileg értelmezhető formátumba
 {
   $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
   for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
   return( round( $bytes, 2 ) . " " . $types[$i] );
 } 
 
 function UresTerulet($drive)
 {
    return DecodeSize(disk_free_space($drive));
 }
 
 function TeljesTerulet($drive)
 {
    return DecodeSize(disk_total_space($drive));
 }
 
 function HasznaltTerulet($drive)
 {
    return DecodeSize(disk_total_space($drive) - disk_free_space($drive));
 }
 
 function Terulet($drive) // Részletes területinfók a megadott meghajtóra (teljes használt szabad)
 {
	if (disk_total_space($drive) != 0)
	{
		print("<h3>Tárterületadatok " .$drive. "</h3><p>Teljes tárterület: <b>" .TeljesTerulet($drive). "</b><br>Használt terület: <b>" .HasznaltTerulet($drive));
		print("</b><br>Szabad terület: <b>" .UresTerulet($drive). "</b></p>");
	} else {
		print("<h3>A meghajtó " .$drive. " nem érhető el.</h3>");
	}
 }
 
 function OsszTerulet() // Minden meghajtó területének listázása (automatikusan eléri őket)
 {
  for ($i = 67; $i <= 90; $i++)
    {
        $drive = chr($i);
        if (is_dir($drive.':'))
        {
            $freespace             = disk_free_space($drive.':');
            $total_space         = disk_total_space($drive.':');
			$used_space			 = $total_space - $freespace;
            $percentage_free     = $freespace ? @round($freespace / $total_space, 2) * 100 : 0;
            echo $drive.': '.DecodeSize($freespace).' / '.DecodeSize($total_space).' (' .DecodeSize($used_space). ') ['.$percentage_free.'%]<br />';
        }
    }
 }
 /* Meghajtó tárterület kódok vége */
 
 function Hibauzenet( $tipus = 'WARNING', $cim = '', $uzenet = '', $fajl = __FILE__ , $sor = __LINE__ ) // Hibaüzenet generátor
 {
	// Feltöltjük a hibaüzenet tömböt a bekapott adatokkal
	$Hmsg = array(
		'tipus' => $tipus,
		'title' => $cim,
		'desc' => $uzenet,
		'fajl' => $fajl,
		'line' => $sor
	);
	// Majd unseteljük a változókat
	unset($tipus);
	unset($cim);
	unset($uzenet);
	unset($fajl);
	unset($sor);
	
	print("<link rel='stylesheet' type='text/css' href='themes/" .THEME_NAME. "/style.css'>"); // Stíluslap
	
	if ( $Hmsg['title'] == '' )
	{
		Hibauzenet("ERROR", "Nem lett elég paraméter megadva a hibaüzenet generáláshoz", "Nem lett megadva a hiba címsora a hibaüzenet generálásakor a következő helyen: <b>" .$Hmsg['fajl']. "</b> a következő sorban: <b>" .$Hmsg['line']. "</b>");
	} else {
		switch ($Hmsg['tipus']) // Típus alapján betöltjük a kép adatait
		{
			case "WARNING":
				$kepnev = 'warning.png';
				break;
			case "ERROR":
			case "CRITICAL":
				$kepnev = 'error.png';
				break;
			default:
				$kepnev = 'x.bmp';
		}
	
		// Elkezdjük kinyomatni a HTML outputot
		print("<div class='hibabox'><div class='hibakep'><img src='themes/" .THEME_NAME. "/" .$kepnev. "'></div><div class='hibacim'>" .$Hmsg['title']. "</div><div class='hibaszoveg'>" .$Hmsg['desc']. "</div></div>");
		
		// Beleírjuk az aktuális értéket a naplóba
		WriteLog($Hmsg['tipus'], $Hmsg['title']. ',' .$Hmsg['desc']. ',' .$Hmsg['fajl']. ',' .$Hmsg['line']);
		
		if ($Hmsg['tipus'] == "CRITICAL") // Ha kritikus (CRITICAL) a hiba, a futtatás megakad.
			//die("A script futtatása megszakítva a következő helyen: <b>" . $Hmsg['fajl'] . "</b> fájl <b>" . $Hmsg['line'] . ".</b> sora.");
			die("Kritikus hiba miatt a futtatás megszakadt!");
	}
}
 
 function BBDecode( $BBText )
 {
	// A funkció segítségével a BB-kódban tárolt szöveget HTML kóddá alakíthatjuk
	$bbKod = array("[img]","[/img]", "\n","[b]","[/b]","[i]","[/i]","[u]","[/u]","[url]","[/url]", "[/a]", "[quoter]", "[/quoter]", "[quote]", "[/quote]"); // BB kódok listája
	$htmlTag = array("<img src='","'>", "<br>","<b>","</b>","<i>","</i>","<u>","</u>","<a href='","'>","</a>", "<p class='header'>", " mondta:</p>", "<blockquote class='quote'>", "</blockquote>"); // HTML tagek listája
	
	return str_replace($bbKod, $htmlTag, $BBText); // A függvény visszaküldi a html-lé alakított szöveget
 }
 
 function HTMLDestroy( $HTMLText ) // HTML kód eltüntetése a szövegből
 {
	return htmlspecialchars($HTMLText, ENT_QUOTES, 'UTF-8');
 }
 
 function EmoticonParse ( $emotText ) // Hangulatjelek értelmezése
 {
	// A funkció segítségével a szövetként tárolt hangulatjeleket ( :P ) képként tudjuk megjeleníteni
	
	/* Kisbetűk */
	$emote = array(":)", ":(", ":h", ":p", ";)", ":o", ":d", ":confused:", ":neutral:", ":sleep:", ":'(", ":wonder:", ":jawohl:", ":offtopic:", ":spam:", ":wned:", ":banhammer:"); // Emotikon kódok
	$hrefs = array("[img]/themes/" .THEME_NAME. "/emote/smile.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/sad.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/cool.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/tongue.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/wink.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/ohmy.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/grin.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/confused.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/neutral.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/closedeyes.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/cry.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/unsure.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/jawohl.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/offtopic.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/spam.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/owned.gif[/img]", "[img]/themes/" .THEME_NAME. "/emote/banhammer.gif[/img]"); // Direktlinkek
	
	$kisbetu = str_replace($emote, $hrefs, $emotText);
	
	/* Nagybetűk */
	$emoteN = array(":)", ":(", ":H", ":P", ";)", ":O", ":D", ":CONFUSED:", ":NEUTRAL:", ":SLEEP:", ":'(", ":WONDER:", ":JAWOHL:", ":OFFTOPIC:", ":SPAM:", ":WNED:", ":BANHAMMER:");  // Emotikon kódok
	
	return str_replace($emoteN, $hrefs, $kisbetu);
 }
 
 function WriteLog( $micsoda = '', $mit = '' ) // Napló írása
 {
  if ( LOG_DEPTH > 0 )
  {
	if (! (file_exists('logs/site.log')))
	{
		// Ha nem létezik a napló fájl, létrehozunk egyet
		file_put_contents('logs/site.log',time(). ',LOG_CREATE');
	}
	
	// Beleírjuk az aktuális értéket a naplóba
	// naplómélységi adattól függően
	switch ( $micsoda )
	{
		case "WARNING":
		case "ERROR":
		case "CRITICAL":
			if ( LOG_DEPTH >= 1 )
				file_put_contents('logs/site.log', "\n" .time(). ',' .$micsoda. ',' .$mit, FILE_APPEND);
			break;
		case "USR_REGISTERED_SUCCESSFULLY":
		case "USR_ACTIVATE":
			if ( LOG_DEPTH >= 2 )
				file_put_contents('logs/site.log', "\n" .time(). ',' .$micsoda. ',' .$mit, FILE_APPEND);
			break;
		case "PAGE_VIEW":
			if ( LOG_DEPTH >= 3 )
				file_put_contents('logs/site.log', "\n" .time(). ',' .$micsoda. ',' .$mit, FILE_APPEND);
			break;
		case "SQL_CONNECT_SELECTDB":
		case "SQL_DC":
			if ( LOG_DEPTH >= 4)
				file_put_contents('logs/site.log', "\n" .time(). ',' .$micsoda, FILE_APPEND);
			break;
		case "SQL":
			if ( LOG_DEPTH >= 4)
				file_put_contents('logs/site.log', "\n" .time(). ',' .$micsoda. ',' .$mit, FILE_APPEND);
			break;
	}
  }
 }
?>