<html>

<head> 
<title>Témához elérhető hangulatjelek listázása</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="index.php"><< Megjelenés</a>
<a href="images.php"><< Képek</a>
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