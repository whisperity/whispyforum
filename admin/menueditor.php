<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/menueditor.php
   menüszerkesztő
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Menüszerkesztő</h2></center>
<?php

if ( $_POST['action'] != $NULL )
 {
	// Ha POST-tal érkeznek az adatok, a POST site lesz az érték
	$action = $_POST['action'];
 } else {
	// Ha nem post, akkor vagy GET-tel jött az adat, vagy sehogy
	if ( $_GET['action'] != $NULL )
	{
		// Ha gettel érkezik, az lesz az érték
		$action = $_GET['action'];
	} else {
		// Sehogy nem érkezett adat
		$action = $NULL;
	}
 }

switch ( $action ) // A bejövő ACTION paraméter szerint nézzük, mi történjen
{
	/* Alapeset */
	case $NULL:
	case "":
		print("A menüszerkesztő eszköz segít neked a weboldalon található menüket szerkeszteni!<br>Első lépésként kérlek válassz az alábbi menük közül");
		print("<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Függőleges helyzet</th>
				<th>Cím/Név</th>
				<th>Típus</th>
				<th>Elhelyezkedés</th>
				
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['hOrder']. "</td>
				<td>" .$sor['name']. "</td>
				<td>");
			
			if ( $sor['type'] == "menu" )
				print("menü");
			if ( $sor['type'] == "addonmodule");
				print("addon-modul");
			
			print("</td>
				<td>");
			
			if ( $sor['side'] == 1)
				print("bal");
			if ( $sor['side'] == 2)
				print("jobb");
			
			print("</td>");
			
			if ( $sor['type'] == "menu")
				print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='menueditor'>
				<input type='hidden' name='action' value='viewitems'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Menüelemek megtekintése'>
			</form></td>");
			
			print("<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='menueditor'>
				<input type='hidden' name='action' value='edit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
			</tr>");
		}
		
		print("</table></div>");
		break;
	
	case "viewitems": // Menüelemek megtekintése
		$menuNev = mysql_fetch_assoc($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."modules WHERE id='" .$_GET['id']. "'"));
		print("A <b>" .$menuNev['name']. "</b> menü elemeinek szerkesztése.<br><br><div class='userbox'><table border='0' cellspacing='1' cellpadding='1'>
			<tr>
				<th>id</th>
				<th>Függőleges elhelyezkedés</th>
				<th>Név</th>
				<th>Hivatkozás</th>				
			</tr>");
		
		$adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuitems WHERE menuId='" .$_GET['id']. "' ORDER BY hOrder");
		while ( $sor = mysql_fetch_assoc($adat) )
		{
			$menuNev = mysql_fetch_assoc($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."modules WHERE id='" .$sor['menuId']. "'"));
			print("<tr>
				<td>" .$sor['id']. "</td>
				<td>" .$sor['hOrder']. "</td>
				<td>" .$sor['text']. "</td>
				<td>" .$sor['href']. "</td>
				<td><form action='" .$_SERVER['PHP_SELF']. "' method='GET'>
				<input type='hidden' name='site' value='menueditor'>
				<input type='hidden' name='action' value='itemedit'>
				<input type='hidden' name='id' value='" .$sor['id']. "'>
				<input type='submit' value='Szerkesztés'>
			</form></td>
					
			</tr>");	
		}
		
		print("</table></div>");
		break;
		
	case "itemedit": // Menüelem szerkesztése
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				// A menü szerkesztésének mentése
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."menuitems SET text='" .$_POST['text']. "', href='" .$_POST['href']. "', hOrder='" .$_POST['hOrder']. "' WHERE id='" .$_POST['id']. "'");
				die("<div class='messagebox'>A menüelem sikeresen szerkesztve!<br><a href='admin.php?site=menueditor'>Vissza a menüszerkesztőhöz</a></td><td class='right' valign='top'>");
			}
			$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuitems WHERE id='" .$_GET['id']. "'"));
			$sor3 = mysql_fetch_assoc($sql->Lekerdezes("SELECT name FROM " .$cfg['tbprf']."modules WHERE id='" .$sor['menuId']. "'"));
			print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Menüelem szerkesztése (" .$sor3['name']. ")</span><br>
		<p class='formText'>Címsor: <input type='text' name='text' value='" .$sor['text']. "' size='96'><br>
		Hivatkozás<a class='feature-extra'><span class='hover'><span class='h3'>Hivatkozás</span><b>Belső hivatkozásnál:</b> A menüelem hivatkozása a weboldal gyökeréhez (" .$cfg['phost']. "/) képest (pl. a kezdőlaphoz <i>index.php</i>).<br><b>Külső hivatkozásnál:</b> A teljes link, a bevezető <b>http://</b>-rel is (pl. <i>http://google.com</i>)</span><sup>?</sup></a>: 
		<input type='text' name='href' value='" .$sor['href']. "'><br>
		Függőleges elhelyezkedés: <input type='text' name='hOrder' value='" .$sor['hOrder']. "' size='3'>\t\t");
		
		/* Egy listába tesszük a szerkesztett modullal megegyező oldalon lévő modulokat (hOrder példa) */
		$oldalmenuk = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."menuitems WHERE menuId='" .$sor['menuId']. "' ORDER BY hOrder");
		$oldalmenuk_szam = mysql_num_rows($oldalmenuk);
		
		print("<br><select size='" .($oldalmenuk_szam+2). "' disabled>");
		while ( $sor2 = mysql_fetch_assoc($oldalmenuk) )
			print("<option>" .$sor2['hOrder'].". " .$sor2['text']. "</option>");
		
		/* Form zárása */
		print("</select><a class='feature-extra'><span class='hover'><span class='h3'>Menüoszlop</span>Ez a kis lista vázlatban tartalmazza a megadott menüben található elemeket a függőleges helyzetük (sorszámuk) azonosítójával. A doboz segítségével egy vázlat tekinthető meg a függőleges helyzetek szerint rendezett (<b>mint ahogy az oldalon megjelenik</b>) menüelemekről, a szerkesztés <b>előtt</b>i állapotból.</span><sup>?</sup></a></p><input type='hidden' name='id' value='" .$sor['id']. "'>
		<input type='hidden' name='action' value='itemedit'>
		<input type='hidden' name='site' value='menueditor'>
		<input type='submit' name='parancs' value='Szerkeszt'>
		</form>");
		}
		break;
		break;
	case "edit": // Szerkesztés
		if ( $_GET['id'] == $NULL )
		{
			Hibauzenet("CRITICAL", "Az id-t kötelező megadni!");
		} else {
			if ( $_POST['parancs'] == "Szerkeszt" )
			{
				// A menü szerkesztésének mentése
				
				$sql->Lekerdezes("UPDATE " .$cfg['tbprf']."modules SET name='" .$_POST['name']. "', type='" .$_POST['type']. "', side='" .$_POST['side']. "', hOrder='" .$_POST['hOrder']. "' WHERE id='" .$_POST['id']. "'");
				die("<div class='messagebox'>A modul sikeresen szerkesztve!<br><a href='admin.php?site=menueditor'>Vissza a menüszerkesztőhöz</a></div></td><td class='right' valign='top'>");
			}
			$sor = mysql_fetch_assoc($sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE id='" .$_GET['id']. "'"));
			
			print("<form method='POST' action='" .$_SEVER['PHP_SELF']. "'>
		<span class='formHeader'>Modul szerkesztése: " .$sor['name']. "</span><br>
		<p class='formText'>Címsor: <input type='text' name='name' value='" .$sor['name']. "' size='96'><br>
		Típus: <input type='radio' name='type' value='menu'");
			if ( $sor['type'] == "menu") // Ha a modul típusa menü, akkor alapból a menü gomb kerül bejelölésre
				print(" checked ");
		
		print("> Menü <input type='radio' name='type' value='addonmodule'");
			if ( $sor['type'] == "addonmodule") // Ha a modul egy addon, a megfelelő gomb lesz bejelölve
			print(" checked ");
		print("> Addon-modul<br>
		Oldal: <input type='radio' name='side' value='1'");
			if ( $sor['side'] == 1)  // Ha a modul bal oldali, a bal oldali gomb kerül bejelölésre
				print(" checked ");
		print("> Bal <input type='radio' name='side' value='2'");
			if ( $sor['side'] == 2)  // Ha jobb oldali, a jobb
				print(" checked ");
		print("> Jobb<br>
		Függőleges elhelyezkedés: <input type='text' name='hOrder' value='" .$sor['hOrder']. "' size='3'>\t\t");
		
		/* Egy listába tesszük a szerkesztett modullal megegyező oldalon lévő modulokat (hOrder példa) */
		$oldalmenuk = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."modules WHERE side='" .$sor['side']. "' ORDER BY hOrder");
		$oldalmenuk_szam = mysql_num_rows($oldalmenuk);
		
		print("<br><select size='" .($oldalmenuk_szam+2). "' disabled>");
		if ( $sor['side'] == 1)
			print("<option>-1. Felhasználói doboz (be/kijelentkezés)");
		
		while ( $sor2 = mysql_fetch_assoc($oldalmenuk) )
			print("<option>" .$sor2['hOrder'].". " .$sor2['name']. "</option>");
		
		/* Form zárása */
		print("</select><a class='feature-extra'><span class='hover'><span class='h3'>Menüoszlop</span>Ez a kis lista vázlatban tartalmazza a ");
		if ( $sor['side'] == 1)
			print("bal");
		if ( $sor['side'] == 2)
			print("jobb");
		
		print(" oldalon lévő modulokat a függőleges helyzetük (sorszámuk) azonosítójával. A doboz segítségével egy vázlat tekinthető meg a függőleges helyzetek szerint rendezett (<b>mint ahogy az oldalon megjelenik</b>) modulokról, a szerkesztés <b>előtt</b>i állapotból.</span><sup>?</sup></a></p><input type='hidden' name='id' value='" .$sor['id']. "'>
		<input type='hidden' name='action' value='edit'>
		<input type='hidden' name='site' value='menueditor'>
		<input type='submit' name='parancs' value='Szerkeszt'>
		</form>");
		}
		break;
}

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=menueditor");
}
?>