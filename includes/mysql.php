<?php
/**
 * WhispyForum
 * 
 * /includes/mysql.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

class class_mysql
{
	// Define private variables
	//private $_connected; // boolean, TRUE if there's active connection, FALSE if there isn't
	//private $_resource; // mysql resource
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
		
		global $cfg, $Ctemplate; // We need to initialize the config array and the template class
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"{LANG_SQL_NOCONNECTION}", // Error title
			'BODY'	=>	'{LANG_SQL_DBCONN_TO} <tt>' .$cfg['dbhost']. '</tt> ({LANG_USERNAME_LOWERCASE} <tt>' .$cfg['dbuser']. '</tt>, {LANG_USING_LOWERCASE} {LANG_PASSWORD_LOWERCASE}: <tt>'. ( ($cfg['dbpass'] != NULL) ? '{LANG_YES}' : '{LANG_NO}' ) .'</tt>) {LANG_COULD_NOT_BE_MADE}', // Error text
			'ALT'	=>	"{LANG_SQL_ERROR}" // Alternate picture text
		), FALSE ); // We output an error message
	}
	
	private function __giveDBSelectError()
	{
		/**
		 * This function gives a database selection error message.
		 * Internal use only
		 */
		
		global $cfg, $Ctemplate; // We need to initialize the config array and the template class
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"{LANG_SQL_DBSELECT_ERROR}", // Error title
			'BODY'	=>	'{LANG_SQL_THEDATABASE} <tt>' .$cfg['dbname']. '</tt> {LANG_SQL_COULD_NOT_BE_SELECTED}', // Error text
			'ALT'	=>	"{LANG_SQL_ERROR}" // Alternate picture text
		), FALSE ); // We output an error message
	}
	
	function Disconnect()
	{
		/**
		 * This function closes the active database connection
		 */
		
		@mysql_close($this->_link); // Close
	}
	
	function Query($sQuery)
	{
		/**
		 * This function processes a mySQL query.
		 * If there is an error, it outputs an error message.
		 * 
		 * @inputs: $sQuery - mySQL query string
		 */
		
		// The query's result is automatically put into the _resource variable
		
		$qerror = FALSE; // By default, there are no errors.
		
		$this->_resource = @mysql_query($sQuery)
			or $qerror = TRUE; // $qerror is the error variable
		
		if ( $qerror == TRUE ) // Checking whether there were any erros
		{
			$this->__giveQueryError($sQuery); // Generating query error message
			return FALSE; // Returnign FALSE in case any if() structure needs it
		} elseif ( $qerror == FALSE ) // If there was no errors
		{
			return $this->_resource; // Return result
		}
	}
	
	private function __giveQueryError($sQuery)
	{
		/**
		 * This function gives a query error message.
		 * Internal use only
		 * 
		 * @inputs: $sQuery - mySQL query string
		 */
		
		global $cfg, $Ctemplate; // We need to initialize the config array and the template class
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"{LANG_SQL_EXEC_ERROR}", // Error title
			'BODY'	=>	'{LANG_SQL_THEQUERY} <tt>' .$sQuery. '</tt> {LANG_SQL_COULD_NOT_BE_PROCESSED}<br>{LANG_SQL_ERROR_MSG_WAS} <tt>' .mysql_error(). '</tt>', // Error text
			'ALT'	=>	"{LANG_SQL_EXEC_ERROR}" // Alternate picture text
		), FALSE ); // We output an error message
	}
	
	function EscapeString($sString)
	{
		/**
		 * This function escapes the sql-injection-unsafe characters from a string.
		 * 
		 * @inputs: $sString - string to escape (default is "NOTHING")
		 */
		
		if ( $sString != NULL )
		{
			// If we passed the string parameter
			/**
			 * Generating session data from user table
			 * (in the session manager class)
			 * using this escape function gives errors.
			*/
			
			$sSring = @mysql_real_escape_string($sString); // We escape the string with the help of the SQL server
				//or $this->__giveEscapeStringError($sString); // If we can't, give error
			
			return $sString; // Return the escaped string
		}
	}
	
	private function __giveEscapeStringError($sString)
	{
		/**
		 * This function gives an error message about string escaping.
		 * Internal use only
		 * 
		 * @inputs: $sString - string that failed to get escaped
		 */
		
		global $cfg, $Ctemplate; // We need to initialize the config array and the template class
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_devices_raid.png", // HDDs icon
			'TITLE'	=>	"String failed to escape!", // Error title
			'BODY'	=>	'There were errors escaping string <tt>' .htmlspecialchars($sString). '</tt>.<br>The mySQL error message was <tt>' .mysql_error(). '</tt>', // Error text
			'ALT'	=>	"{LANG_SQL_ERROR}" // Alternate picture text
		), FALSE ); // We output an error message
	}
}

