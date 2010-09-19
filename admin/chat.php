<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/chat.php
   chat műveletek
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Chat</h2></center>
<?php

if ( $_POST['command'] != $NULL )
{
	switch ( $_POST['command'] ) {
		case "truncate":
			$wf_debug->RegisterDLEvent("Chat törlése");
			
			$sql->Lekerdezes("DELETE FROM " .$cfg['tbprf']."chat");
			
			ReturnTo("A chat törölve. Minden hozzászólás el lett távolítva", "admin.php?site=chat", "Vissza a chat opciókhoz", TRUE);
			// Nem fut le a további kód
			print("</td><td class='right' valign='top'>");
			Lablec();
			die();
			
			break;
	}
}

$sorok = $sql->Lekerdezes("SELECT id FROM " .$cfg['tbprf']."chat");

print("A Chatet ebből a menüpontból tudod vezérelni.<br><br>Jelenlegi chat hozzászólások száma: " .mysql_num_rows($sorok). ".<br>Egyszerre maximálisan megengedhető hozzászólások száma: 20.<br>Fennmaradó sorok száma: " .(20-mysql_num_rows($sorok)). "<br>
<input type='checkbox'");

if ( (20-mysql_num_rows($sorok)) <= 0 )
	print(" checked='1'");

print(" disabled>Új hozzászólás esetén a régi hozzászólás törlése, ha a hozzászólások száma több lenne, mint 20.
<br><br>A chat aktuális tartalma:<br>");

include('includes/chatsystem_scr.php');
Chat_LoadModuleAdmin();

print("<br><form method='POST' action='" .$_SERVER['PHP_SELF']. "'>
<input type='hidden' name='site' value='chat'>
<input type='hidden' name='command' value='truncate'>
<input type='submit' value='Chat ürítése'>
<a class='feature-extra'><span class='hover'><span class='h3'>Chat ürítése</span>A Chat ürítésével törlődik a chat aktuális tartalma.<br>Ezen opció használata nem szükséges, a chat folyamatosan törli önmagát, ha a hozzászólások száma meghaladja az egyszerre megengedhetőt.</span><sup>?</sup></a>
</form>");

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=chat");
}
?>