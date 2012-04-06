<?php
/**
 * WhispyForum
 * 
 * /includes/template.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

class class_template
{
	private $_output; // OUTPUT is the output which is printed at the end of the parsing
	
	private function __getTemplate($templateName = NULL)
	{
		/**
		 * This function caches the template content and makes it ready for parsing
		 * Internal use only
		 * 
		 * @inputs: $templateName (default NULL) -- the template's filename (without extension)
		 */
		
		if ( $templateName != NULL ) // If template name is specified
		{
			if ( file_exists("templates/" .$templateName. ".htm") ) // If the template file exists (we only need to check this here)
			{
				// Read in the file content and store it as output
				$this->_output = file_get_contents("templates/" .$templateName. ".htm");
			} else { // If not
				$this->_output = file_get_contents("templates/template_missing.htm"); // Read an error message template
				
					$this->_output=str_replace('{TNAME}',$templateName,$this->_output); // Replace template name
				
				/* From now, parsing will continue without any replacement
				   and proper error output will be given. */
			}
		}
	}
	
	function useTemplate($templateName = NULL, $replaceArray = array(NULL => NULL), $varOutput = FALSE)
	{
		/**
		 * Main external usable function to get, parse and output templates
		 * 
		 * @inputs: $templateName (default NULL) -- the template's filename (without extension)
		 * 			$replaceArray (default array NULL) -- array of template variables and values to be replaced
					$varOutput (default FALSE) -- boolean variable of giving echo or variable (return) output
		 * 
		 * example:
		 *	useTemplate("filename", array("variable" => "value", "another" => "replacement"), FALSE);
		 */
		
		global $wf_lang; // Load the language array (/language/language_name.php loads it)
		
		if ( $templateName != NULL) // If template name is specified
		{
			$this->__getTemplate($templateName); // Cache the template data
			
			/* Replacing language tokens */
			preg_match_all('/{LANG_.*?}/', $this->_output, $lKeys, PREG_PATTERN_ORDER, 0);
			// $lKeys[0] contains all {LANG_*} language variables (* is the string's name)
			
			$j = 0; // Counter reset to zero
			
				foreach($lKeys[0] as $lang_tag)
				{
					// Then replace the output, updating it
					$this->_output=str_replace($lKeys[0][$j],$wf_lang[ $lKeys[0][$j] ],$this->_output);
					
					$j++; // Turn the counter by one
				}
			/* Replacing language tokens */
			
			if ( count($replaceArray) > 0 ) // If we specified the replace array
			{
				// Replace every template variable, while auto-updating output
				
				$rKeys = array_keys($replaceArray); // Create a second array containing the first array's keys
				$i = 0; // Counter reset to zero
				
					foreach($replaceArray as $replaceTag)
					{
						// First, we get the replacement variable from the array
						$replaceVariable = $rKeys[$i];
						
						// Then replace the output, updating it
						$this->_output=str_replace('{'.$replaceVariable.'}',$replaceTag,$this->_output);
						
						$i++; // Turn the counter by one
					}
			}
			
			/* Replacing language tokens */
			preg_match_all('/{LANG_.*?}/', $this->_output, $lKeys, PREG_PATTERN_ORDER, 0);
			// $lKeys[0] contains all {LANG_*} language variables (* is the string's name)
			
			$j = 0; // Counter reset to zero
			
				foreach($lKeys[0] as $lang_tag)
				{
					// Then replace the output, updating it
					$this->_output=str_replace($lKeys[0][$j],$wf_lang[ $lKeys[0][$j] ],$this->_output);
					
					$j++; // Turn the counter by one
				}
			/* Replacing language tokens */
			
			if ( !isset($_SESSION['theme_name']) )
			{
				// If there isn't session data (for example while logging in or out)
				// Replace the theme name for the default theme (winky)
				$this->_output=str_replace('{THEME_NAME}','winky',$this->_output);
			} else {
				// If there is
				// Replace the theme name for the user's preference
				$this->_output=str_replace('{THEME_NAME}',$_SESSION['theme_name'],$this->_output);
			}
			
			// Removing the <!--- HTML TEMPLATE COMMENTS --> from the output
			//$this->_output=preg_replace("/\n?<!---.*?-->\n?/s","",$this->_output);
			
			if ( $varOutput == TRUE ) // If we decided to give return output
			{
				// First, we need to cache output into a variable
				$rVar = $this->_output;
				
				$this->__resetOutput(); // We clear the output stack
				
				// The reason of caching: after RETURN, we cannot call the clearing (becuse it exits the function)
				// but if we call it before, it will give NULL as output
				return $rVar; // We return cached output as a variable
			} elseif ( $varOutput == FALSE ) // If we decided to give echo output
			{
				$this->__giveOutput(); // At the end, we push the script forward to give the template's output
			}
		}
	}
	
	function useStaticTemplate($templateName = NULL, $varOutput = FALSE)
	{
		/**
		 * Main external usable function to get and output templates without any parsing
		 * like just simply include()-ing the template file.
		 * 
		 * Why use this rather than the include()?
		 *  This function will gain meaning in the future.
		 * 
		 * @inputs: $templateName (default NULL) -- the template's filename (without extension)
		 *			$varOutput (default FALSE) -- boolean variable of giving echo or variable (return) output
		 * 
		 * example:
		 *	useTemplate("filename");
		 */
		
		global $wf_lang; // Load the language array (/language/language_name.php loads it)
		
		if ( $templateName != NULL )  // If template name is specified
		{
			$this->__getTemplate($templateName); // We read in the template...
			
			if ( !isset($_SESSION['theme_name']) )
			{
				// If there isn't session data (for example while logging in or out)
				// Replace the theme name for the default theme (winky)
				$this->_output=str_replace('{THEME_NAME}','winky',$this->_output);
			} else {
				// If there is
				// Replace the theme name for the user's preference
				$this->_output=str_replace('{THEME_NAME}',$_SESSION['theme_name'],$this->_output);
			}
			
			/* Replacing language tokens */
			preg_match_all('/{LANG_.*?}/', $this->_output, $lKeys, PREG_PATTERN_ORDER, 0);
			// $lKeys[0] contains all {LANG_*} language variables (* is the string's name)
			
			$j = 0; // Counter reset to zero
			
				foreach($lKeys[0] as $lang_tag)
				{
					// Then replace the output, updating it
					$this->_output=str_replace($lKeys[0][$j],$wf_lang[ $lKeys[0][$j] ],$this->_output);
					
					$j++; // Turn the counter by one
				}
			/* Replacing language tokens */
			
			// Removing the <!--- HTML TEMPLATE COMMENTS --> from the output
			//$this->_output=preg_replace("/\n?<!---.*?-->\n?/s","",$this->_output);
			
			if ( $varOutput == TRUE ) // If we decided to give return output
			{
				// First, we need to cache output into a variable
				$rVar = $this->_output;
				
				$this->__resetOutput(); // We clear the output stack
				
				// The reason of caching: after RETURN, we cannot call the clearing (becuse it exits the function)
				// but if we call it before, it will give NULL as output
				return $rVar; // We return cached output as a variable
			} elseif ( $varOutput == FALSE ) // If we decided to give echo output
			{
				$this->__giveOutput(); // At the end, we push the script forward to give the template's output
			}
		}
	}
	
	private function __giveOutput()
	{
		/**
		 * This function prints the replaced parsed template output
		 * Internal use only
		 */
		
		echo $this->_output; // Print the output
		
		$this->__resetOutput(); // Reset the output stack
	}
	
	private function __resetOutput()
	{
		/**
		 * Use this function to reset the template output stack
		 */
		
		$this->_output = NULL; // Reset the output
	}
	
	function DoMenuBars($mSide)
	{
		/**
		* This function generates the left and right menubars (based on parameter)
		* 
		* @inputs: $mSide - menu side ('LEFT' or 'RIGHT')
		*/
		
		global $Cmysql; // We need to declare the sql layer
		
		// Define the value of the side (to fit SQL structrue)
		switch ($mSide)
		{
			case 'LEFT':
				$mSideLC = 'left';
				break;
			case 'RIGHT':
				$mSideLC = 'right';
				break;
		}
		
		$menudata = $Cmysql->Query("SELECT * FROM menus WHERE side='" .$mSideLC. "' ORDER BY align ASC"); // Do a query to select menus on the set side (ordered by align)
		
		while ( $row = mysql_fetch_assoc($menudata) )
		{
			// We generate every menu
			
			// First, get menu entries
			$menuentries = $Cmysql->Query("SELECT * FROM menu_entries WHERE menu_id=" .$Cmysql->EscapeString($row['id']));
			
			$menuContent = "<ul>"; // Menu content is an opening list
			
			while ( $entryrow = mysql_fetch_assoc($menuentries) )
			{
				// First, we explode the href by the / characters
				$hrExploded = explode('/', $entryrow['href']);
				
				// Define whether the link is internal or external
				$hrefType = 'INTERNAL'; // The link is internal by default
				
				// Check for HTTP links
				if ( in_array('http:', $hrExploded) )
				{
					$hrefType = 'EXTERNAL'; // If it has HTTP in it, the link is external
				}
				
				// Add current entry to $menuContent variable;
				$menuContent .= "<li><a href='" .$entryrow['href']. "' alt='" .$entryrow['label']. "'"; // List item open, link href and alt
				
				// If the link is external, append external window target
				if ( $hrefType == "EXTERNAL" )
				{
					$menuContent .= " target='_blank'"; // External window
				}
				
				$menuContent .= "'>" .$entryrow['label']. "</a>"; // Link close and text
				
				// If the link is external, append external image
				if ( $hrefType == "EXTERNAL" )
				{
					$menuContent .= "&nbsp;<img src='themes/" .$_SESSION['theme_name']. "/link.png' alt='External link: opens in new window'>";
				}
				
				$menuContent .="</li>\n"; // Add list item closing tag
			}
			
			// End entry list
			$menuContent .= "</ul>"; // Append closing tag
			
			// Do menubox
			$this->UseTemplate("menubox", array(
				"HEADER"	=>	$row['header'], // Menu header
				"CONTENT"	=>	$menuContent
			), FALSE);
			
			$menuContent = NULL; // Menu content is nothing
		}
	}
}

