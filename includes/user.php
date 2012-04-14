<?php
/**
 * WhispyForum
 * 
 * /includes/user.php
*/

if ( !defined("WHISPYFORUM") )
	die("Direct opening.");

class user
{
	// The user class defines the user object itself and manages sessions.
	// Methods of this class is used to access the users table seamlessly and easily.
	
	// The following two variables contain the user's data.
	// _userdata is filled at page load (see the _extract_data() function), while
	// _userdata_diff is altered by setValue() and saved to the database at end (-> _save_data()).
	private $_userdata = array();
	private $_userdata_diff = array();
	
	// Userid stores the pointer ID of the user referenced in this instance
	public $userid = 0;
	
	// Current is TRUE if the instance refers for the current user, FALSE if otherwise.
	public $current = TRUE;
	
	// Stores whether the instance opened the user's data only for reading
	private $_readonly = TRUE;
	
	function __construct( $pointer = -1, $readOnly = TRUE )
	{
		/**
		 * Constructor function, gets called every time a new
		 * instance of the user object is created.
		 * 
		 * The $pointer input sets the userID of the user the current instance
		 * refers to. (This way the object can be used to get the data of other users.) 
		 * Pointer should be zero if initialization happens for the current user.
		 * 
		 * If $readOnly is TRUE, the class will be initialized on the user in read-only mode.
		*/
		
		$this->_readonly = $readOnly;
		
		// Load the session if the instance refers to the current user 
		if ( $pointer === 0 )
		{
			// If the class is read-only on the current user, give an error
			if ( $this->_readonly )
			{
				die("Attempted to initialize read-only user object on the concurrent user.");
			}
			
			// If the session is uninitialized (well it should be), initialize it
			if ( !@$_SESSION )
			{
				$this->_init_session();
			} else {
				echo "Session is already initialized.";
			}
		} elseif ( $pointer > 0)
		{
			// If the instance refers to the non-current user, we load their data.
			
			$this->userid = $pointer;
			$this->current = FALSE;
			
			$this->_extract_data();
		}
		
		// Define a constant for get_value()
		@define('USER_NO_KEY', "requested-key-not-present");
	}
	
	private function _init_session()
	{
		/**
		 * Function initializes the session data for the user.
		*/
		
		// Load the session
		session_start();
		Header('Cache-Control: cache');
		
		// If there is no userID present in the session, set up a new session
		// If there is, check for the session's client data and authenticate the user
		if ( !isset($_SESSION['id']) )
		{
			$this->_setup_session();
		} elseif ( $_SESSION['id'] > 0  )
		{
			$cl_side = $this->_check_slient();
			$sv_side = $this->_db_auth_user();
			
			// If both checks confirm that the session is valid, request
			// query and filling of the user's userdata array in the object.
			if ( $cl_side === TRUE && $sv_side === TRUE )
			{
				$this->userid = $_SESSION['id'];
				$this->current = TRUE;
				
				$this->_extract_data();
			}
		}
	}
	
	private function _setup_session()
	{
		/**
		 * This function sets up a basic session for new visitors.
		*/
		
		$_SESSION = array(
			'id'	=>	0,
			'username'	=>	NULL,
			'password'	=>	NULL,
			'ip'	=>	sha1($_SERVER['REMOTE_ADDR']),
			'sessid'	=>	session_id(),
			'user_agent'	=>	sha1($_SERVER['HTTP_USER_AGENT'])
		);
	}
	
	private function _check_client()
	{
		/**
		 * This function compares the visitor's client data and the session data.
		 * The data consists of the client's IP address, user agent and session_id.
		 * 
		 * TRUE is returned if the session clearly matches the current request.
		 * Otherwise, FALSE is returned.
		*/
		
		if ( $_SESSION['ip'] === sha1($_SERVER['REMOTE_ADDR']) &&
			 $_SESSION['user_agent'] === sha1($_SERVER['HTTP_USER_AGENT']) &&
			 $_SESSION['sessid'] === $_COOKIE[session_name()]
			)
		{
			$res = 1;
		} else {
			$res = 0;
		}
		
		return ( $res === 1 ? TRUE : FALSE );
	}
	
