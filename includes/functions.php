<?php
 /**
 * WhispyForum function file - functions.php
 * 
 * General functions library file
 * 
 * WhispyForum
 */
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

	// Resize the original image
	$imageResized = imagecreatetruecolor($new_width, $new_height);
	$imageTmp = imagecreatefrompng ($originalImage);
	imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// Output
	imagepng($imageResized, $filename.".png", 0);
	imageDestroy($imageResized);
}

function DecodeSize( $bytes )
{
	/**
	* This function generates a more human-readable format
	* of a given file size value.
	* 
	* @inputs: $bytes - file size in bytes
	* @outputs: the file size in KB, MB, GB, etc. based on the rounded size
	*/
	
	$types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
	return( round( $bytes, 2 ) . " " . $types[$i] );
}

function bbDecode($input)
{
	/**
	* This function replaces the BB code format to HTML format
	* 
	* @inputs: $input - BB code formatted text
	* @outputs: HTML formatted text
	*/
	
	$bbCode = array(
		"/\n?\[code\](.*?)\[\/code\]/si",
		//'/(^|[ \n\r\t])((http(s?):\/\/)(www\.)?([a-z0-9_-]+(\.[a-z0-9_-]+)+)(:[0-9]+)?(\/[^\/ \)\(\n\r]*)*)/is',
		"/\[img\](.*?)\[\/img\]/si",
		"/\[img[=]([0-9]*?)x([0-9]*?)\](.*?)\[\/img\]/si",
		"/\[i\](.*?)\[\/i\]/si",
		"/\[b\](.*?)\[\/b\]/si",
		"/\[u\](.*?)\[\/u\]/si",
		"/\[s\](.*?)\[\/s\]/si",
		//"/([a-z_-][a-z0-9\._-]*@[a-z0-9_-]+(\.[a-z0-9_-]+)+)/is",
		"/\[url\](.*?)\[\/url\]/si",
		"/\[url=(.*?)\](.*?)\[\/url\]/si",
		"/\n?\[quote\]\n*/i",
		'/\n?\[quote=["]?([a-zA-Z0-9\s]*?)["]?\]\n*/i',
		"/\[\/quote\]/i",
		"/\n/i",
		
		// Emoticons
		"/:cry:/i",
		"/:wave:/i",
		"/:spam:/i",
		"/:offtopic:/i",
		"/:[-]?\)/i",
		"/:[-]?\(/i",
		"/:[-]?D/i",
		"/:[-]?P/i",
		"/;[-]?\)/i",
		"/:[-]?S/i",
		"/:[-]?O/i",
		"/:[-]?H/i"
	); 
	
	$htmlTags = array(
		"<TABLE BORDER=\"0\" ALIGN=\"CENTER\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"75%\"><TR><TD CLASS=\"code\" ALIGN=\"LEFT\"><B>{LANG_BB_TAG_CODE}</B><BR><HR>\\1<HR></TD></TR></TABLE>",
		//'\1[url=\2]\2[/url]',
		"<IMG SRC=\"\\1\" BORDER=\"0\">",
		"<IMG SRC=\"\\3\" BORDER=\"0\" WIDTH=\"\\1\" HEIGHT=\"\\2\">",
		"<I>\\1</I>",
		"<B>\\1</B>",
		"<U>\\1</U>",
		"<S>\\1</S>",
		//"[url=mailto:\\1]\\1[/url]",
		"<A HREF=\"\\1\" TARGET=\"_blank\">\\1</A>",
		"<A HREF=\"\\1\" TARGET=\"_blank\">\\2</A>",
		"<TABLE BORDER=\"0\" ALIGN=\"CENTER\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"75%\"><TR><TD CLASS=\"quote\" ALIGN=\"LEFT\"><B>{LANG_BB_TAG_QUOTE}</B><BR><HR>",
		"<TABLE BORDER=\"0\" ALIGN=\"CENTER\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"75%\"><TR><TD CLASS=\"quote\" ALIGN=\"LEFT\"><B>{LANG_BB_TAG_QUOTE_BY} \\1</B><BR><HR>",
		"<HR></TD></TR></TABLE>",
		"<br>",
		
		// Emoticons
		'<img src="themes/{THEME_NAME}/emoticons/cry.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/wave.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/spam.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/offtopic.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/smile1.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/sad.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/grin.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/tongue.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/wink.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/confused.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/ohmy.gif">',
		'<img src="themes/{THEME_NAME}/emoticons/cool.gif">'
	);
	
	$output = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // Kill the HTML codes inside
	$output = preg_replace($bbCode, $htmlTags, $output); // Parse the BB codes
	
	global $wf_lang; // Initializing language array
	
	/* Replacing language tokens */
	preg_match_all('/{LANG_.*?}/', $output, $lKeys, PREG_PATTERN_ORDER, 0);
	// $lKeys[0] contains all {LANG_*} language variables (* is the string's name)
	
	$j = 0; // Counter reset to zero
	
	foreach($lKeys[0] as $lang_tag)
	{
		// Then replace the output, updating it
		$output=str_replace($lKeys[0][$j],$wf_lang[ $lKeys[0][$j] ],$output);
		
		$j++; // Turn the counter by one
	}
	/* Replacing language tokens */
	
	return $output; // Return the formatted variable
}

function fDate($date = "current")
{
	/**
	* This function formats the set TIMESTAMP (epoch)
	* formatted date to a human-readable one.
	* 
	* @inputs: $date - timestamp (epoch) (if isn't set, it'll format the current time)
	* @outputs: formatted date
	*/
	
	if ( $date == "current" )
	{
		// If the $date has the default value
		$date = time(); // Make it the current EPOCH TIMESTAMP
	}
	
	// Format the date with the date(format, epoch); function and return it
	return date("F j, Y, H:i", $date);
}

function config($variable = NULL)
{
	/**
	* This function returns the global configuration variable
	* $variable value.
	* 
	* @inputs: $variable - name of the variable
	* @outputs: return value of variable (from database table `config`)
	*/
	
	global $Cmysql; // We need the SQL class
	
	// Get the value array from database
	$value = mysql_fetch_row($Cmysql->Query("SELECT value FROM config WHERE variable='" .$Cmysql->EscapeString($variable). "'"));
	
	return $value[0]; // Return the value
}

function prettyVar($variable = NULL)
{
	/**
	* This function returns the var_export($variable) output
	* in both machine and human readable format
	* 
	* @inputs: $variable - name of the variable
	* @outputs: formatted text
	*/
	
	
	return str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($variable,true))."<br>";
}
?>