class template
{
	/**
	 * The template conductor is responsible for generating the HTML-output of the system.
	 * 
	 * Templates are HTML files containing the HTML output the system uses. Templates are parsed
	 * in runtime, with their "keys" replaced into "values". Keys are put into the source.
	 * 
	 * Template files can either be "single" or "multi" ones.
	 * 
	 * Single files are bearing the .tpf (template file) extension and they contain one template.
	 * The template file is added to the memory with the bare filename.
	 * 
	 * Multi files are .tpp (template package), and they contain multiple templates in this format:
	 * <!--- BEGIN template -->
	 *  (HTML content is here)
	 * <!--- END template -->
	 *  (recommended line break)
	 * <!--- BEGIN another_template -->
	 *  (some HTML code and variables)
	 * <!--- END another_template -->
	 * 
	 * When these packages are loaded, template data between the BEGIN and END statements is added to
	 * the memory with the name of the name present in the BEGIN and END statement.
	 * (In this example: template and another_template will be available after loading the .tpp file.)
	*/
	
	// Base directory (relative to main folder of script) from where the template files should be included.
	private $_basedir = "templates/";
	
	// The array contains all the templates loaded into the memory, unparsed.
	private $_templates = array();
	
	// $_index_tpp contains an indexed list on all the multi-template packages loaded
	private $_index_tpp = array();
	
