<?php
 /**
 * WhispyForum function file - functions.php
 * 
 * General functions library file
 * 
 * WhispyForum
 */
echo date('l jS \of F Y H:i:s')." functions loaded\n<br>"; // DEV

function generateHexToken()
{
	/**
	* This function generates a hexadecimal token and returns it as a variable
	* 
	* Nearly identical to generateHexTokenNoDC, but it does group the output variable in 4 char groups seperated with a double colon.
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

function generateHexTokenNoDC()
{
	/**
	* This function generates a hexadecimal token and returns it as a variable
	* 
	* Nearly identical to generateHexToken, but it does not group the output variable in 4 char groups seperated with a double colon.
	* 
	* @outputs: a token in this format: 3c43a9683f69348032fe1206d835
	*/
	$token = md5( sha1( time()+rand(0, time()) ) );
	
	return $token;
}

function saveThumbnailJPEG($originalImage,$new_height,$filename)
{
	/**
	* This function resizes an image to a set height and saves it as a thumbnail
	* Use for JPEG pictures
	* 
	* @inputs: $originalImage - filename of the original image file
	* 		   $new_height - new height (in pixels)
	*		   $filename - output filename
	*/
	
	// Get the original geometry and calculate scales
	list($width, $height) = getimagesize($originalImage);
		$new_width = round(($width * $new_height) / $height);
		//$new_width = $new_height; // Need cleanup here

	// Resize the original image
	$imageResized = imagecreatetruecolor($new_width, $new_height);
	$imageTmp = imagecreatefromjpeg ($originalImage);
	imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// Output
	imagejpeg($imageResized, $filename.".jpg", 100);
	imageDestroy($imageResized);
}

function saveThumbnailGIF($originalImage,$new_height,$filename)
{
	/**
	* This function resizes an image to a set height and saves it as a thumbnail
	* Use for GIF pictures
	* 
	* @inputs: $originalImage - filename of the original image file
	* 		   $new_height - new height (in pixels)
	* 		   $filename - output filename
	*/
	
	// Get the original geometry and calculate scales
	list($width, $height) = getimagesize($originalImage);
		$new_width = round(($width * $new_height) / $height);
		//$new_width = $new_height; // Need cleanup here

	// Resize the original image
	$imageResized = imagecreatetruecolor($new_width, $new_height);
	$imageTmp = imagecreatefromgif ($originalImage);
	imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// Output
	imagegif($imageResized, $filename.".gif", 0);
	imageDestroy($imageResized);
}

function saveThumbnailPNG($originalImage,$new_height,$filename)
{
	/**
	* This function resizes an image to a set height and saves it as a thumbnail
	* Use for GIF pictures
	* 
	* @inputs: $originalImage - filename of the original image file
	* 		   $new_height - new height (in pixels)
	* 		   $filename - output filename
	*/
	
	// Get the original geometry and calculate scales
	list($width, $height) = getimagesize($originalImage);
		$new_width = round(($width * $new_height) / $height);
		//$new_width = $new_height; // Need cleanup here

	// Resize the original image
	$imageResized = imagecreatetruecolor($new_width, $new_height);
	$imageTmp = imagecreatefrompng ($originalImage);
	imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// Output
	imagepng($imageResized, $filename.".png", 0);
	imageDestroy($imageResized);
}
?>
