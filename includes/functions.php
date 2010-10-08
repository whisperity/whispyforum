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
		$Anap = date(d, $epoch) .". ". $hetNapjaiKB[date(N, $epoch)];
		break;
	case "dL": // Nap száma vezető nullákkal és neve nagybetűvel
		$Anap = date(d, $epoch) .". ". $hetNapjai[date(N, $epoch)];
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
	
	print("<link rel='stylesheet' type='text/css' href='themes/" .$_SESSION['themeName']. "/style.css'>"); // Stíluslap
	
	if ( $Hmsg['title'] == '' )
	{
		Hibauzenet("ERROR", "Nem lett elég paraméter megadva a hibaüzenet generáláshoz", "Nem lett megadva a hiba címsora a hibaüzenet generálásakor a következő helyen: <b>" .$Hmsg['fajl']. "</b> a következő sorban: <b>" .$Hmsg['line']. "</b>");
	} else {
		switch ($Hmsg['tipus']) // Típus alapján betöltjük a kép adatait
		{
			case "WARNING":
				$kepnev = 'warning.png';
				$kepalt = 'Figyelmeztetés';
				break;
			case "ERROR":
				$kepnev = 'error.png';
				$kepalt = 'Hiba';
				break;
			case "CRITICAL":
				$kepnev = 'error.png';
				$kepalt = 'Kritikus hiba';
				break;
			case "BAN":
				$kepnev = 'stop_hand.png';
				$kepalt = 'Tiltás';
				break;
			case "CONSTRUCTION":
				$kepnev = 'construction.png';
				$kepalt = 'Karbantartás alatt';
				break;
			default:
				$kepnev = 'x.bmp';
		}
	
		// Elkezdjük kinyomatni a HTML outputot
		print("<div class='hibabox'><div class='hibakep'>");
		
		if ( $_SESSION['theme'] == $NULL )
		{
			print("<img src='themes/default/" .$kepnev. "' alt='" .$kepalt. "'>");
		} else {
			print("<img src='themes/" .$_SESSION['themeName']. "/" .$kepnev. "' alt='" .$kepalt. "'>");
		}
		
		print("</div><div class='hibacim'>" .$Hmsg['title']. "</div><div class='hibaszoveg'>" .$Hmsg['desc']. "</div></div>");
			
		//if ($Hmsg['tipus'] == "CRITICAL") // Ha kritikus (CRITICAL) a hiba, a futtatás megakad.
			//die("A script futtatása megszakítva a következő helyen: <b>" . $Hmsg['fajl'] . "</b> fájl <b>" . $Hmsg['line'] . ".</b> sora.");
			//die("Kritikus hiba miatt a futtatás megszakadt!");
	}
}
 
 function BBDecode( $BBText )
 {
	// A funkció segítségével a BB-kódban tárolt szöveget HTML kóddá alakíthatjuk
	$bbKod = array("[img]","[/img]", "\n","[b]","[/b]","[i]","[/i]","[u]","[/u]","[url]","[/url]", "[/a]", "[quoter]", "[/quoter]", "[quote]", "[/quote]", "[h]", "[/h]"); // BB kódok listája
	$htmlTag = array("<img src='","'>", "<br>","<b>","</b>","<i>","</i>","<u>","</u>","<a href='","'>","</a>", "<p class='header'>", " mondta:</p>", "<blockquote class='quote'>", "</blockquote>", "<h4>", "</h4>"); // HTML tagek listája
	
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
	$emote = array(":owned:", ":offtopic:", ":)", ":(", ":h", ":p", ";)", ":o", ":d", ":confused:", ":neutral:", ":sleep:", ":'(", ":wonder:", ":jawohl:", ":spam:", ":banhammer:", "(l)", ":s"); // Emotikon kódok
	$hrefs = array("[img]/themes/" .$_SESSION['themeName']. "/emote/owned.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/offtopic.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/smile.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/sad.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/cool.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/tongue.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/wink.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/ohmy.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/grin.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/confused.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/neutral.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/closedeyes.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/cry.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/unsure.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/jawohl.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/spam.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/banhammer.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/love.gif[/img]", "[img]/themes/" .$_SESSION['themeName']. "/emote/confused_smile.gif[/img]"); // Direktlinkek
	
	$kisbetu = str_replace($emote, $hrefs, $emotText);
	
	/* Nagybetűk */
	$emoteN = array(":OWNED:", ":OFFTOPIC:", ":)", ":(", ":H", ":P", ";)", ":O", ":D", ":CONFUSED:", ":NEUTRAL:", ":SLEEP:", ":'(", ":WONDER:", ":JAWOHL:", ":SPAM:", ":BANHAMMER:", "(L)", ":S");  // Emotikon kódok
	
	return str_replace($emoteN, $hrefs, $kisbetu);
 }
 
 function ReturnTo($text, $href = '', $hreftext = '', $metareturn = FALSE ) // Visszatérési üzenetdoboz generálása
 {
	// $text: a megjelenő sablon szövege
	// $href: visszatérési link (ha van, megjelenik egy visszatérési link)
	// $hreftext: a visszatérési link szövege (ha van, megjelenik egy visszatérési link)
	// $metareturn: ha TRUE, akkor HTTP META átirányítással pár másodperc után automatikusan visszatérünk
	
	print("<div class='messagebox'>" .$text); // Kiírjuk a doboz szövegét
	
	if ( ($href != '') && ($hreftext != '') ) // Ha href és hreftext is rendelkezik értékkel
	{
		print("<br><a href='" .$href. "'>" .$hreftext. "</a>"); // Visszatérő link kiíratása
	}
	
	if ( ($href == '') && ($hreftext != '') ) // Ha href-nek nincs értéke, de hreftextnek van
	{
		Hibauzenet("WARNING", "A visszatérő sablon érvénytelen paraméterekkel rendelkezik", "A visszatérő sablonban a visszatérési linkje a következő szöveget viselte volna: <tt>" .$hreftext. "</tt>, ám a visszatérési hivatkozás érvénytelen (üres)");
	}
	
	if ( ($href != '') && ($hreftext == '') ) // Ha href-nek van értéke, de hreftextnek nincs
	{
		Hibauzenet("WARNING", "A visszatérő sablon érvénytelen paraméterekkel rendelkezik", "A visszatérő sablonban a visszatérési linkje a következő helyre irányította volna a felhasználót: <tt><a href='" .$href. "'>" .$href. "</a></tt>, de a visszatérő link nem kapott szöveget!");
	}
		
	if ( ($href != '') && ($hreftext != '') && ( $metareturn == TRUE) ) // Ha van visszatérési link, szöveg, és a metareturn igaz
	{
		if ( SHOWDEBUG == 0 )
		{
			echo '<meta http-equiv="refresh" content="3;url=' .$cfg['phost']. '/' .$href. '" />'; // META visszatérési parancsot küldünk, mely a böngészőt 3 sec után automatikusan átirányítja
		}
		print("&nbsp;<small><span id='dotdotdots'></span>"); // dotdotdots = visszaszámlálás
		print("<span id='returnmsg'></span></small>"); // returnmsg = 0 visszaszámlálás értéknél bónusz üzenet
		
		if ( SHOWDEBUG == 1 )
		{
			print("<small>3 másodperc...<br>2 másodperc..<br>1 másodperc..<br>META-átirányítás folyamatban...<br><br>ReturnTo() folyamat megszakítva a hibakeresés bekapcsoltsága miatt</small>");
		}
		
		if ( SHOWDEBUG == 0 ) {
		?>
		<SCRIPT language=javascript>
function GetObject(name)
{
	var o=null;
	if(document.getElementById)
		o=document.getElementById(name);
	else if(document.all)
		o=document.all.item(name);
	else if(document.layers)
		o=document.layers[name];
	if (o==null && document.getElementsByName)
	{
		var e=document.getElementsByName(name);
		if (e.length==1) o=e[0];
	}
	return o;
}
function rvl(n)
{
	if (n==51)
	{
		var o2=GetObject("returnmsg");
		if (o2) o2.innerHTML="<br>META-átirányítás folyamatban...";
		setTimeout("document.location=document.location;",1000);
	}
	else
	{
		var o=GetObject("dotdotdots");
		if (o) o.innerHTML="3 másodperc...<br>2 másodperc...<br>1 másodperc...".substr(0,n);
		setTimeout("rvl("+(n+1)+");",45);
	}
}
rvl(0);
</SCRIPT>
	<?php
	}
	}
	
	print("</div>");
 }
?>