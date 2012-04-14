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
		
		if ( !array_key_exists($stack, $this->_stack) )
		{
			echo "Error! The stack named " .$stack. " does not exist.";
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
			echo "Error! The stack named " .$name. " does not exist.";
			return FALSE;
		}
		
		return $this->_stack[ $name ];
	}
}
?>
