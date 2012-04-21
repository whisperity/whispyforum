<?php
/**
 * WhispyForum
 * 
 * /includes/template.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

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
	
	// $_replace_archive contain the already replaced keys with the replace values.
	// This allows to cache the commonly used keys like 'THEME_NAME'.
	private $_replace_archive = array();
	
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
			echo ambox('CRITICAL', lang_key("TEMPLATE FILE MISSING", array(
				'FILE'	=>	$file.$suffix,
				'BASEDIR'	=>	$this->_basedir)) );
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
			echo ambox('ERROR', lang_key("TEMPLATE MISSING", array('TEMPLATE'	=>	$template)) );
			return FALSE;
		}
		
		$this->_output = $this->_templates[ $template ];
		
		// We load the already changed keys from the archive.
		// Because the current $replace parameter is the second one,
		// the current replace array will dominate over already-existing keys in the archive.
		$replace = array_merge( $this->_replace_archive, ( is_array($replace) ? $replace : array() ) );
		
		if ( is_array($replace) )
		{
			foreach ( $replace as $k => $v )
			{
				$this->_output = str_replace( "{[" .$k ."]}", $v, $this->_output);
				$this->_replace_archive[$k] = $v;
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
				echo ambox('WARNING', lang_key("TEMPLATE STACK ALREADY", array('STACK'	=>	$name)) );
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
			echo ambox('WARNING', lang_key("TEMPLATE STACK MISSING", array('STACK'	=>	$name)) );
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
		
		if ( !array_key_exists($stack, $this->_stack) )
		{
			echo ambox('WARNING', lang_key("TEMPLATE STACK MISSING", array('STACK'	=>	$stack)) );
			return FALSE;
		}
		
		$this->_stack[ $stack ] .= "\n" .$data;
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
			echo ambox('WARNING', lang_key("TEMPLATE STACK ALREADY", array('STACK'	=>	$name)) );
			return FALSE;
		}
		
		return $this->_stack[ $name ];
	}
}

function load_all_templates( $object = NULL, $tpf = TRUE, $tpp = TRUE, $htm = FALSE )
{
	/**
	 * This function loads _every_ template file found installed on the server.
	 * Because this function creates an exaggerated amount of overhead, using it
	 * in productional environments is discouraged.
	 * 
	 * The function will use the referenced $object object as its template conductor.
	 * $tpf, $tpp and $htm are boolean values setting whether the function should load such files.
	*/
	
	// Output error if conductor is not referenced.
	if ( !isset($object) || !is_object($object) )
	{
		echo "The referenced object argument is not an object or not present.";
		return FALSE;
	}
	
	// Store the old basedir and set the current basedir to script install folder.
	$old_basedir = $object->get_basedir();
	$object->set_basedir("./");
	
	function index_directory( $dir )
	{
		/**
		 * This function reads and enumerates the requested $dir directory, searching for files.
		 * 
		 * To understand this function, you need to know that this function executes itself recursively.
		 * Every execution returns a $names array, which is automatically merged into the "widest" $names,
		 * and that last $names is returned at the end of the last execution.
		*/
		
		$names = array();
		
		// Do not enumerate if we failed to open the directory.
		if ( is_dir($dir) && is_readable($dir) )
		{
			// We go on with every file in the directory.
			foreach ( scandir($dir) as $file )
			{
				// We omit some directories to prevent recursion and errors.
				if ( !in_array($file, array(".", "..", ".svn")) )
				{
					if ( is_dir($dir."/".$file) && is_readable($dir."/".$file) )
					{
						// If the current entry is a directory, we read that directory too.
						// (And we add the returned values of this execution to the $names array.)
						$names[] = index_directory($dir."/".$file);
					}
					
					if ( is_file($dir."/".$file) && is_readable($dir."/".$file) )
					{
						// If the current entry is a file, we check for file extension and add it to the files-to-load list.
						
						// Omit .bak and .xxx~ (Windows and Unix-like) backup files.
						if ( pathinfo($dir."/".$file, PATHINFO_EXTENSION) !== "bak" && substr( pathinfo($dir."/".$file, PATHINFO_EXTENSION), strlen( pathinfo($dir."/".$file, PATHINFO_EXTENSION) ) - 1, 1 ) !== "~" )
						{
							$names[] = $dir."/".$file;
						}
					}
				}
			}
		} elseif ( !is_dir($dir) || !is_readable($dir) )
		{
			echo "The requested directory " .$dir. " could not be opened.";
			return FALSE;
		}
		
		return $names;
	}
	
	function array_merge_multidimensional( $array = array() )
	{
		/**
		 * This function merges a nested, multidimensional array into one superarray
		 * The input $array is the initial array, and a result of an array will be returned.
		*/
		
		$return = array();
		
		if ( !isset($array) || !is_array($array) )
			return FALSE;
		
		// Go trough the input element-by-element.
		foreach ( $array as &$element )
		{
			if ( is_array($element) )
			{
				// If the currently checked element is an array, we call this function recursively.
				$return = array_merge( $return, array_merge_multidimensional($element) );
			} elseif ( !is_array($element) )
			{
				// If it is not an array, add the element to the return list.
				$return[] = $element;
			}
		}
		
		return $return;
	}
	
	$files = array_merge_multidimensional( index_directory(".") );
	
	foreach ( $files as &$file )
	{
		// Trim the prefixing ./ if present.
		if ( substr($file, 0, 2 ) === "./" )
			$file = substr($file, 2, strlen($file));
		
		// Load TPF files if requested
		if ( pathinfo($file, PATHINFO_EXTENSION) === "tpf" && $tpf === TRUE )
		{
			$file = substr($file, 0, strpos($file, ".tpf"));
			$object->load_template($file, FALSE);
		}
		
		// Load TPP files if requested
		if ( pathinfo($file, PATHINFO_EXTENSION) === "tpp" && $tpp === TRUE )
		{
			$file = substr($file, 0, strpos($file, ".tpp"));
			$object->load_template($file, TRUE);
		}
		
		// Loading the old, deprecated HTM files are a bit more tricky.
		// The old-fashioned HTM templates were structurally identical to the new TPP multi-template packages,
		// but each file only contained one template.
		if ( pathinfo($file, PATHINFO_EXTENSION) === "htm" && $htm === TRUE )
		{
			// Set up a temporary path and copy-paste the file into the temporary folder.
			$tmpdir = ini_get("upload_tmp_dir")."/";
			$token = token();
			$tmppath = $tmpdir.$token.".tpp";
			file_put_contents($tmppath, file_get_contents($file));
			
			// With setting the basedir back and forth, we load the temporary file.
			$object->set_basedir($tmpdir);
			$object->load_template($token, TRUE);
			$object->set_basedir("./");
			
			// Remove the temporary file.
			unlink($tmppath);
		}
	}
	
	// Set the basedir back to the original one
	$object->set_basedir($old_basedir);	
}
?>
