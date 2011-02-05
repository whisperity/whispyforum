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
echo date('l jS \of F Y H:i:s')." users lodaded\n<br>"; // DEV

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
	}
	
	private function __createSession()
	{
		/**
		 * This function creates an empty session with default data
		 * Internal use only!
		 */
		
		$_SESSION['username'] = "";
		$_SESSION['pwd'] = "";
		$_SESSION['uid'] = "";
		$_SESSION['curr_ip'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['curr_sessid'] = session_id();
		$_SESSION['log_status'] = "guest";
		$_SESSION['log_bool'] = FALSE;
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
		
		global $Cmysql;
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .mysql_real_escape_string($_SESSION['username']). "' AND pwd='" .mysql_real_escape_string($_SESSION['pwd']). "'"));
		if ( $userDBArray == FALSE )
		{
			// If we cannot do the query (because it contains false data or empty session)
			// we create the guest status
			
			$_SESSION['log_status'] = "guest";
			$_SESSION['log_bool'] = FALSE;
		}
	}
	
	function DoUserForm()
	{
		/**
		 * This function generates a login (guest) or a userbox form (logged in)
		 */
		
		if ( ( $_SESSION['log_status'] == "guest" ) && ( $_SESSION['log_bool'] == FALSE ) )
		{
			$this->__doLoginForm(); // We create the login form
		} elseif ( ( $_SESSION['log_status'] == "user" ) && ( $_SESSION['log_bool'] == TRUE ) )
		{
			$this->__doUserForm(); // Do user form
		}
	}
	
	private function __doLoginForm()
	{
		/**
		 * This function generates the login form
		 * Internal use only!
		 */
		
		global $Ctemplate; // We need to declare the templates class
		
		// We generate the return link from the HTTP REQUEST_URI (so we passthru the GET array)
		$returnLink = substr($_SERVER['REQUEST_URI'],1); // We crop the starting / from the returnLink
		
		$Ctemplate->useTemplate("user/loginform", array(
			"RETURN_TO"	=>	$returnLink
		), FALSE);
	}
	
	private function __doUserForm()
	{
		/**
		 * This function generates the logout form
		 * Internal use only!
		 */
		
		global $Ctemplate; // We need to declare the templates class
		
		// We generate the return link from the HTTP REQUEST_URI (so we passthru the GET array)
		$returnLink = substr($_SERVER['REQUEST_URI'],1); // We crop the starting / from the returnLink
		
		$Ctemplate->useTemplate("user/logoutform", array(
			"RETURN_TO"	=>	$returnLink
		), FALSE);
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
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .mysql_real_escape_string($username). "' AND pwd='" .md5(mysql_real_escape_string($password)). "'")); // We query the user's data
		
		if ( $userDBArray == TRUE )
		{
			// If the login info was correct
			// we fill up the session
			$_SESSION['username'] = $userDBArray['username'];
			$_SESSION['pwd'] = $userDBArray['pwd'];
			$_SESSION['uid'] = $userDBArray['id'];
			$_SESSION['log_status'] = "user";
			$_SESSION['log_bool'] = TRUE;
			
			$Cmysql->Query("UPDATE users SET curr_ip='" .$_SESSION['curr_ip']. "', curr_sessid='" .$_SESSION['curr_sessid']. "', loggedin=1 WHERE id='" .$userDBArray['id']. "'"); // We update the database to enter the current session data
			return TRUE; // Then return TRUE
		} elseif ( $userDBArray == FALSE )
		{
			// If there was errors during the query (wrong name/password)
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
		
		$bLogout = $Cmysql->Query("UPDATE users SET curr_ip='0.0.0.0', curr_sessid='', loggedin=0 WHERE id='" .$_SESSION['uid']. "'"); // Clear session connections from database
		
		if ( $bLogout == FALSE )
		{
			// If we cannot log out the user
			return FALSE; // We return false to give an error
		} elseif ( $bLogout == TRUE )
		{
			// If we successfully logged out the user
			
			$this->__destroySession(); // We clear the session
			return TRUE; // We give success
		}
		
	}
}
?>