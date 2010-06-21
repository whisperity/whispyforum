<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* admin/addforum.php
   fórum hozzáadása
*/

if ( $admin == 1)
{
// Script megjelenítése csak akkor, ha admin menüből hívjuk meg
?>
<center><h2 class='header'>Fórum hozzáadása</h2></center>
<?php

if ($_POST['cmd'] == "add") // Ha a bejövő parancs "ADD" (fórum hozzáadása)
{
	if ($_POST['name'] == $NULL)
	{
		// Ha nincs megadva név (ami kötelező mező)
		print("<span class='star'>Nem töltöttél ki néhány szükséges mezőt!</span><br>");
	} else {
		// Fórum hozzáadása
		//var_dump($_POST);
		
		$sikeres = TRUE; // Alapból sikerrel indulunk
		
		$sorlekerdezes = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']. "forum");
		while ($sor = mysql_fetch_array($sorlekerdezes, MYSQL_ASSOC)) {
			if ( $_POST['name'] == $sor['name'] )
			{
				echo "<span class='star'>A megadott nevű fórum már létezik!</span> Kérlek válassz másik nevet!<br>";
				$sikeres = FALSE;
			}
		}
		
		if ($sikeres == TRUE)
		{
			// Ha sikeres volt a hozzáadás (ha létezik ilyen fórum, a hozzáadás sikertelennek minősül)
			
			$lekerd = $sql->Lekerdezes("INSERT INTO " .$cfg['tbprf']."forum(name, description, lastpostdate) VALUES ('" .$_POST['name']. "', '" .$_POST['description']. "', '0')"); // Fórum hozzáadása az adatbázishoz
			
			/* Lehetőség azonnal új post létrehozására */
			$szam = mysql_num_rows($sorlekerdezes); // Sorok száma (az új sor miatt +1-gyel használjuk)
			print("<div class='messagebox'>A fórum sikeresen hozzáadásra került!<br><a href='viewforum.php'>Fórumok megtekintése</a><br><a href='newtopic.php?id=" .($szam+1). "'>Új téma hozzáadása a fórumhoz</a></div>"); // MessageBox stílusú <div>-be nyomtatunk
			
			print("</td><td class='right' valign='top'>"); // Dobozzárás
			Lablec(); // Lábléc létrehozása
			die(); // A lenti kódoknak már nem kell lefutnia
		}
	}
}

// Itt nem muszály else-be tenni, mert ha a név megvan adva, létrehozzuk a fórumot, láblécezünk és die();

/* Beírunk két alapértelmezett értéket */
if ( $_POST['name'] == $NULL )
{
	$_POST['name'] = "Új fórum";
}

if ( $_POST['description'] == $NULL )
{
	$_POST['description'] = "Ez egy új fórum, melyet " .$_SESSION['username']. " hozott létre az admin menü segítségével.\nA pontos idő " .Datum("normal","kisbetu","dL","H","i","s"). ".";
}


print("A modul segítségével hozzáadhatsz egy új fórumot. A <span class='star'>*</span>-nal jelölt mezők kitöltése kötelező!
<form action='" .$_SERVER['PHP_SELF']. "' method='POST'>
<p class='formText'>Fórum neve<a class='feature-extra'><span class='hover'><span class='h3'><center><span class='star'>*</span> Kötelezően kitöltendő mező <span class='star'>*</span></center></span>Ezt a mezőt kötelező kitölteni, kitöltése nélkül az űrlap érvénytelenül lesz beadva.</span><span class='star'>*</span></a>: <input type='text' name='name' value='" .$_POST['name']. "'><br>
Leírás: <textarea cols='40' rows='15' name='description'>" .$_POST['description']. "</textarea></p>
<input type='hidden' name='cmd' value='add'>
<input type='hidden' name='site' value='addforum'>
<input type='submit' value='Fórum hozzáadása'>
</form>"); // Információ, űrlap

print("</td><td class='right' valign='top'>"); // Középső doboz zárása, jobboldali üres doboz elhelyezése (td.right-ot az admin.php zárja)
} else {
	// Ha nem admin menüből hívódik meg, felhasználó átirányítása az admin menübe
	header("Location: /admin.php?site=addforum");
}
?>