<?php
 /**
 * WhispyForum script file - viewposts.php
 * 
 * Listing posts and managing post-specific modifying (edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("forum/posts_head", FALSE); // Header

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
/* Everytime we require the ID of the topic the posts are in, we use the $id variable */

if ( !isset($id) )
{
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
		'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
		'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("forum/posts_foot", FALSE); // Footer
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
	
	$topic_array = mysql_fetch_assoc($Cmysql->Query("SELECT title, forumid, locked FROM topics WHERE id='" .$Cmysql->EscapeString($id). "'")); // Data of the topic
	
	$fName = mysql_fetch_row($Cmysql->Query("SELECT title FROM forums WHERE id='" .$topic_array['forumid']. "'")); // $fName[0] is the name of the forum the topic (containing the posts) is in
	
	$tName = $topic_array['title']; // Name of the topic
	
	$Ctemplate->useTemplate("forum/posts_list_head", array(
		'FORUMID'	=>	$topic_array['forumid'], // ID of the forum the topic is in
		'FORUM_NAME'	=>	$fName[0],
		'TOPIC_NAME'	=>	$tName,
		'NEW_POST'	=>	($topic_array['locked'] == 0 ? 
			$Ctemplate->useTemplate("forum/posts_new", array(
				'TOPIC_ID'	=>	$id
			), TRUE) : $Ctemplate->useStaticTemplate("forum/posts_new_locked", TRUE)), // New post button (or error if the topic is locked)
	), FALSE); // Output opening of posts table
	
	/**
	 * The post list is split, based on user setting.
	 * Because of it, we need to generate a page switcher by using the 'LIMIT start, count'
	 * syntax.
	 */
	
	$usr_post_split_value = 2; // dev value // Use the user's preference (queried from session)
	
	// Query the total number of NORMAL (not highlighted) topics in the current forum
	$post_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM posts WHERE topicid='" .$Cmysql->EscapeString($id). "'"));
	
	// Generate the number of pages (we need to ceil it up because we need to have integer pages)
	$post_pages = ceil($post_count[0] / $usr_post_split_value);
	
	// Generate the start_at value
	if ( @$_GET['start_at'] == NULL )
	{
		// If the value is missing, we will assume 0 as the start
		$post_start = 0;
	} elseif ( @$_GET['start_at'] != NULL )
	{
		// If we have start value, multiply it with the split value so it'll show the correct page
		$post_start = $_GET['start_at'] * $usr_post_split_value;
	}
	
	$post_result = $Cmysql->Query("SELECT * FROM posts WHERE topicid='" .$Cmysql->EscapeString($id). "' ORDER BY createdate ASC LIMIT " .$post_start.", " .$Cmysql->EscapeString($usr_post_split_value)); // Query "normal" tables in the set forum (splitted)
	
	while ( $row = mysql_fetch_assoc($post_result) )
	{
		// Query poster's data
		$uData = mysql_fetch_assoc($Cmysql->Query("SELECT username, regdate, loggedin, avatar_filename FROM users WHERE id='" .$Cmysql->EscapeString($row['createuser']). "'"));
		
		if ( $uData['avatar_filename'] == NULL )
		{
			$poster_avatar = "themes/{THEME_NAME}/default_avatar.png";
		} else {
			// If the user have a defined avatar, make it his SESSION avatar
			$poster_avatar = "upload/usr_avatar/" .$uData['avatar_filename'];
			
			if ( !file_exists("upload/usr_avatar/" .$uData['avatar_filename']) )
			{
				$poster_avatar = "themes/{THEME_NAME}/default_avatar.png";
			}
		}
		
		$Ctemplate->useTemplate("forum/posts_row", array(
			'USERNAME'	=>	$uData['username'], // Poster's name
			'IMGSRC'	=>	$poster_avatar, // Poster's avatar (or your theme's default if poster does not have one)
			'REGDATE'	=>	fDate($uData['regdate']), // Poster's registration date
			'LOG_STATUS'	=>	($uData['loggedin'] == 1 ? "online" : "offline"), // Logged in or out picture
			'LOG_ALT'	=>	"{LANG_" . ($uData['loggedin'] == 1 ? "ONLINE" : "OFFLINE"). "}", // Alternate text for log_status picture
			'TITLE'	=>	$row['title'], // Post title
			'DATE'	=>	fDate($row['createdate']), // Post date
			'TEXT'	=>	$row['content'] // The post itself
		), FALSE); // Output one row for the post
	}
	
	$Ctemplate->useStaticTemplate("forum/posts_list_foot", FALSE);
	
	/* Pager */
	if ( $post_pages > 1 )
	{
		// If we have more than one topic list page
		
		// Generate embedded pager
		$pages = ""; // Define the variable
		for ( $p = 0; $p <= ($post_pages-1); $p++ )
		{
			$pages .= $Ctemplate->useTemplate("forum/posts_page_embed", array(
				'TOPIC_ID'	=>	$id,
				'START_AT'	=>	$p,
				'PAGE_NUMBER'	=>	($p+1)
			), TRUE);
		}
		
		// Output switcher table
		$Ctemplate->useTemplate("forum/posts_pages_table", array(
			'CURRENT_PAGE'	=>	(@$_GET['start_at']+1), // Number of current page
			'PAGES_EMBED'	=>	$pages, // Embedding the generated pages box
			'PAGE_TOTAL'	=>	$post_pages
		), FALSE);
	}
}

$Ctemplate->useStaticTemplate("forum/posts_foot", FALSE); // Footer
DoFooter();
?>