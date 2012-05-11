<?php
/**
 * WhispyForum
 * 
 * /includes/functions.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

function token()
{
	/**
	 * This function generates a unique token for various purposes.
	*/
	
	return md5( sha1( time() + rand(0, time()) ) ) ."-". substr( str_shuffle('0123456789abcdefghijklmnopqrstvwxyz0123456789abcdefghijklmnopqrstvwxyz0123456789abcdefghijklmnopqrstvwxyz0123456789abcdefghijklmnopqrstvwxyz0123456789abcdefghijklmnopqrstvwxyz0123456789abcdefghijklmnopqrstvwxyz'), 0, 16);
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

function config( $variable )
{
	/**
	 * This function returns the value of $variable from the config table.
	*/
	
	global $sql;
	
	if ( !is_object($sql) )
		return FALSE;
	
	$result = $sql->fetch_array($sql->query("SELECT value FROM config WHERE variable='" .$sql->escape($variable). "'"), SQL_NUM);
	
	return $result[0];
}

function prettyVar($variable = NULL, $output = TRUE)
{
	/**
	 * This function returns the var_export($variable) output
	 * in both machine and human readable format.
	 * 
	 * If $output is TRUE, the output is printed to the screen (default).
	 * If FALSE, it is returned.
	*/
	
	$return_value = str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($variable,true))."<br>";
	
	if ( $output )
	{
		echo $return_value;
		return 0;
	} elseif ( !$output )
	{
		return $return_value;
	}
}

function sendTemplateMail($address, $subject, $template_name, $variable_array)
{
	/**
	* This function sends an email message to a set recipient
	* using a set template and a set array of replaced variables (in template)
	* 
	* @inputs: $address - recipient address
	* 	   $subject - mail subject
	* 	   $template_name - name of the template file
	* 	   $variable_array - array of the variables have to be replaced
	*/
	
	global $Ctemplate; // Hook template conductor
	
	// Mail body (content)
	$message = $Ctemplate->useTemplate($template_name, $variable_array, TRUE);
	
	// Mail headers
	$headers  = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'Website-domain: ' .config("site_host");
	
	// Send out the mail
	mail($address, $subject, $message, $headers);
}

function ambox($type, $body = NULL, $title = NULL, $image = NULL, $image_alt = NULL, $theme = NULL)
{
	/**
	 * This function generates a template message box from
	 * the parameters. $type can be CRITICAL, ERROR, WARNING, INFO or SUCCESS.
	 * 
	 * The parsed HTML template is returned as a string.
	 * 
	 * Easter egg: amBox (http://en.wikipedia.org/wiki/Template:Ambox) is the all-purpose pseudotemplate of Wikipedia :)
	*/
	
	global $template;
	
	if ( !isset($image) )
	{
		switch ( $type )
		{
			case 'CRITICAL':
				$image = "critical.png";
				break;
			case 'ERROR':
				$image = "error.png";
				break;
			case 'WARNING':
				$image = "warning.png";
				break;
			case 'INFO':
				$image = "info.png";
				break;
			case 'SUCCESS':
				$image = "success.png";
				break;
		}
	}
	
	if ( !isset($theme) )
	{
		global $user;
		
		$theme = ( ( is_object($user) && $user->get_value("theme") != USER_NO_KEY ) ? $user->get_value("theme") : config("theme") );
		
		if ( !$theme )
			$theme = "tuvia";
	}
	
	return $template->parse_template("ambox", array(
		'TYPE'	=>	strtolower($type),
		'TITLE'	=>	$title,
		'BODY'	=>	$body,
		'IMAGE'	=>	$image,
		'IMAGE_ALT'	=>	$image_alt,
		'THEME_NAME'	=>	$theme) );
}

function selfURL()
{
	/**
	 * This function generates the full URL of the current request.
	*/
	
	// Define whether HTTPS (secure HTTP) is on
	$s = empty($_SERVER["HTTPS"]) ? ''
		: ($_SERVER["HTTPS"] == "on") ? "s"
		: "";
	
	// Get the protocol itself
	$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")).$s;
	
	// Get the port or use HTTP 80 by default
	$port = ($_SERVER["SERVER_PORT"] == "80") ? ""
		: (":".$_SERVER["SERVER_PORT"]);
	
	// Fetch a proper URL from the data and the current request
	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
?>
