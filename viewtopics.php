<?php
 /**
 * WhispyForum script file - viewtopics.php
 * 
 * Listing topics and managing topic-specific modifying (edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/topics_head", FALSE); // Header

/* Generate the global POST or GET id variable */
if ( isset($_POST['id']) )
{
	// The POSTed value always has more priority
	$id = $_POST['id'];
} else {
	// If POST is undefined (empty, NULL), we search for the lower priority GET
	if ( isset($_GET['id']) )
	{
		// If GET is there, make it the ID
		$id = $_GET['id'];
	} else {
		// If there's no GET nor POST, it will be NULL
		$id = NULL;
	}
}
/* Everytime we require the ID of the forum the topics are in, we use the $id variable */

if ( !isset($id) )
{
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
		'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
		'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("forum/topics_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// Get the current user's level
$uLvl = mysql_fetch_row($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));

// Query the minimal level for the forum
$fMLvl = mysql_fetch_row($Cmysql->Query("SELECT minLevel FROM forums WHERE id='" .$Cmysql->EscapeString($id). "'"));

if ( $uLvl[0] < $fMLvl[0] )
{
	// If the user is on lower level
	// than the currently required to view the forum
	
	// First, generate the variable which stores the
	// name of the level to be on to view the forum.
	
	switch ($fMLvl[0]) // Minimal level required to view the forum
	{
		case 0:
			// Guest
			/* It's really purposeless, the default minimum is guest and users cannot be lower than guests */
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_GUEST}'];
			break;
		case 1:
			// User
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_USER}'];
			break;
		case 2:
			// Moderator
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_MODERATOR}'];
			break;
		case 3:
			// Administrator
			$minLName = $wf_lang['{LANG_TOPICS_THIS_FORUM_REQUIRES_ADMINISTRATOR}'];
			break;
	}
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	$minLName, // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
	), FALSE ); // Give rights error
} elseif ( $uLvl[0] >= $fMLvl[0] )
{
	// The user has the rights to view the topic list
	
	$Ctemplate->useTemplate("forum/topics_table_open", array(
		'CREATE_NEW_TOPIC'	=>	$Ctemplate->useTemplate("forum/topics_create_new", array(
				'FORUM_ID'	=>	$id // ID of the forum we're creating the theme into
			), TRUE), // Output button of new topic creation
		'ADMIN_ACTIONS'	=>	($uLvl[0] >= 2 ? 
			$Ctemplate->useStaticTemplate("forum/forums_admin_actions", TRUE) // Return the header
		: NULL ) // Output header for admin actions only if the user is moderator or higher
	), FALSE); // Open the table and output header
	
	$topic_Hresult = $Cmysql->Query("SELECT * FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "' AND highlighted='1'"); // Query highlighted tables in the set forum
	
	while ( $Hrow = mysql_fetch_assoc($topic_Hresult) )
	{
		// Get the username of who created the topic
		$Hcreator_uName = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$Cmysql->EscapeString($Hrow['createuser']). "'"));
		
		// Output rows for every table
		$Ctemplate->useTemplate("forum/topics_table_row", array(
			'TYPE'	=>	"highlight", // Different theme for highlighted topics
			'LOCKED'	=>	($Hrow['locked'] == 1 ? "_locked" : ""), // The icon will be a locked icon if the thread is locked
			'ALT'	=>	($Hrow['locked'] == 1 ? "{LANG_TOPICS_HIGHLIGHTED_LOCKED}" : "{LANG_TOPICS_HIGHLIGHTED}"), // Alternate picture text
			'TITLE'	=>	$Hrow['title'], // Title of the topic
			'CREATOR'	=>	$Hcreator_uName[0], // Username of creator
			'CREATION_DATE'	=>	fDate($Hrow['createdate']), // Creation timestamp
		), FALSE); // Output row
	}
	
	$topic_result = $Cmysql->Query("SELECT * FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "' AND highlighted='0'"); // Query "normal" tables in the set forum
	
	while ( $row = mysql_fetch_assoc($topic_result) )
	{
		// Get the username of who created the topic
		$creator_uName = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$Cmysql->EscapeString($row['createuser']). "'"));
		
		// Output rows for every table
		$Ctemplate->useTemplate("forum/topics_table_row", array(
			'TYPE'	=>	"normal", // Different theme for normal topics
			'LOCKED'	=>	($row['locked'] == 1 ? "_locked" : ""), // The icon will be a locked icon if the thread is locked
			'ALT'	=>	($row['locked'] == 1 ? "{LANG_TOPICS_NORMAL_LOCKED}" : "{LANG_TOPICS_NORMAL}"), // Alternate picture text
			'TITLE'	=>	$row['title'], // Title of the topic
			'CREATOR'	=>	$creator_uName[0], // Username of creator
			'CREATION_DATE'	=>	fDate($row['createdate']), // Creation timestamp
		), FALSE); // Output row
	}
	
	$Ctemplate->useStaticTemplate("forum/topics_table_close", FALSE); // Close the table
}

$Ctemplate->useStaticTemplate("forum/topics_foot", FALSE); // Footer
DoFooter();
?>