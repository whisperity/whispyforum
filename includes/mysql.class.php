<?php
/**
 * WhispyForum class library - mysql.class.php
 * 
 * mySQL database layer
 * 
 * Class used to connect and maintain database queries
 * 
 * WhispyForum
 */

class class_mysql
{
	// Define private variables
	private $_connected; // boolean, TRUE if there's active connection, FALSE if there isn't
	private $_resource; // mysql resource
	private $_link; // database link identifier
	
	function Connect()
	{
		/**
		 * This function is called by load.php and makes an initial database connection.
		 * If the connection cannot be made, it gives error message.
		 */

		global $cfg; // First, we need to load the configuration array
		
		$this->_link = @mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']) // connect to database
			or $this->__giveConnectionError(); // If we can't, give error message
		
		if ( $this->_link ) // _link is set if there's connection, so if there is, we select the set database
		{
			@mysql_select_db($cfg['dbname']) // We select the database set in config
				or $this->__giveDBSelectError(); // If we can't, give error message
		}
	}
	
	function TestConnection()
	{
		/**
		 * This function makes a database connection for install and testing purposes.
		 * 
		 * This function is usally called by install.php.
		 * 
		 * It return a boolean (true/false) variable based on success.
		 */
		
		global $cfg; // First, we need to load the configuration array
		
		$this->_link = @mysql_connect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']); // connect to database
		
		if ( $this->_link ) // _link is set if there's connection, so if there is, we return TRUE
		{
			return TRUE;
		} elseif ( !$this->_link ) // if there isn't _link, there's no connection, we return FALSE
		{
			return FALSE;
		}
	}
	
	private function __giveConnectionError()
	{
		/**
		 * This function gives a connection error message.
		 * Internal use only
		 */
		
		global $cfg; // We need to initialize the config array
		$Ctemplate = new class_template; // We need to declare the templates class
		
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	"winky", // Theme name
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"Unable to connect!", // Error title
			'BODY'	=>	'Database connection to <tt>' .$cfg['dbhost']. '</tt> (user <tt>' .$cfg['dbuser']. '</tt>, using password: <tt>'. ( ($cfg['dbpass'] != NULL) ? 'yes' : 'no' ) .'</tt>) could not be made.', // Error text
			'ALT'	=>	"Connection error" // Alternate picture text
	), FALSE ); // We output an error message
	}
	
	private function __giveDBSelectError()
	{
		/**
		 * This function gives a database selection error message.
		 * Internal use only
		 */
		
		global $cfg; // We need to initialize the config array
		$Ctemplate = new class_template; // We need to declare the templates class
		
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	"winky", // Theme name
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"Unable to select database!", // Error title
			'BODY'	=>	'The specified database <tt>' .$cfg['dbname']. '</tt> could not be selected.', // Error text
			'ALT'	=>	"mySQL error" // Alternate picture text
	), FALSE ); // We output an error message
	}
}
?>
