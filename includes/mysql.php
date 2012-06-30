<?php
/**
 * WhispyForum
 * 
 * /includes/mysql.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

define('SQL_ASSOC', 1);
define('SQL_NUM', 2);
define('SQL_BOTH', 3);

class mysql
{
	/**
	 * Database handler on the mySQL layer.
	*/
	
	// Link identifier for current connection.
	public $link;
	
	// Resource container for the current query
	public $res;
	
	// $_fetch_types contain the type constants for fetch_array()
	private $_fetch_types = array(1	=>	MYSQL_ASSOC, 2	=>	MYSQL_NUM, 3	=>	MYSQL_BOTH);
	
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
		$this->link = mysql_connect( $dbhost, $dbuser, $dbpass ) or
			die("Connect error.");
		
		mysql_select_db( $dbname, $this->link ) or
			die("DB select error.");
	}
	
	public function query( $query )
	{
		/**
		 * Runs the argument $query on the database then stores the result in $res.
		 * 
		 * Returns TRUE if the query was successfully executed, FALSE if not.
		*/
		
		$res = @mysql_query($query, $this->link)
			or print( ambox('WARNING', lang_key("QUERY ERROR", array(
				'ERROR'	=>	mysql_error(),
				'QUERY'	=>	$query) )) );
		
		$this->res = $res;
		return $res;
	}
	
	public function fetch_array( $res = NULL, $type = SQL_ASSOC )
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
		
		if ( isset($res) && is_resource($res) )
		{
			return @mysql_fetch_array( $res, $this->_fetch_types[$type] );
		} else {
			if ( isset($this->res) )
			{
				return @mysql_fetch_array( $this->res, $this->_fetch_types[$type] );
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
		
		return @mysql_num_rows( (isset($res) ? $res : $this->res) );
	}
	
	public function insert_id()
	{
		/**
		 * This function returns the ID of the currently inserted row.
		*/
		
		return @mysql_insert_id($this->link);
	}
	
	public function escape( $string )
	{
		/**
		 * This function escapes certain characters to prevent SQL-injection attack.
		*/
		
		return @mysql_real_escape_string($string, $this->link);
	}
	
	public function seek( $res = NULL, $row = 0 )
	{
		/**
		 * This function seeks the set $res result (or the internal property) to row number $row
		*/
		
		return @mysql_data_seek( (isset($res) ? $res : $this->res ), $row);
	}
	
	public function __destruct()
	{
		/**
		 * The destructor releases the object at its dereference.
		 * This function closes the database link and readies the class for dereference.
		*/
		
		@mysql_close($this->link);
	}
}
?>
