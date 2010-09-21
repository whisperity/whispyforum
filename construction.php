<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* construction.php
   karbantartási betöltő lap
*/
 
 include('includes/common.php'); // Betöltjük a portálrendszer alapscriptjeit (common.php elvégzi)
 Inicialize('construction.php');
 SetTitle("Karbantartás alatt");
 
 $wf_debug->RegisterDLEvent("Karbantartási mód");
 
 $szoveg = "A weboldal karbantartás alatt van!<br>Adminisztrátoroknak engedélyezett a belépés. Ha adminisztrátor vagy, kérlek használd a bejelentkező űrlapot!<br>Hamarosan visszatérünk, megértéseteket kérjük, köszönjük!"; // Bevezető szöveg
	
 if ( ( CONSTRUCTION_MESSAGE != $NULL ) && ( CONSTRUCTION_MESSAGE != "" ) && ( CONSTRUCTION_MESSAGE_USERID != 0 ) ) // Ha van megadva egyéni adminisztrátori komment, megjelenítjük
 {
	$felhasznalonev = mysql_fetch_row($sql->Lekerdezes("SELECT username FROM " .$cfg['tbprf']."user WHERE id='" .mysql_real_escape_string(CONSTRUCTION_MESSAGE_USERID). "'"));
	$szoveg.= "<br><br><b>" .$felhasznalonev[0]. "</b> üzenete:<br>" .CONSTRUCTION_MESSAGE;
 }
 
 if ( CONSTRUCTION == 1 ) // Ha be van kapcsolva a karbantartás, kiírjuk a szöveget
 {
	Hibauzenet("CONSTRUCTION", "Karbantartás alatt!", $szoveg);
 } else { // Egyébként a kíváncsiaknak információt jelenítünk meg
	Hibauzenet("WARNING", "Nincs karbantartás alatt", "A most megnyitott weblap az üdvözlőoldal mindenki részére, ha az oldalt az adminisztrátorok karbantartási módba állítják.<br>Jelen állapotban azonban az oldal rendesen fut, ezért kérlek, válassz egy másik lapot a menükből!");
 }
 
 if ( ( $_SESSION['userLevel'] == 3 ) && ( CONSTRUCTION == 0 ) ) // Azonban ha nincsen karbantartás, de a belépett felhasználó admin, megjelenítjük, milyen lenne a szöveg, ha lenne karbantartás.
	Hibauzenet("CONSTRUCTION", "Karbantartás alatt!", "<h4>Karbantartás alatt ez az üzenet fogadna minden látogatót!</h4>" .$szoveg);
 
 DoFooter();
?>