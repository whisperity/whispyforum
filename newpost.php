<?php
 /**
 * WhispyForum script file - newpost.php
 * 
 * Adding new post
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/posts_create_head", FALSE); // Header

if ( !isset($_POST['id']) )
{
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
		'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
		'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("forum/topics_create_foot", FALSE); // Footer
	DoFooter();
	exit;
}

$tName = mysql_fetch_row($Cmysql->Query("SELECT title, forumid FROM topics WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'")); // Title of the topic the posts are in
		
if ( $tName == FALSE )
{
	// If the selected forum does not exist, give error
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_error.png", // Error X icon
		'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
		'BODY'	=>	"{LANG_POSTS_CREATE_TOPIC_DOES_NOT_EXIST}", // Error text
		'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate the script
	$Ctemplate->useStaticTemplate("forum/posts_create_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// Get the current user's level
$uLvl = mysql_fetch_row($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'"));

if ( $uLvl == FALSE )
{
	// If the user does not have a return value (meaning the user is a guest)
	// Set the level to 0
	$uLvl = array(0	=>	'0');
}

// Query the minimal level for the forum
$fMLvl = mysql_fetch_row($Cmysql->Query("SELECT minLevel FROM forums WHERE id='" .$tName[1]. "'"));

if ( ( $uLvl[0] < $fMLvl[0] ) && ( $uLvl[0] != "0" ) )
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
} elseif ( ( $uLvl[0] >= $fMLvl[0] ) && ( $uLvl[0] != "0" ) )
{
	// The user has the rights to view the post list, thus has rights to create one
	
	if ( !isset($_POST['post_do']) )
	{
		$fTitle = mysql_fetch_row($Cmysql->Query("SELECT title FROM forums WHERE id='" .$tName[1]. "'")); // Query the title of the forum
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("forum/posts_create_form", array(
				'FORUMID'	=>	$tName[1], // ID of the forum
				'FORUM_NAME'	=>	$fTitle[0], // Header of the forum
				'TOPICID'	=>	$_POST['id'], // ID of the topic
				'TOPIC_NAME'	=>	$tName[0], // Title of the topic
				'POST_TITLE'	=>	$_POST['post_title'], // Post title
				'POST_CONTENT'	=>	$_POST['post_content'], // Post content
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("forum/posts_create_form", array(
				'FORUMID'	=>	$tName[1], // ID of the forum
				'FORUM_NAME'	=>	$fTitle[0], // Header of the forum
				'TOPICID'	=>	$_POST['id'], // ID of the topic
				'TOPIC_NAME'	=>	$tName[0], // Title of the topic
				'POST_TITLE'	=>	"", // Post title (nothing)
				'POST_CONTENT'	=>	"", // Post content (nothing)
			), FALSE);
		}
	}
	
	if ( @$_POST['post_do'] == "do" )
	{
		// Check for missing mandatory variables...

		if ( $_POST['post_content'] == NULL ) // Post body
		{
			$Ctemplate->useTemplate("forum/posts_create_variable_error", array(
				'VARIABLE'	=>	"{LANG_POSTS_POST}", // Missing variable's name
				'TOPICID'	=>	$_POST['id'],
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'], // Post body (should be empty)
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("forum/posts_create_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Every variable is entered, doing SQL work
		
		$post_create = $Cmysql->Query("INSERT INTO posts(topicid, forumid, title, createuser, createdate, content) VALUES (
			'" .$Cmysql->EscapeString($_POST['id']). "',
			'" .$Cmysql->EscapeString($tName[1]). "',
			'" .$Cmysql->EscapeString($_POST['post_title']). "',
			'" .$Cmysql->EscapeString($_SESSION['uid']). "', '" .time(). "',
			'" .$Cmysql->EscapeString($_POST['post_content']). "')"); // Post adding (to the previously created topic)
		
		if  ( $post_create == FALSE )
		{
			$Ctemplate->useTemplate("forum/posts_create_error", array(
				'TOPICID'	=>	$_POST['id'],
				'POST_TITLE'	=>	$_POST['post_title'], // Title of the post
				'POST_CONTENT'	=>	$_POST['post_content'] // Post body
			), FALSE); // Give error if we failed the creation
		} elseif ( $post_create == TRUE )
		{
			$Ctemplate->useTemplate("forum/posts_create_success", array(
				'TOPICID'	=>	$_POST['id'],
				'TITLE'	=>	(@$_POST['post_title'] == NULL ? "No title" : @$_POST['post_title']), // Title of the post
			), FALSE); // Give success if we did it!
		}
	}
} elseif ( $uLvl[0] == "0" )
{
	// If the user is a guest, even though he/she can view the forum
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	"{LANG_POSTS_CREATE_GUEST_ERROR}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
	), FALSE ); // Give rights error
}

$Ctemplate->useStaticTemplate("forum/posts_create_foot", FALSE); // Footer
DoFooter();
?>