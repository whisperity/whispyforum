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
		
		global $Ctemplate; // Initialize the template conductor class
		
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
		$_SESSION['osztaly'] = ""; // School class number
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
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "' AND osztaly='" .$Cmysql->EscapeString($_SESSION['osztaly']). "'"));
		
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
		
		global $Ctemplate; // Declare template conductor
		
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
			'AVATAR_FILENAME'	=>	$_SESSION['avatar_filename'], // Avatar file
			'OSZTALY'	=>	$_SESSION['osztaly'], // School class
		), FALSE); // Beginning divs of userbox
		
		$Ctemplate->useStaticTemplate("user/userform_user-cp_link", FALSE); // User control panel link
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "' AND osztaly='" .$Cmysql->EscapeString($_SESSION['osztaly']). "'")); // We query the user's data
		
		// If the user has Admin (3) or higher levels, output link to administrator panel
		if ( $userDBArray['userLevel'] >= 3 )
		{
			$Ctemplate->useStaticTemplate("user/userform_user-ap_link", FALSE); // Output link
		}
		
		if ( FREEUNI_PHASE == 1 )
		{
			// If we're in phase one, give phase one links
			$Ctemplate->useTemplate("user/userform_user-freeuni_links", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'],
				'ADMINLOGO'	=>	($userDBArray['userLevel'] >= 3 ? 
					$Ctemplate->useTemplate("adminlogo", array(
					'THEME_NAME'	=>	$_SESSION['theme_name']
					), TRUE)
				: NULL) // Administrator logo if the user is an admin
			), FALSE); // Free university links
		}
		
		if ( FREEUNI_PHASE == 2 )
		{
			// Give backward-compatible write only performer list (phase 1) link if we're in phase two
			// and if we're admins
			
			if ($userDBArray['userLevel'] >= 3 )
			{
				$Ctemplate->useTemplate("user/userform_user-freeuni2_p1_links", array(
					'THEME_NAME'	=>	$_SESSION['theme_name'],
					'ADMINLOGO'	=>	($userDBArray['userLevel'] >= 3 ? 
						$Ctemplate->useTemplate("adminlogo", array(
							'THEME_NAME'	=>	$_SESSION['theme_name']
						), TRUE)
					: NULL) // Administrator logo if the user is an admin
				), FALSE); // Free university links
			}
			
			// If we're in phase two, give phase two links
			$Ctemplate->useTemplate("user/userform_user-freeuni2_links", array(
				'LINK_NEW_LECTURE'	=>	($userDBArray['userLevel'] >= 3 ?
					$Ctemplate->useTemplate("freeuni2/ub_new_lecture", array(
						'THEME_NAME'	=>	$_SESSION['theme_name']
					), TRUE)
				: NULL), // 'Add new lecture' only if the user is an admin
				'LINK_SELECT_LECTURE'	=>	$Ctemplate->useStaticTemplate("freeuni2/ub_select_lecture", TRUE),
				'LINK_MANAGE_LECTURE'	=>	$Ctemplate->useStaticTemplate("freeuni2/ub_manage_lecture", TRUE),
				'LINK_LIST_STUDENTS'	=>	($userDBArray['userLevel'] >= 2 ?
					$Ctemplate->useTemplate("freeuni2/ub_list_students", array(
						'THEME_NAME'	=>	$_SESSION['theme_name']
					), TRUE)
				: NULL), // 'List students' only if the user is an admin
				'LINK_LIST_LECTURES'	=>	($userDBArray['userLevel'] >= 3 ?
					$Ctemplate->useTemplate("freeuni2/ub_list_lectures", array(
						'THEME_NAME'	=>	$_SESSION['theme_name']
					), TRUE)
				: NULL) // 'List lectures' only if the user is an admin
			), FALSE); // Free university 2 links
		}
		
		if ( FREEUNI_PHASE == 3 )
		{
			// If we're in phase three, give phase three links
			$Ctemplate->useTemplate("user/userform_user-freeuni3_links", array(
				'LINK_SURVEY'	=>	$Ctemplate->useStaticTemplate("freeuni3/ub_survey", TRUE),
				'LINK_RESULTS'	=>	($userDBArray['userLevel'] >= 3 ?
					$Ctemplate->useTemplate("freeuni3/ub_results", array(
						'THEME_NAME'	=>	$_SESSION['theme_name']
					), TRUE)
				: NULL), // 'Show survey results' only if the user is an admin
			), FALSE); // Free university links
		}
		
		$Ctemplate->useTemplate("user/userform_logout", array(
			'RETURN_TO'	=>	$returnLink
		), FALSE); // Logout button
		
		$Ctemplate->useStaticTemplate("user/userform_foot", FALSE); // Close divs
	}
	
	function Login($username, $password, $osztaly)
	{
		/**
		 * This function makes the user logged in
		 * 
		 * @inputs: $username - (string) login username
		 * 			$password - (string) login password (without encryption)
		 */
		
		global $Cmysql; // We need to declare the mySQL class
		
		$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE username='" .$Cmysql->EscapeString($username). "' AND pwd='" .md5($Cmysql->EscapeString($password)). "' AND osztaly='" .$Cmysql->EscapeString($osztaly). "'")); // We query the user's data
		
		if ( $userDBArray == TRUE )
		{
			// If the login info was correct
			// we fill up the session
			$_SESSION['username'] = $userDBArray['username'];
			$_SESSION['pwd'] = $userDBArray['pwd'];
			$_SESSION['uid'] = $userDBArray['id'];
			$_SESSION['log_status'] = "user";
			$_SESSION['log_bool'] = TRUE;
			$_SESSION['osztaly'] = $userDBArray['osztaly']; // School class number
			
			if ( $userDBArray['avatar_filename'] == NULL )
			{
				// If the user does not have an avatar set, make a default avatar for him/her
				//$fnToken = generateHexTokenNoDC(); // Generate token
				
				// We need to copy it to user upload directory to prevent
				// the file in the theme directory to be deleted if the user wants to
				// modify his/her avatar
				
				//copy("themes/" .$_SESSION['theme_name']. "/default_avatar.png", "upload/usr_avatar/temporary_" .$fnToken. ".png"); // Copy the default file from the themeset
				
				// Make the user's avatar the temporary one
				//$_SESSION['avatar_filename'] = "temporary_" .$fnToken. ".png";
				
				// Update the database to make the system remove the temporary file at logout
				//$Cmysql->Query("UPDATE users SET avatar_filename='temporary' WHERE id='" .$userDBArray['id']. "'");
			} else {
				// If the user have a defined avatar, make it his SESSION avatar
				$_SESSION['avatar_filename'] = $userDBArray['avatar_filename'];
				
				//if ( !file_exists("upload/usr_avatar/" .$userDBArray['avatar_filename']) )
				//{
					// If the user have an avatar previously set, but
					// the file does not exists, set a temporary avatar for the user
					
					//$fnToken = generateHexTokenNoDC(); // Generate token
					
					// We need to copy it to user upload directory to prevent
					// the file in the theme directory to be deleted if the user wants to
					// modify his/her avatar
					
					//copy("themes/" .$_SESSION['theme_name']. "/default_avatar.png", "upload/usr_avatar/temporary_" .$fnToken. ".png"); // Copy the default file from the themeset
					
					// Make the user's avatar the temporary one
					//$_SESSION['avatar_filename'] = "temporary_" .$fnToken. ".png";
					
					// Remove the user's avatar setting from the database
					// to make the system remove the temporary file at logout
					//$Cmysql->Query("UPDATE users SET avatar_filename='temporary' WHERE id='" .$userDBArray['id']. "'");
				//}
			}
			
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
}
?>