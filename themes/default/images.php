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
<title>Témához elérhetõ képek listázása</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="index.php"><< Megjelenés</a>
<a href="emoticons.php">Hangulatjelek >></a>
<br>
<table border="1">
	<tr>
		<th></th>
		<th>Kép</th>
		<th>Fájlnév</th>
		<th>Fájlméret</th>
		<th>Leírás</th>
	</tr>

<?php
	function RajzKep($kepNum, $kepName, $leiras = '')
	{
		
		print("
		<tr>
			<td>" .$kepNum. "</td>
			<td><img src='" .$kepName. "'></td>
			<td><a href='" .$kepName. "'>" .$kepName. "</a></td>
			<td>" .DecodeSize(@filesize($kepName)). "</td>
			<td>" .$leiras. "</td>
		</tr>"); // Egy kép kiírása-rajzolása
	}
	
	// Képlista kinyerése a leírófájlból
	$data = @file_get_contents('images.lst');
	$sorok = explode("\r\n", $data);
	
	foreach ($sorok as &$sor)
	{
		$kep = explode(",", $sor);
		
		if ( $kep[0] != "")
			RajzKep($i, $kep[0], $kep[1]);
	}
	
	
// Zárás
?>
</table>
	
</body>

</html>