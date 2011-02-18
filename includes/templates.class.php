<?php
/**
 * WhispyForum class library - templates.class.php
 * 
 * Templates class used for parsing template files.
 * 
 * Template files are located inside the /templates directory
 * and acts like static HTML files with marked locations
 * which are being replaced to set variables
 * during output.
 * 
 * WhispyForum
 */
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
		
		if ( $templateName != NULL) // If template name is specified
		{
			$this->__getTemplate($templateName); // Cache the template data
			
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
			
			if ( $varOutput == TRUE ) // If we decided to give return output
			{
				// First, we need to cache output into a variable
				$rVar = $this->_output;
				
				$this->__resetOutput(); // We clear the output stack
				
				// The reason of caching: after RETURN, we cannot call the clearing (becuse it exist the function)
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
		 
		if ( $templateName != NULL )  // If template name is specified
		{
			$this->__getTemplate($templateName); // We read in the template...
			
			if ( $varOutput == TRUE ) // If we decided to give return output
			{
				// First, we need to cache output into a variable
				$rVar = $this->_output;
				
				$this->__resetOutput(); // We clear the output stack
				
				// The reason of caching: after RETURN, we cannot call the clearing (becuse it exist the function)
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
?>
