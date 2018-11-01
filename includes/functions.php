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

function prettyVar($variable = NULL, $output = TRUE)
{
	/**
	* This function returns the var_export($variable) output
	* in both machine and human readable format
	* 
	* @inputs: $variable - name of the variable
	* 		   $output - if false, won't print the output of prettyVar automatically
	* @outputs: formatted text
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

/*function createArray($variable = NULL)
{
	/**
	* This function creates an array from a prettyVar()-formatted string.
	* 
	* @inputs: $variable - variable contents
	* @outputs: created array
	*/
	
	/**
	* Current errors:
	*  This function cannot properly handle boolean variables.
	* 
	* Action taken: disabling.
	*/
	
	/*// Immediately terminate execution because function still needs developing
	return '<span style="color: red; font-weight: bold;">The function <i>createArray()</i> should not be used as it is malfunctioning.</span>';
	die("Terminated, disabled function was called.");
	
	// Remove the prettyVar-formatting, this will remove the HTML tags
	$variable = str_replace(array("<br>","&nbsp;"), array("\n"," "), $variable);
	
	// First, truncate the header "array (" line and the footer ")\n" line
	$variable = str_replace(array("array (",")\n"), NULL, $variable);
	
	// Cut the remaining text into an array, containing each line
	$elements = explode(",\n", $variable);
	
	// Declare the array under construction
	$return_array = array();
	
	foreach ( $elements as &$line )
	{
		// Going through all lines, build the array
		
		// If the currently processed element is not empty (in normal cases, the footer appends a single NULL string), process it further
		if ( $line != "" )
		{
			// The $line variable looks like this:
			// 'key' => 'value'
			
			// We first need to further explode() the $line to fetch both the variable and the value
			$line_exploded = explode("=>", $line);
			
			// $line_exploded is now an array, containing two keys:
			// $line_exploded[0] is the original key
			// $line_exploded[1] is the original value
			
			// Trim whitespaces of both
			$line_exploded[0] = trim($line_exploded[0]);
			$line_exploded[1] = trim($line_exploded[1]);
			
			// Remove any unwanted ' character from both
			$line_exploded[0] = str_replace("'", NULL, $line_exploded[0]);
			$line_exploded[1] = str_replace("'", NULL, $line_exploded[1]);
			
			// Put the fetched variable and value into the array
			$return_array[$line_exploded[0]] = $line_exploded[1];
		}
	}
	
	return $return_array;
}*/

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

function ambox($type, $body = NULL, $title = NULL)
{
	/**
	* This function generates a template error/message/success box from
	* the parameters.
	* 
	* Easter egg: amBox (http://en.wikipedia.org/wiki/Template:Ambox) is the all-purpose pseudotemplate of Wikipedia :)
	* @inputs: $type - box type: ERROR, MESSAGE, SUCCESS, DEVELOPER (red, orange, green, orange (with special picture))
	* 	   $body - message body
	* 	   $title - box header
	*/
	
	// Hook the template conductor
	global $Ctemplate;
	
	// Based on the type, fetch the box
	switch ( strtoupper($type) )
	{
		case "ERROR":
			$template = "errormessage";
			$alt = "{LANG_ERROR_EXCLAMATION}";
			$picture = "Nuvola_apps_error.png";
			
			break;
		case "MESSAGE":
			$template = "messagebox";
			$alt = "Message";
			$picture = "Nuvola_apps_terminal.png";
			
			break;
		case "SUCCESS":
			$template = "successbox";
			$alt = "{LANG_SUCCESS_EXCLAMATION}";
			$picture = "Nuvola_apps_korganizer.png";
			
			break;
		case "DEVELOPER":
			$template = "messagebox";
			$alt = "Under development";
			$picture = "Nuvola_mimetypes_deb.png";
			
			$title = "This module is under development.";
			$body = "Please do not use this module in production!";
			
			break;
		default:
			$template = "errormessage";
			$alt = "{LANG_MISSING_PARAMETERS}";
			$picture = "Stop_hand.png";
			
			$title = "{LANG_MISSING_PARAMETERS}";
			$body = "{LANG_MISSING_PARAMETERS_BODY}. Invalid AmBox type.";
			
			break;
	}
	
	// Output the box on the screen
	$Ctemplate->useTemplate($template, array(
		'TITLE'	=>	$title,
		'BODY'	=>	$body,
		'ALT'	=>	$alt,
		'PICTURE_NAME'	=>	$picture
	), FALSE);
}

function devBox()
{
	/* Function alias for ambox("DEVELOPER"); */
	ambox("DEVELOPER");
}

function fClassFix( $value )
{
	/*
	 * Because the user can (and will) be retarded, and
	 * he/she cannot use the given format, this function
	 * fixes the entered f_class value to fit the original
	 * workway.
	*/
	
	// Format the f_class variable
	$f_class_fixed = strtoupper(preg_replace("/([0-9][0-9]?)(\.\s*|,\s*|\/\s*|:\s*|\.\s?_*|\s?|_|\.\/)([A-Za-z]*)(\.|,)?/","$1. $3", $value));
	
	/*// Valid f_class values
	$f_class_valid = array("TANáR",
		"9. A", "9. B", "9. C", "9. D", "9. E",
		"10. A", "10. B", "10. C", "10. D", "10. E",
		"11. A", "11. B", "11. C", "11. D", "11. E",
		"12. A", "12. B", "12. C", "12. D", "12. E",
		"13. A", "13. B", "13. C", "13. D", "13. E"
	);
	
	// Output error message if the entered f_class is improper,
	// return proper value and continue execution if proper.
	if ( !in_array($f_class_fixed, $f_class_valid) )
	{
		ambox("ERROR", "Az általad megadott osztály érvénytelen, mivel ilyen osztály nem létezik.<br>Kérlek, ellenőrizd az osztályodat az ellenőrző könyveden, vagy a bizonyítványodban.<br><br>".'<a href="index.php" alt="Kezdőlap">Visszatérés a kezdőlapra</a>', "Érvénytelen osztály!");
		
		echo 'A rendszerhibák elkerülése érdekében érvénytelenül megadott osztály esetén a futtatás kényszerítetten megszakad. Bizonyos megadott adatok, vagy nem mentett fájlok visszaállíthatatlanul elveszhettek.<br style="clear: both"><br><br><br><br><br style="clear: both">';
		
		die("Worker thread received <tt>SIGCLUSTERFUCK</tt> (killcode <tt>0x1981</tt>), execution halted. Going nowhere without my <tt>init()</tt>.");
	} elseif ( in_array($f_class_fixed, $f_class_valid) )
	{*/
		return $f_class_fixed;
	//}
}

function selfURL()
{
	/* This function generates the full URL of the current request.
	 *
	 * Useful for return URL generation.
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

function log_append($text, $time = 0)
{
	if ($time === 0)
		$time = time();
	
	global $Cmysql;
	
	$Cmysql->Query("INSERT INTO log(logdate, log) VALUES ('" .$time. "', '" .$Cmysql->EscapeString($text). "')");
}
?>
