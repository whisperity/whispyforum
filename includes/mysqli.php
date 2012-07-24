<?php
/**
 * WhispyForum
 * 
 * /includes/mysqli.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

// Constants for db::fetch_array().
define('SQL_ASSOC', 1);
define('SQL_NUM', 2);
define('SQL_BOTH', 3);

class db_mysqli
{
	/**
	 * Database handler to connect to MySQL servers using the mysqli extension.
	*/
	
	// Link identifier for current connection.
	private $_link;
	
	// Resource container for the current query
	private $_res;
	
	// $_fetch_types contain the type constants for fetch_array()
	private $_fetch_types = array(SQL_ASSOC	=>	MYSQLI_ASSOC, SQL_NUM	=>	MYSQLI_NUM, SQL_BOTH	=>	MYSQLI_BOTH);
	
	public function __construct( $dbhost, $dbuser, $dbpass, $dbname )
	{
		/**
		 * Construction function loads the class and initializes the header of object.
		 * 
		 * On initialization, the instance connects to the database with the arguments set:
		 * 		dbhost:	database server host (usually 'localhost')
		 * 		dbuser:	database username
		 * 		dbpass:	password of user
		 * 		dpname:	name of the database to use
		*/
		
		// Connect to the host and select the database
		$this->_link = @mysqli_connect( $dbhost, $dbuser, $dbpass, $dbname );
		
		if ( mysqli_connect_error() )
			die("Unable to connect to database " .$dbuser. "@" .$dbhost. " (using password: " .(isset($dbpass) ? "yes" : "no"). "): " .mysqli_connect_errno(). " - " .mysqli_connect_error());
		
		mysqli_set_charset($this->_link, mysqli_character_set_name($this->_link) );
	}
	
	public static function test_connection ( $dbhost, $dbuser, $dbpass )
	{
		/**
		 * This function initiates a test connection using the same method as it would be
		 * using the __construct() fuction, but immediately closes the connection.
		 * 
		 * TRUE returned if the connection is successful. If not, an error message string is returned.
		*/
		
		$link = @mysqli_connect($dbhost, $dbuser, $dbpass);
		
		if ( mysqli_connect_error() )
		{
			return mysqli_connect_errno(). " - " .mysqli_connect_error();
		} else {
			@mysqli_close($link);
			return TRUE;
		}
	}
	
	public function query( $query )
	{
		/**
		 * Runs the argument $query on the database then stores the result in $res.
		 * 
		 * Returns TRUE if the query was successfully executed, FALSE if not.
		*/
		
		$res = mysqli_query($this->_link, $query);
		
		if ( $res === FALSE )
			print( ambox('WARNING', lang_key("QUERY ERROR", array(
				'ERROR'	=>	mysqli_error($this->_link),
				'QUERY'	=>	$query) )) );
		
		$this->_res = $res;
		return $res;
	}
	
	public function fetch_array( $res = NULL, $type = SQL_BOTH )
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
		
		if ( isset($res) && @get_class($res) === "mysqli_result" )
		{
			return mysqli_fetch_array( $res, $this->_fetch_types[$type] );
		} else {
			if ( $res === NULL && @get_class($res) === "mysqli_result" )
			{
				return mysqli_fetch_array( $this->_res, $this->_fetch_types[$type] );
			} else {
				return FALSE;
			}
		}
	}
	
	public function num_rows( $res = NULL )
	{
		/**
		 * Return the number of rows in the result.
		*/
		
		return mysqli_num_rows( (isset($res) ? $res : $this->_res) );
	}
	
	public function insert_id()
	{
		/**
		 * This function returns the ID of the currently inserted row.
		*/
		
		return mysqli_insert_id($this->_link);
	}
	
	public function escape( $str )
	{
		/**
		 * This function escapes certain characters to prevent SQL-injection attack.
		*/
		
		return mysqli_real_escape_string($this->_link, $str);
	}
	
	public function seek( $res = NULL, $row = 0 )
	{
		/**
		 * This function seeks the set $res result (or the internal property) to row number $row
		*/
		
		return mysqli_data_seek( (isset($res) ? $res : $this->_res ), $row);
	}
	
	public function free_result( $res = NULL )
	{
		/**
		 * Frees the result container associated with $res.
		*/
		
		mysqli_free_result( (isset($res) ? $res : $this->_res) );
	}
		
	public function __destruct()
	{
		/**
		 * The destructor releases the object at its dereference.
		 * This function closes the database link and readies the class for dereference.
		*/
		
		if ( @get_class($this->_link) === "mysqli" )
			mysqli_close($this->_link);
	}
}
?>
