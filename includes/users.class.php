<?php
/**
 * WhispyForum class library - users.class.php
 * 
 * users and session manager
 * 
 * Class used to manage users and session data as well as login processes
 * 
 * WhispyForum
 */
class class_users
{
	function Initialize()
	{
		/**
		 * This function initializes the session and stores user content
		 */
		
		session_start(); // We load the session
		
		if ( $_SESSION == NULL )
		{
			// If there's no present session, we create one
			$this->__createSession();
		} else {
			$this->__checkUserData(); // We check the login status if there's active session
		}
		
		if ( isset($_SESSION['usr_language']) )
		{
			// If the session stores the user's language preference
			include('language/' .$_SESSION['usr_language']. '/language.php'); // Load set language file
		} elseif ( !isset($_SESSION['usr_language']) )
		{
			// If the directory isn't defined,
			include('language/english/language.php'); // Load English language file
		}
	}
	
	private function __createSession()
	{
		/**
		 * This function creates an empty session with default data
		 * Internal use only!
		 */
		
		global $Cmysql; // Hook the SQL class
		
		$_SESSION['username'] = ""; // Empty username
		$_SESSION['pwd'] = ""; // Empty password
		$_SESSION['uid'] = ""; // Empty user id
		$_SESSION['curr_ip'] = $_SERVER['REMOTE_ADDR']; // IP address
		$_SESSION['curr_sessid'] = session_id(); // Session id
		$_SESSION['log_status'] = "guest"; // Guest login
		$_SESSION['log_bool'] = FALSE; // Logged out
		$_SESSION['avatar_filename'] = ""; // Guests does not have avatars
		$_SESSION['theme_name'] = config("theme"); // Default theme name
		$_SESSION['usr_language'] = config("language"); // Default language name
		
		/* Forum */
		$_SESSION['forum_topic_count_per_page'] = config("forum_topic_count_per_page"); // Default number of topics appearing on one page
		$_SESSION['forum_post_count_per_page'] = config("forum_post_count_per_page"); // Default number of posts appearing on one page
		
		/* News */
		$_SESSION['news_split_value'] = config("news_split_value"); // Default number of entries appearing on one page
	}
	
	private function __destroySession()
	{
		/**
		 * This function deletes the session array
		 * 		and the session cookie.
		 * Internal use only!
		 */
		
		$_SESSION = array(); // We delete everything from the session
		
		// We also decide to remove the cookie data
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		
		session_destroy(); // We destroy the session
	}
	