	private function _db_auth_user()
	{
		/**
		 * This function checks the session data and the user table
		 * and authenticates the user.
		 * 
		 * TRUE is returned if the user credentials match the ones stored in database.
		 * Otherwise, FALSE is returned.
		*/
		
		global $sql;
		
		$sql->query("SELECT id FROM `users` WHERE 
			`id`=" .$sql->escape($_SESSION['id']). " AND
			`username`='" .$sql->escape($_SESSION['username']). "' AND
			`pwd`='" .$sql->escape($_SESSION['password']). "';");
		
		return ( $sql->num_rows() === 1 ? TRUE : FALSE );
	}
	
	private function _extract_data()
	{
		/**
		 * This function queries the database for the concurrent user's data
		 * and fills the _userdata array with it for further usage.
		*/
		
		global $sql;
		
		if ( $this->current )
		{
			$row = $sql->fetch_array($sql->query("SELECT * FROM `users` WHERE 
				`id`=" .$sql->escape($_SESSION['id']). " AND
				`username`='" .$sql->escape($_SESSION['username']). "' AND
				`pwd`='" .$sql->escape($_SESSION['password']). "' LIMIT 1;"), SQL_ASSOC);
		} elseif ( !$this->current )
		{
			$row = $sql->fetch_array($sql->query("SELECT * FROM `users` WHERE 
				`id`=" .$sql->escape($this->userid). " LIMIT 1;"), SQL_ASSOC);
		}
		
		if ( !$row )
			return FALSE;
		
		// Store the data into the _userdata property.
		foreach ( $row as $k => $v )
		{
			$this->_userdata[$k] = $v;
		}
		
		// Unserialize and parse the `extra_data` field and store that data too.
		$extra = @unserialize($row['extra_data']);
		
		if ( !is_array($extra) )
			$extra = array();
		
		foreach ( $extra as $k => $v )
		{
			$this->_userdata[$k] = $v;
		}
	}
	
	function get_value( $key, $past = FALSE )
	{
		/**
		 * This function returns the requested key ($key) from _userdata.
		 * 
		 * If the said key has been already modified in the current thread, we return the new value,
		 * unless $past is set to TRUE.
		*/
		
		if ( $past === FALSE )
		{
			if ( array_key_exists($key, $this->_userdata_diff) )
			{
				$ret = $this->_userdata_diff[$key];
			} elseif ( array_key_exists($key, $this->_userdata) && !array_key_exists($key, $this->_userdata_diff) )
			{
				$ret = $this->_userdata[$key];
			} elseif ( !array_key_exists($key, $this->_userdata) && !array_key_exists($key, $this->_userdata_diff) )
			{
				$ret = USER_NO_KEY;
			}
		} elseif ( $past === TRUE )
		{
			if ( array_key_exists($key, $this->_userdata) )
			{
				$ret = $this->_userdata[$key];
			} elseif ( !array_key_exists($key, $this->_userdata) )
			{
				$ret = USER_NO_KEY;
			}
		}
		
		return $ret;
	}
	
	function set_value( $key, $value )
	{
		/**
		 * This function sets the $key key of userdata to the new $value value.
		*/
		
		if ( $this->_readOnly )
		{
			echo "Warning! This instance of the user object is running in read-only mode.";
		}
		
		if ( isset($key) && isset($value) )
		{
			if ( array_key_exists($key, $this->_userdata) && $this->_userdata[$key] === $value )
			{
				return TRUE;
			} else {
				$this->_userdata_diff[$key] = $value;
				return TRUE;
			}
		}
	}
	
	function is_readonly()
	{
		/**
		 * This function returns whether the current object is read-only as a boolean.
		*/
		
		return $this->_readOnly;
	}
	
	private function _save_data()
	{
		/**
		 * This function checks the changes of the userdata in memory and updates the database.
		*/
		
		global $sql;
		
		// If there are no keys to modify, we return FALSE.
		if ( count( $this->_userdata_diff ) === 0 )
			return FALSE;
		
		$updates = '';
		$extra_exists = FALSE;
		
		// Analyze the _userdata_diff array to prepare an SQL query for modifications.
		
		foreach ( $this->_userdata_diff as $k => $v )
		{
			if ( $this->_check_for_extra($k) === FALSE )
			{
				$updates .= "`" .$k."`='" .$sql->escape($v)."',\n";
				unset( $this->_userdata_diff[$k] );
			} elseif ( $this->_check_for_extra($k) === TRUE )
			{
				$extra_exists = TRUE;
			}
		}
		
		// If there is at least one 'extra' key modified, update the `extra_data` field too.
		if ( $extra_exists === TRUE )
		{
			// Get the original extra data's array, update it with the new ones, then put it back to database.
			$arrExtra = unserialize( $this->_userdata['extra_data'] );
			
			foreach ( $this->_userdata_diff as $k => $v )
				$arrExtra[$k] = $v;
			
			$updates .= "`extra_data`='" .$sql->escape(serialize( $arrExtra )). "'";
		}
		
		$updates = rtrim($updates, "\n");
		$updates = rtrim($updates, ",");
		
		if ( $this->current )
		{
			$query = "UPDATE `users` SET " .$sql->escape( rtrim($updates, ",") ). " WHERE 
				`id`=" .$sql->escape($_SESSION['id']). " AND
				`username`='" .$sql->escape($_SESSION['username']). "' AND
				`pwd`='" .$sql->escape($_SESSION['password']). "' LIMIT 1;";
		} elseif ( !$this->current )
		{
			$query = "UPDATE `users` SET " .$sql->escape( rtrim($updates, ",") ). " WHERE 
				`id`=" .$sql->escape($this->userid). " LIMIT 1;";
		}
		
		$result = $sql->query($query);
		
		return $result;
	}
	
	private function _check_for_extra( $key )
	{
		/**
		 * This function checks whether the data field ($key) is a seperate
		 * column in the database, or should be put into the extra_data array.
		 * 
		 * Returned value is TRUE if the key is an extra, FALSE if seperate.
		*/
		
		/*** Explanation for this method:
				The `users` table in the database stores the data for every user.
				The user's data is seperated into two types: seperate and extra.
				
				'Seperate' values have their own key/field in the database.
					For example: id, username, password are seperate fields.
				'Extra' values are put into the `extra_data` column of the database
				via the serialize() function, and stored as one data.
				Extra values can store basically anything about the user which does not
				require its own seperate colums, thus, being "extra".
					For example: module configuration like `forum_topics_per_page` is an extra field.
		**/
		
		global $sql;
		
		$sql->query("SHOW COLUMNS FROM `users` LIKE '" .$sql->escape($key). "';");
		
		return ( $sql->num_rows() === 1 ? FALSE : TRUE );
	}
	
	function __destruct()
	{
		/**
		 * Destructor. Called each time the instance is deferenced
		 * (either with unset() or the end of execution).
		*/
		
		// If there is a user logged in and the instance is not set read-only, save its data.
		if ( $this->userid > 0 && !$this->_readonly )
			$this->_save_data();
	}
}
?>