	// $_stack is the array containing the runtime templates parsed, ready to be output.
	private $_stack = array();
	
	// This array contains the current output string.
	private $_output = NULL;
	
	function set_basedir( $basedir )
	{
		/**
		 * This function sets the internal basedir to the $basedir argument.
		*/
		
		$this->_basedir = $basedir;
	}
	
	function get_basedir()
	{
		/**
		 * This function returns the current _basedir property of the object.
		*/
		
		return $this->_basedir;
	}
	
	function load_template( $file, $multi = FALSE )
	{
		/**
		 * This function loads the requested $file template from the filesystem.
		 * If $multi is set to TRUE, the file will be treated a multi-template file.
		*/
		
		$suffix = ( $multi ? ".tpp" : ".tpf" );
		
		// Return error if the file we want does not exist.
		if ( !file_exists( $this->_basedir.$file.$suffix ) )
		{
			echo "Warning! The requested file (" .$file.$suffix. ") not found in basedir (" .$this->_basedir. ").";
			return FALSE;
		}
		
		// Stop executing the function if the template file is already loaded.
		if ( ( !$multi && array_key_exists( $file, $this->_templates ) ) || ( $multi && in_array( $file, $this->_index_tpp ) ) )
			return FALSE;
		
		// Load the file contents
		$handle = fopen( $this->_basedir.$file.$suffix, "rb" );
		$data = fread( $handle, filesize($this->_basedir.$file.$suffix) );
		fclose($handle);
		
		// Store the template data into memory
		if ( !$multi )
		{
			$this->_templates[ $file ] = $data;
		} elseif ( $multi )
		{
			// Escape some strings in the
			$data = str_replace( array('\\', '\'', "\n"), array('\\\\', '\\\'', ''), $data);
			
			// Chunk up the template package to individual template files and make an executable
			// PHP code which will insert the loaded templates into memory.
			$data = preg_replace('#<!--- BEGIN (.*?) -->(.*?)<!--- END (.*?) -->#', "\n" . '$this->_templates[\'\\1\'] = \'\\2\';', $data);
			
			// Run the previously created PHP code
			eval($data);
			
			// Store the template package's name in the index
			$this->_index_tpp[] = $file;
		}
		
		unset($data);
	}
	
