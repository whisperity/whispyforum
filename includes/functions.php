<?php
 /**
 * WhispyForum function file - functions.php
 * 
 * General functions library file
 * 
 * WhispyForum
 */
echo "functions loaded\n<br>"; // DEV

 function generateHexToken()
 {
	/**
	* This function generates a hexadecimal token and returns it as a variable
	* 
	* @outputs: a token in this format: 3c43:a968:3f69:3480:32fe:1206:d835
	*/
	$token = md5( sha1( time()+rand(0, time()) ) );
	
	$tok = substr($token, 0, 4); // First 4 characters
	for ($i = 5; $i <= 28; $i+=4 ) // Generate the rest with a FOR loop
	{
		$tok .= ":" . substr($token, $i, 4); // The rest 4 charaters portions are linked with a :
	}
	
	return $tok;
 }
?>
