<?php
 /**
 * WhispyForum script file - viewtopics.php
 * 
 * Listing topics and managing topic-specific modifying (edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("forum"); // Die if FORUM is disabled

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
$uLvl = $Cusers->getLevel();

// Query the minimal level for the forum
$fMLvl = mysql_fetch_row($Cmysql->Query("SELECT minLevel FROM forums WHERE id='" .$Cmysql->EscapeString($id). "'"));

if ( $uLvl < $fMLvl[0] )
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
} elseif ( $uLvl >= $fMLvl[0] )
{
	// The user has the rights to view the topic list
	
	/* Editing a topic */
	if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "edit" ) && ( isset($_POST['topic_id']) ) )
	{
		// Editing a topic
		if ( $uLvl < 3 )
		{
			// If the user does not have rights to add new forum
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
				'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
				'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
				'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
			), FALSE ); // We give an unavailable error
		} elseif ( $uLvl >= 3 )
		{
			// Access granted :)
			if ( !isset($_POST['edit_do']) )
			{
				// If we requested the form to edit a topic
				
				$tData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM topics WHERE id='" .$Cmysql->EscapeString($_POST['topic_id']). "'"));
				
				if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
				{
					// We output the form with data returned (user doesn't have to enter it again)
					$Ctemplate->useTemplate("forum/topics_edit_form", array(
						'FORUM_ID'	=>	$id,
						'TOPIC_ID'	=>	$_POST['topic_id'], // ID of the topic
						'OTITLE'	=>	$tData['title'], // Topic's title (original)
						'TITLE'	=>	$_POST['title'], // Topic's title (new, returned from error)
						// The topic 'Lock' checkbox will be get it's previous state
						'LOCK_CHECK'	=>	($_POST['lock'] == 1 ? " checked" : ""),
						// The topic 'Highlight' checkbox will be get it's previous state
						'HIGHLIGHT_CHECK'	=>	($_POST['highlight'] == 1 ? " checked" : ""),
						'START_AT'	=>	$_POST['start_at']
					), FALSE);
				} else {
					// We output general form
					$Ctemplate->useTemplate("forum/topics_edit_form", array(
						'FORUM_ID'	=>	$id,
						'TOPIC_ID'	=>	$_POST['topic_id'], // ID of the topic
						'OTITLE'	=>	$tData['title'], // Topic's title (original)
						'TITLE'	=>	$tData['title'], // Topic's title (same as original)
						// The topic 'Lock' checkbox will be get it's previous state
						'LOCK_CHECK'	=>	($tData['locked'] == 1 ? " checked" : ""),
						// The topic 'Highlight' checkbox will be get it's previous state
						'HIGHLIGHT_CHECK'	=>	($tData['highlighted'] == 1 ? " checked" : ""),
						'START_AT'	=>	$_POST['start_at']
					), FALSE);
				}
			} elseif ( ( isset($_POST['edit_do']) ) && ( $_POST['edit_do'] == "yes") )
			{
				// If we added the data and requested SQL query
				
				// First, we check whether every required variables were entered
				if ( $_POST['title'] == NULL ) // Topic's title
				{
					$Ctemplate->useTemplate("forum/topics_edit_variable_error", array(
						'VARIABLE'	=>	"{LANG_FORUMS_TITLE}", // Errornous variable name
						'FORUM_ID'	=>	$id,
						'TOPIC_ID'	=>	$_POST['topic_id'], // ID of the topic
						'TITLE'	=>	$_POST['title'], // Topic's title (should be empty)
						'LOCK'	=>	(!isset($_POST['lock']) ? "0" : "1"),
						'HIGHLIGHT'	=>	(!isset($_POST['highlight']) ? "0" : "1"),
						'START_AT'	=>	$_POST['start_at']
					), FALSE);
					
					// We terminate the script
					$Ctemplate->useStaticTemplate("forum/topics_foot", FALSE); // Footer
					DoFooter();
					exit;
				}
				
				// Every variable has value, do the SQL query.
				$tEdit = $Cmysql->Query("UPDATE topics SET ".
					"title='" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['title'])). "',
					locked='" .$Cmysql->EscapeString( (!isset($_POST['lock']) ? "0" : "1") ). "',
					highlighted='" .$Cmysql->EscapeString( (!isset($_POST['highlight']) ? "0" : "1") ). "' WHERE " .
					"id='" .$Cmysql->EscapeString($_POST['topic_id']). "'");
				
				// $tEdit is TRUE if we succeeded
				// $tEdit is FALSE if we failed
				
				if ( $tEdit == FALSE )
				{
					// Failed to edit the topic
					$Ctemplate->useTemplate("forum/topics_edit_error", array(
						'FORUM_ID'	=>	$id,
						'TOPIC_ID'	=>	$_POST['topic_id'], // ID of the topic
						'TITLE'	=>	$_POST['title'], // Topic's title
						'LOCK'	=>	@$_POST['lock'], // Description
						'HIGHLIGHT'	=>	@$_POST['highlight'], // Minimal user level
						'START_AT'	=>	$_POST['start_at']
					), FALSE); // Output a retry form
				} elseif ( $tEdit == TRUE )
				{
					// Edited the topic
					$Ctemplate->useTemplate("forum/topics_edit_success", array(
						'FORUM_ID'	=>	$id,
						'TITLE'	=>	$_POST['title'], // Topic's title
						'START_AT'	=>	$_POST['start_at']
					), FALSE); // Output a success form
				}
			}
		}
	}
	/* Editing a topic */
	
	/* Deleting a topic */
	if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "delete" ) && ( isset($_POST['topic_id']) ) )
	{
		// Deleting a topic
		if ( $uLvl < 3 )
		{
			// If the user does not have rights to delete the topic
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
				'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
				'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
				'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
			), FALSE ); // We give an unavailable error
		} elseif ( $uLvl >= 3 )
		{
			// Access granted
			
			// Delete the topic
			$tDel = $Cmysql->Query("DELETE FROM topics WHERE id='" .$Cmysql->EscapeString($_POST['topic_id']). "'");
			
			// $tDel is TRUE if we succeeded
			// $tDel is FALSE if we failed
			
			if ( $tDel == FALSE )
			{
				// Failed to delete the topic
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
					'BODY'	=>	"{LANG_TOPICS_DELETE_SQL_ERROR}", // Error text
					'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
				
				$Ctemplate->useTemplate("forum/topics_backtolist", array(
					'FORUM_ID'	=>	$id,
					'START_AT'	=>	$_POST['start_at']
				), FALSE); // Return button
			} elseif ( $tDel == TRUE )
			{
				// Deleted the topic
				$Ctemplate->useTemplate("successbox", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
					'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
					'BODY'	=>	"{LANG_TOPICS_DELETE_SUCCESS_HEAD}", // Success text
					'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give success
				
				// Delete the posts
				
				// Remove one post from the posters' post count
				$posts_in_topic = $Cmysql->Query("SELECT createuser FROM posts WHERE topicid='" .$Cmysql->EscapeString($_POST['topic_id']). "'"); // Query all posts in the recently deleted topic
				
				while ( $prow = mysql_fetch_row($posts_in_topic) )
				{
					// Going through every post in the topic
					$pCount = mysql_fetch_row($Cmysql->Query("SELECT post_count FROM users WHERE id='" .$Cmysql->EscapeString($prow[0]). "'")); // Query the poster's post count
					
					$Cmysql->Query("UPDATE users SET post_count=" .($pCount[0] - 1). " WHERE id='" .$Cmysql->EscapeString($prow[0]). "'"); // Remove one post from count
				}
				
				$pDel = $Cmysql->Query("DELETE FROM posts WHERE topicid='" .$Cmysql->EscapeString($_POST['topic_id']). "'");
				
				// $pDel is TRUE if we succeeded
				// $pDel is FALSE if we failed
				
				if ( $pDel == FALSE )
				{
					// Failed to delete the posts
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
						'BODY'	=>	"{LANG_TOPICS_DELETE_POSTS_SQL_ERROR}", // Error text
						'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
					), FALSE ); // We give an error
				} elseif ( $pDel == TRUE )
				{
					// Deleted the posts
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
						'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
						'BODY'	=>	"{LANG_TOPICS_DELETE_POSTS_SUCCESS_HEAD}", // Success text
						'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
					), FALSE ); // We give an error
				}
				
				$Ctemplate->useTemplate("forum/topics_backtolist", array(
					'FORUM_ID'	=>	$id,
					'START_AT'	=>	$_POST['start_at']
				), FALSE); // Return button
			}
		}
	}
	/* Deleting a topic */
	
	/* Listing topics */
	if ( !isset($_POST['action']) )
	{
		$fName = mysql_fetch_row($Cmysql->Query("SELECT title FROM forums WHERE id='" .$Cmysql->EscapeString($id). "'")); // Title of the forum the topics are in
		
		if ( $fName == FALSE )
		{
			// If the selected forum does not exist, give error
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_apps_error.png", // Error X icon
				'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
				'BODY'	=>	"{LANG_TOPICS_FORUM_DOES_NOT_EXIST}", // Error text
				'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
			), FALSE ); // We give an error
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("forum/topics_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		$Ctemplate->useTemplate("forum/topics_table_open", array(
			'CREATE_NEW_TOPIC'	=>	( ($uLvl >= $fMLvl[0]) && ($uLvl != "0") ? $Ctemplate->useTemplate("forum/topics_create_new", array(
					'FORUM_ID'	=>	$id // ID of the forum we're creating the theme into
				), TRUE) : "<br>"), // Output button of new topic creation (only if the user is logged in and has rights to view the forum)
			'ADMIN_ACTIONS'	=>	($uLvl >= 2 ? 
				$Ctemplate->useStaticTemplate("forum/forums_admin_actions", TRUE) // Return the header
			: NULL ), // Output header for admin actions only if the user is moderator or higher
			'FORUM_NAME'	=>	$fName[0] // Title of the forum
		), FALSE); // Open the table and output header
		
		/* Highlighted tables */
		// Highlighted tables appear on each page
		$topic_Hresult = $Cmysql->Query("SELECT * FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "' AND highlighted='1'"); // Query highlighted tables in the set forum
		
		while ( $Hrow = mysql_fetch_assoc($topic_Hresult) )
		{
			// Get the username of who created the topic
			$Hcreator_uName = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$Cmysql->EscapeString($Hrow['createuser']). "'"));
			
			// Get post count
			$Hpost_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM posts WHERE topicid='" .$Hrow['id']. "'"));
			
			// Get last post
			$Hlast_post = mysql_fetch_assoc($Cmysql->Query("SELECT id, topicid, createuser, createdate FROM posts WHERE topicid='" .$Hrow['id']. "' ORDER BY createdate DESC LIMIT 1"));
			// and get last poster's name
			$Hlast_post_user = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$Hlast_post['createuser']. "'"));
			
			// Output rows for every table
			$Ctemplate->useTemplate("forum/topics_table_row", array(
				'TYPE'	=>	"highlight", // Different theme for highlighted topics
				'LOCKED'	=>	($Hrow['locked'] == 1 ? "_locked" : ""), // The icon will be a locked icon if the thread is locked
				'ALT'	=>	($Hrow['locked'] == 1 ? "{LANG_TOPICS_HIGHLIGHTED_LOCKED}" : "{LANG_TOPICS_HIGHLIGHTED}"), // Alternate picture text
				'ID'	=>	$Hrow['id'],
				'TITLE'	=>	$Hrow['title'], // Title of the topic
				'CREATOR'	=>	$Hcreator_uName[0], // Username of creator
				'CREATION_DATE'	=>	fDate($Hrow['createdate']), // Creation timestamp
				'LAST_POST'	=>	($Hlast_post['createdate'] != NULL ? $Ctemplate->useTemplate("forum/topics_table_row_last_post", array(
					'DATESTAMP'	=>	fDate($Hlast_post['createdate']),
					'NAME'	=>	$Hlast_post_user[0],
					'TOPICID'	=>	$Hlast_post['topicid'],
					'POSTID'	=>	$Hlast_post['id'],
					'USERID'	=>	$Hlast_post['createuser']
				), TRUE) : $wf_lang['{LANG_POSTS_NO}']),
				'POSTS'	=>	$Hpost_count[0],
				'EDIT'	=>	($uLvl >= 3 ?
					$Ctemplate->useTemplate("forum/topics_admin_edit", array(
						'TOPIC_ID'	=>	$Hrow['id'],
						'FORUM_ID'	=>	$id,
						'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
					), TRUE)
				: NULL ), // Edit button for admins and mods
				'DELETE'	=>	($uLvl >= 3 ?
					$Ctemplate->useTemplate("forum/topics_admin_delete", array(
						'TOPIC_ID'	=>	$Hrow['id'],
						'FORUM_ID'	=>	$id,
						'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
					), TRUE)
				: NULL ), // Delete button for admins and mods
			), FALSE); // Output row
		}
		
		/**
		 * The topic list is split, based on user setting.
		 * Because of it, we need to generate a page switcher by using the 'LIMIT start, count'
		 * syntax.
		 */
		
		$usr_topic_split_value = $_SESSION['forum_topic_count_per_page']; // Use the user's preference (queried from session)
		
		// Query the total number of NORMAL (not highlighted) topics in the current forum
		$topic_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "' AND highlighted='0'"));
		
		// Generate the number of pages (we need to ceil it up because we need to have integer pages)
		$topic_pages = ceil($topic_count[0] / $usr_topic_split_value);
		
		// Generate the start_at value
		if ( @$_GET['start_at'] == NULL )
		{
			// If the value is missing, we will assume 0 as the start
			$topic_start = 0;
		} elseif ( @$_GET['start_at'] != NULL )
		{
			// If we have start value, multiply it with the split value so it'll show the correct page
			$topic_start = $_GET['start_at'] * $usr_topic_split_value;
		}
		
		/* Normal tables */
		$topic_result = $Cmysql->Query("SELECT * FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "' AND highlighted='0' LIMIT " .$topic_start.", " .$Cmysql->EscapeString($usr_topic_split_value)); // Query "normal" tables in the set forum (splitted)
		
		while ( $row = mysql_fetch_assoc($topic_result) )
		{
			// Get the username of who created the topic
			$creator_uName = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$Cmysql->EscapeString($row['createuser']). "'"));
			
			// Get post count
			$post_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM posts WHERE topicid='" .$row['id']. "'"));
			
			// Get last post
			$last_post = mysql_fetch_assoc($Cmysql->Query("SELECT id, topicid, createuser, createdate FROM posts WHERE topicid='" .$row['id']. "' ORDER BY createdate DESC LIMIT 1"));
			// and get last poster's name
			$last_post_user = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$last_post['createuser']. "'"));
			
			// Output rows for every table
			$Ctemplate->useTemplate("forum/topics_table_row", array(
				'TYPE'	=>	"normal", // Different theme for normal topics
				'LOCKED'	=>	($row['locked'] == 1 ? "_locked" : ""), // The icon will be a locked icon if the thread is locked
				'ALT'	=>	($row['locked'] == 1 ? "{LANG_TOPICS_NORMAL_LOCKED}" : "{LANG_TOPICS_NORMAL}"), // Alternate picture text
				'ID'	=>	$row['id'],
				'TITLE'	=>	$row['title'], // Title of the topic
				'CREATOR'	=>	$creator_uName[0], // Username of creator
				'CREATION_DATE'	=>	fDate($row['createdate']), // Creation timestamp
				'LAST_POST'	=> ($last_post['createdate'] != NULL ? $Ctemplate->useTemplate("forum/topics_table_row_last_post", array(
					'DATESTAMP'	=>	fDate($last_post['createdate']),
					'NAME'	=>	$last_post_user[0],
					'TOPICID'	=>	$last_post['topicid'],
					'POSTID'	=>	$last_post['id'],
					'USERID'	=>	$last_post['createuser']
				), TRUE) : $wf_lang['{LANG_POSTS_NO}']),
				'POSTS'	=>	$post_count[0],
				'EDIT'	=>	($uLvl >= 3 ?
					$Ctemplate->useTemplate("forum/topics_admin_edit", array(
						'TOPIC_ID'	=>	$row['id'],
						'FORUM_ID'	=>	$id,
						'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
					), TRUE)
				: NULL ), // Edit button for admins and mods
				'DELETE'	=>	($uLvl >= 3 ?
					$Ctemplate->useTemplate("forum/topics_admin_delete", array(
						'TOPIC_ID'	=>	$row['id'],
						'FORUM_ID'	=>	$id,
						'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
					), TRUE)
				: NULL ) // Delete button for admins and mods
			), FALSE); // Output row
		}
		
		// Get the overall count of topics in the forum
		$topic_all_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM topics WHERE forumid='" .$Cmysql->EscapeString($id). "'"));
		
		if ( $topic_all_count[0] == 0 )
		{
			// If the forum does not have tables in it
			
			$Ctemplate->useStaticTemplate("forum/topics_table_row_noentry", FALSE); // Output message about no tables in the forum
		}
		
		$Ctemplate->useStaticTemplate("forum/topics_table_close", FALSE); // Close the table
		
		if ( $topic_pages > 0 )
		{
			// If we have more than one topic list page
			
			// Generate embedded pager
			$pages = ""; // Define the variable
			for ( $p = 0; $p <= ($topic_pages-1); $p++ )
			{
				$pages .= $Ctemplate->useTemplate("forum/topics_page_embed", array(
					'FORUM_ID'	=>	$id,
					'START_AT'	=>	$p,
					'PAGE_NUMBER'	=>	($p+1)
				), TRUE);
			}
			
			// Output switcher table
			$Ctemplate->useTemplate("forum/topics_pages_table", array(
				'CURRENT_PAGE'	=>	(@$_GET['start_at']+1), // Number of current page
				'PAGES_EMBED'	=>	$pages, // Embedding the generated pages box
				'PAGE_TOTAL'	=>	$topic_pages
			), FALSE);
		}
	}
	/* Listing topics */
}

$Ctemplate->useStaticTemplate("forum/topics_foot", FALSE); // Footer
DoFooter();
?>
