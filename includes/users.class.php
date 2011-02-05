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
			//$this->__; // Do user form
		}
	}
	
	private function __doLoginForm()
	{
		global $Ctemplate; // We need to declare the templates class
		
		// We generate the return link from the HTTP REQUEST_URI (so we passthru the GET array)
		$returnLink = substr($_SERVER['REQUEST_URI'],1); // We crop the starting / from the returnLink
		
		$Ctemplate->useTemplate("user/loginform", array(
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
			
			return TRUE; // Then return TRUE
		} elseif ( $userDBArray == FALSE )
		{
			// If there was errors during the query (wrong name/password)
			return FALSE; // We return false
		}
	}
}
?>