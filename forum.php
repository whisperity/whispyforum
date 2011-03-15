<?php
 /**
 * WhispyForum script file - forum.php
 * 
 * Listing forums and managing forum-specific modifying (add, edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/forums_head", FALSE); // Header

// Get the current user's level
$uLvl = mysql_fetch_row($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));

/* Creating new forum */
if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "newforum" ) )
{
	// Adding new forum
	if ( $uLvl[0] < 3 )
	{
		// If the user does not have rights to add new forum
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_REQUIRED_ADMIN}", // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
		), FALSE ); // We give an unaviable error
	} elseif ( $uLvl[0] >= 3 )
	{
		// Access granted :)
		if ( !isset($_POST['newforum_do']) )
		{
			// If we requested the form to add new forum
			
			if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
			{
				// We output the form with data returned (user doesn't have to enter it again)
				$Ctemplate->useTemplate("forum/forums_create_form", array(
					'TITLE'	=>	$_POST['title'], // Forum's title
					'DESC'	=>	$_POST['desc'], // Description
					'0_CHECKED'	=>	($_POST['minlevel'] == 0 ? " checked" : ""), // User min. level 0 (guest)
					'1_CHECKED'	=>	($_POST['minlevel'] == 1 ? " checked" : ""), // User min. level 1 (user)
					'2_CHECKED'	=>	($_POST['minlevel'] == 2 ? " checked" : ""), // User min. level 2 (moderator)
					'3_CHECKED'	=>	($_POST['minlevel'] == 3 ? " checked" : "") // User min. level 3 (administrator)
				), FALSE);
			} else {
				// We output general form
				$Ctemplate->useTemplate("forum/forums_create_form", array(
					'TITLE'	=>	"", // Forum's title
					'DESC'	=>	"", // Description
					'0_CHECKED'	=>	" checked", // User min. level 0 (guest)
					'1_CHECKED'	=>	"", // User min. level 1 (user)
					'2_CHECKED'	=>	"", // User min. level 2 (moderator)
					'3_CHECKED'	=>	"", // User min. level 3 (administrator)
				), FALSE);
			}
		} elseif ( ( isset($_POST['newforum_do']) ) && ( $_POST['newforum_do'] == "yes") )
		{
			// If we added the data and requested SQL query
			
			// First, we check whether every required variables were entered
			if ( $_POST['title'] == NULL ) // Forum's title
			{
				$Ctemplate->useTemplate("forum/forums_create_variable_error", array(
					'VARIABLE'	=>	"{LANG_FORUMS_TITLE}", // Errornous variable name
					'TITLE'	=>	$_POST['title'], // Forum's title (should be empty)
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE);
				
				// We terminate the script
				$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
				DoFooter();
				exit;
			}
			
			// Every variable has value, do the SQL query.
			$fCreate = $Cmysql->Query("INSERT INTO forums(title, info, minLevel, createdate) VALUES(".
			"'" .$Cmysql->EscapeString($_POST['title']). "',
				'" .$Cmysql->EscapeString($_POST['desc']). "',
				'" .$Cmysql->EscapeString($_POST['minlevel']). "', " .time(). ")");
			
			// $fCreate is TRUE if we succeeded
			// $fCreate is FALSE if we failed
			
			if ( $fCreate == FALSE )
			{
				// Failed to create the menu
				$Ctemplate->useTemplate("forum/forums_create_error", array(
					'TITLE'	=>	$_POST['title'], // Forum's title (should be empty)
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE); // Output a retry form
			} elseif ( $fCreate == TRUE )
			{
				// Created the menu
				$Ctemplate->useTemplate("forum/forums_create_success", array(
					'TITLE'	=>	$_POST['title'] // Menu title
				), FALSE); // Output a success form
			}
		}
	}
}
/* Creating new forum */

/* Listing forums */
if ( !isset($_POST['action']) )
{
	// Do listing only if we opened the list in plain mode
	
	$Ctemplate->useTemplate("forum/forums_table_open", array(
		'ADMIN_CREATE_NEW_FORUM'	=>	($uLvl[0] >= 3 ?
			$Ctemplate->useStaticTemplate("forum/forums_admin_createnew", TRUE) // Return the button
		: NULL ), // Output buttor for new forum only if the user is admin or higher
		'ADMIN_ACTIONS'	=>	($uLvl[0] >= 2 ? 
			$Ctemplate->useStaticTemplate("forum/forums_admin_actions", TRUE) // Return the header
		: NULL ) // Output header for admin actions only if the user is moderator or higher
	), FALSE); // Open the table and output header
	
	$forums_data = $Cmysql->Query("SELECT * FROM forums"); // Query down the forums
	
	while ( $row = mysql_fetch_assoc($forums_data) )
	{
		// Going through every row of the returned dataset,
		// output rows for forums
		var_dump($row);
	}
	
	$Ctemplate->useStaticTemplate("forum/forums_table_close", FALSE); // Close the table
}
/* Listing forums */

$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
DoFooter();
?>