	private function __checkUserData()
	{
		/**
		 * This function compares session data to database
		 * Internal use only!
		 */
		
		global $Cmysql, $Ctemplate; // We need to declare the mySQL and template class
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));
		if ( $userDBArray == FALSE )
		{
			// If we cannot do the query (because it contains false data or empty session)
			// we create the guest status
			
			$_SESSION['log_status'] = "guest";
			$_SESSION['log_bool'] = FALSE;
		} elseif ( $userDBArray == TRUE )
		{
			// If the user is logged in, we check if
			// he or she is logged into the correct session
			// from the correct ip
			
			if ( ( $_SESSION['curr_ip'] != $userDBArray['curr_ip']) || ( $_SESSION['curr_sessid'] != $userDBArray['curr_sessid']) )
			{
				// If the current session ID or the IP address differs from
				// the ID/IP stored in the database
				
				// We output an error message to the user
				// also redirecting him/her to the homepage
				$Ctemplate->useStaticTemplate("user/ip_id_forced_logout", FALSE);
					
				$this->Logout($userDBArray['username']); // We purge the user's session
				
				// We need to recreate a new session
				$this->__createSession();
				
				// We set a specific login status
				$_SESSION['log_status'] = "session_error";
				$_SESSION['log_bool'] = FALSE;
			}
		}
	}
	
	function DoUserForm()
	{
		/**
		 * This function generates a login (guest) or a userbox form (logged in)
		 */
		
		global $Ctemplate; // We need to declare the template class
		
		if ( ( $_SESSION['log_status'] == "guest" ) && ( $_SESSION['log_bool'] == FALSE ) )
		{
			$this->__doLoginForm(); // We create the login form
		} elseif ( ( $_SESSION['log_status'] == "user" ) && ( $_SESSION['log_bool'] == TRUE ) )
		{
			$this->__doUserForm(); // Do user form
		} elseif ( ( $_SESSION['log_status'] == "session_error" ) && ( $_SESSION['log_bool'] == FALSE ) )
		{
			$Ctemplate->useStaticTemplate("user/ip_id_forced_logout_form", FAlSE); // Give a placeholder login box without fields
		}
	}
	
	private function __doLoginForm()
	{
		/**
		 * This function generates the login form
		 * Internal use only!
		 */
		
		global $Ctemplate; // We need to declare the templates class
		
		$Ctemplate->useTemplate("user/loginform", array(
			'RETURN_TO'	=>	selfURL()
		), FALSE);
	}
	
	private function __doUserForm()
	{
		/**
		 * This function generates the user control/logout form
		 * Internal use only!
		 */
		
		global $Ctemplate, $Cmysql; // We need to declare the templates and mySQL class
		
		$Ctemplate->useTemplate("user/userform_head", array(
			'USERNAME'	=>	$_SESSION['username'], // Username (from session)
			'AVATAR_FILENAME'	=>	$_SESSION['avatar_filename'] // Avatar file
		), FALSE); // Beginning divs of userbox
		
		$Ctemplate->useTemplate("user/userform_user-cp_link", array(
			'USERID'	=>	$_SESSION['uid']
		), FALSE); // User control panel link
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data
		
		// If the user has Admin (3) or higher levels, output link to administrator panel
		if ( $userDBArray['userLevel'] >= 3 )
		{
			$Ctemplate->useStaticTemplate("user/userform_user-ap_link", FALSE); // Output link
		}
		
		$Ctemplate->useTemplate("user/userform_logout", array(
			'RETURN_TO'	=>	selfURL()
		), FALSE); // Logout button
		
		$Ctemplate->useStaticTemplate("user/userform_foot", FALSE); // Close divs
	}
	
	function Login($username, $password)
	{
		/**
		 * This function makes the user logged in
		 * 
		 * @inputs: $username - (string) login username
		 * 			$password - (string) login password (without encryption)
		 */
		
		global $Cmysql; // We need to declare the mySQL class
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($username). "' AND pwd='" .$Cmysql->EscapeString($password). "'")); // We query the user's data
		
		if  ( $userDBArray == TRUE ) 
		{
			// If the login info was correct and the user is activated
			
			// Check wether the user is activated
			if ( $userDBArray['activated'] == "1" )
			{
				// If yes
				// we fill up the session
				$_SESSION['username'] = $userDBArray['username'];
				$_SESSION['pwd'] = $userDBArray['pwd'];
				$_SESSION['uid'] = $userDBArray['id'];
				$_SESSION['log_status'] = "user";
				$_SESSION['log_bool'] = TRUE;
				$_SESSION['theme_name'] = $userDBArray['theme'];
				$_SESSION['usr_language'] = $userDBArray['language'];
				
				if ( $userDBArray['avatar_filename'] == NULL )
				{
					// If the user does not have an avatar set, make a default avatar for him/her
					$fnToken = generateHexTokenNoDC(); // Generate token
					
					// We need to copy it to user upload directory to prevent
					// the file in the theme directory to be deleted if the user wants to
					// modify his/her avatar
					
					copy("themes/" .$_SESSION['theme_name']. "/default_avatar.png", "upload/usr_avatar/temporary_" .$fnToken. ".png"); // Copy the default file from the themeset
					
					// Make the user's avatar the temporary one
					$_SESSION['avatar_filename'] = "temporary_" .$fnToken. ".png";
					
					// Update the database to make the system remove the temporary file at logout
					$Cmysql->Query("UPDATE users SET avatar_filename='temporary' WHERE id='" .$userDBArray['id']. "'");
				} else {
					// If the user have a defined avatar, make it his SESSION avatar
					$_SESSION['avatar_filename'] = $userDBArray['avatar_filename'];
					
					if ( !file_exists("upload/usr_avatar/" .$userDBArray['avatar_filename']) )
					{
						// If the user have an avatar previously set, but
						// the file does not exists, set a temporary avatar for the user
						
						$fnToken = generateHexTokenNoDC(); // Generate token
						
						// We need to copy it to user upload directory to prevent
						// the file in the theme directory to be deleted if the user wants to
						// modify his/her avatar
						
						copy("themes/" .$_SESSION['theme_name']. "/default_avatar.png", "upload/usr_avatar/temporary_" .$fnToken. ".png"); // Copy the default file from the themeset
						
						// Make the user's avatar the temporary one
						$_SESSION['avatar_filename'] = "temporary_" .$fnToken. ".png";
						
						// Remove the user's avatar setting from the database
						// to make the system remove the temporary file at logout
						$Cmysql->Query("UPDATE users SET avatar_filename='temporary' WHERE id='" .$userDBArray['id']. "'");
					}
				}
				
				/* Forum */
				$_SESSION['forum_topic_count_per_page'] = $userDBArray['forum_topic_count_per_page']; // Number of topics appearing on one page
				$_SESSION['forum_post_count_per_page'] = $userDBArray['forum_post_count_per_page']; // Number of topics appearing on one page
				
				/* News */
				$_SESSION['news_split_value'] = $userDBArray["news_split_value"]; // Number of entries appearing on one page
				
				$Cmysql->Query("UPDATE users SET curr_ip='" .$_SESSION['curr_ip']. "', curr_sessid='" .$_SESSION['curr_sessid']. "', loggedin=1 WHERE id='" .$userDBArray['id']. "'"); // We update the database to enter the current session data
				return TRUE; // Then return TRUE
			} elseif ( $userDBArray['activated'] == "0" )
			{
				// If the login informations are OK, but the user is not activated, 
				return "activate";
			}
		} elseif ( $userDBArray == FALSE )
		{
			// If there was errors during the query (wrong name/password)
			// or the user is not activated
			return FALSE; // We return false
		}
	}
	
	function Logout($username)
	{
		/**
		 * This function makes the user logged out
		 * 
		 * @inputs: $username - (string) logout username
		 */
		
		global $Cmysql; // We need to declare the mySQL class
		
		$bLogout = $Cmysql->Query("UPDATE users SET curr_ip='0.0.0.0', curr_sessid='', loggedin=0 WHERE id='" .$_SESSION['uid']. "' AND username='" .$Cmysql->EscapeString($username). "'"); // Clear session connections from database
		
		if ( $bLogout == FALSE )
		{
			// If we cannot log out the user
			return FALSE; // We return false to give an error
		} elseif ( $bLogout == TRUE )
		{
			// If we successfully logged out the user
			
			$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT avatar_filename FROM users WHERE username='" .$Cmysql->EscapeString($username). "'")); // We query the filename of the user's avatar
			
			if ( $userDBArray['avatar_filename'] == "temporary" )
			{
				// If the user has no avatar filename set in the database
				// It means that he/she haven't uploaded an avatar.
				// If somebody logins without an avatar set,
				// he/she will be assigned with a temporary avatar
				// (see the Login function)
				
				// After logout, remove the temporary avatar's file
				@unlink("upload/usr_avatar/" .$_SESSION['avatar_filename']);
			}
			
			$this->__destroySession(); // We clear the session
			return TRUE; // We give success
		}
	}
	
	function getLevel()
	{
		/**
		 * The function is used to get a user's level from the database.
		 */
		
		global $Cmysql; // Hook MySQL class here
		
		$userLevel = mysql_fetch_row($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));
		
		if ( $userLevel == FALSE )
		{
			// If the user does not have a return value (meaning the user is a guest)
			// Set the level to 0
			$userLevel = array(0	=> '0');
		}
		
		return $userLevel[0]; // Return the value
	}
}

