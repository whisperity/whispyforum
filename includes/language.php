<?php
/**
 * WhispyForum
 * 
 * /includes/language.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

// If the global $localization array is nonexistant, create it to prevent further errors.
global $localization;
if ( !isset($localization) || !is_array($localization) )
	$localization = array();

function load_lang( $basedir, $file )
{
	/**
	 * This function loads the $file file from $basedir folder from the hard disk to the memory.
	 * The contents of the file will be merged into the global $localization array.
	 * 
	 * It means that if two loaded localization files set a value for the same key,
	 * the value stated, the value used will be the one stated in the last loaded file.
	*/
	
	if ( !file_exists("language/".$basedir."/".$file.".php") )
	{
		echo "Requested language file " .$basedir. "/" .$file. " not found.";
		return FALSE;
	}
	
	// Load the language file, it will give us an $localized array.
	include("language/" .$basedir. "/" .$file. ".php");
	
	// Merge the loaded array with the global localization.
	global $localization;
	$localization = array_merge($localization, $localized);
	
	// Free the now-loaded array from memory. 
	unset($localized);
}
	
function lang_key( $key, $replace = array() )
{
	/**
	 * This function returns the localized language key $key
	 * with optionally replacing keys from the $replace input.
	*/
	
	global $localization;
	
	if ( !array_key_exists($key, $localization) )
		return "The requested key " .$key. " is not found in localization.";
	
	// Load the requested key from the localization array.
	$return = $localization[$key];
	
	// Replace the given variables in the localization if any.
	if ( is_array($replace) )
	{
		foreach ( $replace as $k => $v )
		{
			$return = str_replace( "{" .$k ."}", $v, $return);
		}
	}
	
	return $return;
}
?>
