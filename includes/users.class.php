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
	}
	
	private function __createSession()
	{
		/**
		 * This function creates an empty session with default data
		 * Internal use only!
		 */
		
		$_SESSION['username'] = ""; // Empty username
		$_SESSION['pwd'] = ""; // Empty password
		$_SESSION['uid'] = ""; // Empty user id
		$_SESSION['curr_ip'] = $_SERVER['REMOTE_ADDR']; // IP address
		$_SESSION['curr_sessid'] = session_id(); // Session id
		$_SESSION['log_status'] = "guest"; // Guest login
		$_SESSION['log_bool'] = FALSE; // Logged out
		$_SESSION['avatar_filename'] = ""; // Guests does not have avatars
		$_SESSION['theme_name'] = "winky"; // Default theme name
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
		
		// We generate the return link from the HTTP REQUEST_URI (so we passthru the GET array)
		$returnLink = substr($_SERVER['REQUEST_URI'],1); // We crop the starting / from the returnLink
		
		$Ctemplate->useTemplate("user/loginform", array(
			'RETURN_TO'	=>	$returnLink
		), FALSE);
	}
	
	private function __doUserForm()
	{
		/**
		 * This function generates the user control/logout form
		 * Internal use only!
		 */
		
		global $Ctemplate, $Cmysql; // We need to declare the templates and mySQL class
		
		// We generate the return link from the HTTP REQUEST_URI (so we passthru the GET array)
		$returnLink = substr($_SERVER['REQUEST_URI'],1); // We crop the starting / from the returnLink
		
		$Ctemplate->useTemplate("user/userform_head", array(
			'USERNAME'	=>	$_SESSION['username'], // Username (from session)
			'AVATAR_FILENAME'	=>	$_SESSION['avatar_filename'] // Avatar file (requires implementation)
		), FALSE); // Beginning divs of userbox
		
		$Ctemplate->useStaticTemplate("user/userform_user-cp_link", FALSE); // User control panel link
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data
		
		// If the user has Admin (3) or higher levels, output link to administrator panel
		if ( $userDBArray['userLevel'] >= 3 )
		{
			$Ctemplate->useStaticTemplate("user/userform_user-ap_link", FALSE); // Output link
		}
		
		$Ctemplate->useTemplate("user/userform_logout", array(
			'RETURN_TO'	=>	$returnLink
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
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($username). "' AND pwd='" .md5($Cmysql->EscapeString($password)). "'")); // We query the user's data
		
		if ( $userDBArray == TRUE )
		{
			// If the login info was correct
			// we fill up the session
			$_SESSION['username'] = $userDBArray['username'];
			$_SESSION['pwd'] = $userDBArray['pwd'];
			$_SESSION['uid'] = $userDBArray['id'];
			$_SESSION['log_status'] = "user";
			$_SESSION['log_bool'] = TRUE;
			$_SESSION['avatar_filename'] = $userDBArray['avatar_filename'];
			
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