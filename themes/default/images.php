<?php
header('Content-type: text/html; charset=iso-8859-2'); // Szükséges

 function DecodeSize( $bytes ) // Fájlméret dekódolása emberileg értelmezhetõ formátumba
 {
   $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
   for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
   return( round( $bytes, 2 ) . " " . $types[$i] );
 } 

// Fejléc, táblázatkezdés rajzolása
?>

<html>

<head> 
<title>CSS bemutató oldal</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="index.php"><< Megjelenés</a>
<br>
<table>
	<tr>
		<th>Kép</th>
		<th>Fájlnév</th>
		<th>Fájlméret</th>
		<th>Leírás</th>
	</tr>

<?php
	function RajzKep($kepName, $leiras = '')
	{
		
		print("
		<tr>
			<td><img src='" .$kepName. "'></td>
			<td><a href='" .$kepName. "'>" .$kepName. "</a></td>
			<td>" .DecodeSize(filesize($kepName)). "</td>
			<td>" .$leiras. "</td>
		</tr>"); // Egy kép kiírása-rajzolása
	}
	
	RajzKep('warning.png', 'Hibaüzenet ablakban figyelmeztetés szimbólum');
	RajzKep('error.png', 'Hibaüzenet ablakban hiba/kritikus hiba szimbólum');
	RajzKep('x.bmp', 'Üres semmi');
	RajzKep('lastpost.gif', 'Fórumban az utolsó hozzászóláshoz ugrás linkjének képe');
// Zárás
?>
</table>
</body>

</html>