class mysql
{
	/**
	 * Database handler on the mySQL layer.
	*/
	
	// Link identifier for current connection.
	public $link;
	
	// Resource container for the current query
	public $res;
	
	function __construct( $dbhost, $dbuser, $dbpass, $dbname )
	{
		/**
		 * Construction function loads the class and initializes the header of object.
		 * 
		 * On initialization, the instance connects to the database with the arguments set.
		*/
		
		// Connect to the host and select the database
		$this->link = mysql_connect( $dbhost, $dbuser, $dbpass ) or
			die("Connect error.");
		
		mysql_select_db( $dbname, $this->link ) or die("DB select error.");
		
		// Set up the SQL-specific constants
		define('SQL_ASSOC', MYSQL_ASSOC);
		define('SQL_NUM', MYSQL_NUM);
		define('SQL_BOTH', MYSQL_BOTH);
	}
	
	function flush()
	{
		/**
		 * Empties the result and frees the data from memory.
		*/
		
		if ( is_resource($this->res) )
			mysql_free_result($this->res);
	}
	
	function query( $query )
	{
		/**
		 * Runs the argument $query on the database then stores the result in $res.
		 * 
		 * Returns TRUE if the query was successfully executed, FALSE if not.
		*/
		
		$this->flush();
		
		$this->res = @mysql_query($query, $this->link);
		
		if ( !$this->res )
		{
			echo "Query error.";
			return FALSE;
		} else {
			return $this->res;
		}
	}
	
	function fetch_array( $res = NULL, $type = SQL_ASSOC )
	{
		/**
		 * Fetch an array from the given query result with the given type.
		 * If no result is given, it will use the latest.
		 * 
		 * Types can be:
		 *		SQL_ASSOC	-	returned array contains the data in key => value format
		 *		SQL_NUM		-	array contains data in column_id => value (column_id is a number)
		 *		SQL_BOTH	-	the returned array contains the data in each of the ways mentioned above
		 * 
		 * Returns the fetched array or FALSE if failed to fetch.
		*/
		
		if ( isset($res) )
		{
			return @mysql_fetch_array( $res, $type );
		} else {
			if ( isset($this->res) )
			{
				return @mysql_fetch_array( $this->res, $type );
			} else {
				return FALSE;
			}
		}
	}
	
	function num_rows()
	{
		/**
		 * Return the number of rows in the result.
		*/
		
		return @mysql_num_rows($this->res);
	}
	
	function insert_id()
	{
		/**
		 * This function returns the ID of the currently inserted row.
		*/
		
		return @mysql_insert_id($this->link);
	}
	
	function escape( $string )
	{
		/**
		 * This function escapes certain characters to prevent SQL-injection attack.
		*/
		
		return @mysql_real_escape_string($string, $this->link);
	}
	
	function __destruct()
	{
		/**
		 * The destructor releases the object at its dereference.
		 * This function closes the database link and readies the class for dereference.
		*/
		
		$this->flush();
		mysql_close($this->link);
		
		prettyVar($this);
	}
}
?>