	function parse_template( $template, $replace = NULL )
	{
		/**
		 * This function parses the said $template with replacing the keys in the template file
		 * with values from the $replace array. The output is stored in the _output buffer.
		*/
		
		if ( !array_key_exists( $template, $this->_templates ) )
		{
			echo "Error! The requested template (" .$template. ") is not loaded.";
			return FALSE;
		}
		
		$this->_output = $this->_templates[ $template ];
		
		if ( is_array($replace) )
		{
			foreach ( $replace as $k => $v )
			{
				$this->_output = str_replace( "{[" .$k ."]}", $v, $this->_output);
			}
		}
		
		return $this->_output;
	}
	
	function create_stack( $name = NULL )
	{
		/**
		 * This function creates a stack to contain templates for buffering purposes.
		 * The stack will be given the name $name if present, or an automatically generated one.
		*/
		
		if ( isset($name) )
		{
			if ( array_key_exists($name, $this->_stack) )
			{
				echo "Error! The stack named " .$name. " already exists.";
				return FALSE;
			}
			
			$this->_stack[ $name ] = "";
		} else {
			$this->_stack[] = "";
		}
	}
	
	private function _get_recent_stack()
	{
		/**
		 * This function returns the identifier of the most recent stack.
		*/
		
		// The commendted-out way would be understandable and actually used, but no.
		// Since PHP 5.0.5, using it gives "Strict Standards: Only variables should be passed by reference"
		// $stack = end( array_keys($this->_stack) );
		
		$stack_keys = array_keys($this->_stack);
		return end($stack_keys);
	}
	
	function delete_stack( $name = NULL )
	{
		/**
		 * Remove the stack named $name, or, if not present, the most recent one.
		*/
		
		if ( !isset($name) )
			$name = $this->_get_recent_stack();
		
		if ( !array_key_exists($name, $this->_stack) )
		{
			echo "Error! The stack named " .$name. " does not exist.";
			return FALSE;
		}
		
		unset($this->_stack[ $name ]);
	}
	
	function add_to_stack( $data = NULL, $stack = NULL )
	{
		/**
		 * Store $data into the stack named $stack.
		*/
		
		// If $data is not present, it will automatically be the last parsed template.
		if ( !isset($data) )
			$data = $this->_output;
		
		// If $stack is not present, it will automatically be the newest created stack.
		if ( !isset($stack) )
			$stack = $this->_get_recent_stack();
		
		if ( !array_key_exists($name, $this->_stack) )
		{
			echo "Error! The stack named " .$name. " does not exist.";
			return FALSE;
		}
		
		$this->_stack[ $stack ] .= $data;
	}
	
	function get_stack( $name = NULL )
	{
		/**
		 * Return the value of stack named $name, or the most recent one.
		*/
		
		if ( !isset($name) )
			$name = $this->_get_recent_stack();
		
		if ( !array_key_exists($name, $this->_stack) )
		{
			echo "Error! The stack named " .$name. " does not exist.";
			return FALSE;
		}
		
		return $this->_stack[ $name ];
	}
}
?>
