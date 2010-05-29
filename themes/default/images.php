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
<br>
<a name="images">
<h2>Képek</h2>
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
	$kepSzam = $sorok[0];
	
	for ($i == 1; $i <= $kepSzam; $i++)
	{
		$kep = explode(",", $sorok[$i]);
		
		if ( $kep[0] != "")
			RajzKep($i, $kep[0], $kep[1]);
	}
	
	
// Zárás
?>
</table>
<a name="emoticons">
<h2>Hangulatjelek (emoteiconok)</h2>
<table border="1">
	<tr>
		<th>Megjelenés</th>
		<th>Kód</th>
	</tr>
<?php
	function RajzSmile($kepName, $kepKod = '')
	{
		
		print("
		<tr>
			<td><img src='emote/" .$kepName. "'></td>
			<td>" .$kepKod. "</td>
		</tr>"); // Egy emoteicon kiírása-rajzolása
	}
	
	// Emoteicon lista kinyerése a leírófájlból
	$data2 = @file_get_contents('emotes.lst');
	$sorok2 = explode("\r\n", $data2);
	$kepSzam2 = $sorok2[0];
	
	for ($j == 1; $j <= $kepSzam2; $j++)
	{
		$kep2 = explode(",", $sorok2[$j]);
		
		if ( $kep2[0] != "")
			RajzSmile($kep2[0], $kep2[1]);
	}
// Zárás
?>
</table>	
	
</body>

</html>