class user
{ 
	// The user class defines the user object itself and manages sessions.
	// Methods of this class is used to access the users table seamlessly and easily.
	
	private $_userdata = array(); // The user's data (requested and filled at page load)
	private $_userdata_diff = array(); // If a user's data is updated, new values are stored in a new array, saved at destruction time
	
	function __construct()
	{
		/**
		 * Constructor function, gets called every time a new
		 * instance of the user object is created.
		*/
		
		// If the session is uninitialized (well it should be), initialize it
		if ( !@$_SESSION )
		{
			$this->_initSession();
		} else {
			echo "Session is already initialized.";
			$this->_initSession();
		}
	}
	
	private function _initSession()
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
			$this->_setupSession();
		} elseif ( $_SESSION['id'] > 0  )
		{
			$cl_side = $this->_checkClient();
			$sv_side = $this->_dbAuthUser();
			
			// If both checks confirm that the session is valid, request
			// query and filling of the user's userdata array in the object.
			if ( ( $cl_side === TRUE ) && ( $sv_side === TRUE ) )
			{
				$this->_extractData();
			}
		}
	}
	
	private function _setupSession( $incomedata = array() )
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
	
	private function _checkClient()
	{
		/**
		 * This function compares the visitor's client data and the session data.
		 * The data consists of the client's IP address, user agent and session_id.
		 * 
		 * TRUE is returned if the session clearly matches the current request.
		 * Otherwise, FALSE is returned.
		*/
		
		if ( ( $_SESSION['ip'] === sha1($_SERVER['REMOTE_ADDR']) ) &&
			 ( $_SESSION['user_agent'] === sha1($_SERVER['HTTP_USER_AGENT']) ) &&
			 ( $_SESSION['sessid'] === $_COOKIE[session_name()] )
			)
		{
			$res = 1;
		} else {
			$res = 0;
		}
		
		return ( $res === 1 ? TRUE : FALSE );
	}
	
	private function _dbAuthUser()
	{
		/**
		 * This function checks the session data and the user table
		 * and authenticates the user.
		 * 
		 * TRUE is returned if the user credentials match the ones stored in database.
		 * Otherwise, FALSE is returned.
		*/
		
		global $Cmysql;
		
		$res = $Cmysql->Query("SELECT id FROM users WHERE 
			id='" .$Cmysql->EscapeString($_SESSION['id']). "' AND
			username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND
			pwd='" .$Cmysql->EscapeString($_SESSION['password']). "'");
		
		return ( mysql_num_rows($res) === 1 ? TRUE : FALSE );
	}
	
	private function _extractData()
	{
		/**
		 * This function queries the database for the concurrent user's data
		 * and fills the _userdata array with it for further usage.
		*/
		
		global $Cmysql;
		
		$row = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE 
			id='" .$Cmysql->EscapeString($_SESSION['id']). "' AND
			username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND
			pwd='" .$Cmysql->EscapeString($_SESSION['password']). "' LIMIT 1"));
		
		// Store the data into the _userdata property.
		foreach ( $row as $k => $v )
		{
			$this->_userdata[$k] = $v;
		}
		
		// Unserialize and parse the `extra_data` field and store that data too.
		$extra = unserialize($row['extra_data']);
		
		foreach ( $extra as $k => $v )
		{
			$this->_userdata[$k] = $v;
		}
	}
	
	private function _saveData()
	{
		/**
		 * This function checks the changes of the userdata in memory and updates the database.
		*/
		
		global $Cmysql;
		
		// If there are no keys to modify, we return FALSE.
		if ( count( $this->_userdata_diff ) === 0 )
			return FALSE;
		
		$updates = '';
		$extra_exists = FALSE;
		
		// Analyze the _userdata_diff array to prepare an SQL query for modifications.
		
		foreach ( $this->_userdata_diff as $k => $v )
		{
			if ( $this->_checkForExtra($k) === FALSE )
			{
				$updates .= $k."='" .$Cmysql->EscapeString($v)."',\n";
				unset( $this->_userdata_diff[$k] );
			} elseif ( $this->_checkForExtra($k) === TRUE )
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
			
			$updates .= "extra_data='" .$Cmysql->EscapeString(serialize( $arrExtra )). "'";
		}
		
		$query = "UPDATE users SET " .$Cmysql->EscapeString($updates). " WHERE 
			id='" .$Cmysql->EscapeString($_SESSION['id']). "' AND
			username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND
			pwd='" .$Cmysql->EscapeString($_SESSION['password']). "'";
		
		$result = $Cmysql->Query($query);
		
		return $result;
	}
	
	private function _checkForExtra( $key )
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
		
		global $Cmysql;
		
		$field_res = $Cmysql->Query("SHOW COLUMNS FROM users LIKE '" .$Cmysql->EscapeString($key). "'");
		
		return ( mysql_num_rows($field_res) === 1 ? FALSE : TRUE );
	}
	
	function __destruct()
	{
		/**
		 * Destructor. Called each time the instance is deferenced
		 * (either with unset() or the end of execution).
		*/
		
		prettyVar($this);
		
		// If there is a user logged in, save its data.
		if ( $_SESSION['id'] > 0 )
			$this->_saveData();
	}
}
?>
