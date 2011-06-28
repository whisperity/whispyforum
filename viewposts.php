<?php
 /**
 * WhispyForum script file - viewposts.php
 * 
 * Listing posts and managing post-specific modifying (edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("forum"); // Die if FORUM is disabled

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

if ( $uLvl == FALSE )
{
	// If the user does not have a return value (meaning the user is a guest)
	// Set the level to 0
	$uLvl = array(0	=> '0');
}

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
	
	// Select the newest post's ID in the topic
	$lastPost_inTopic = mysql_fetch_row($Cmysql->Query("SELECT id FROM posts WHERE topicid='" .$Cmysql->EscapeString($id). "' ORDER BY id DESC LIMIT 1"));
	
	/* Editing a post */
	if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "edit" ) && ( isset($_POST['post_id']) ) )
	{
		// Editing a post
		
		$pData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM posts WHERE id='" .$Cmysql->EscapeString($_POST['post_id']). "'")); // Query post data
		$topic_array = mysql_fetch_assoc($Cmysql->Query("SELECT title, forumid, locked FROM topics WHERE id='" .$pData['topicid']. "'")); // Data of the topic
		
		// Check whether the user has the rights to edit a post
		// The user has rights to edit a post if:
		// * the topic is open
		// * AND the post is his/her post
		// * OR he/she is a moderator+ user
		// * AND the post is the last post of the topic (if the user is not mod+)
		$editRight = FALSE; // By default, the user does not have the right to edit a post
		if ( ( $pData['createuser'] == $_SESSION['uid'] ) && ( $pData['id'] == $lastPost_inTopic[0] ) && ( $topic_array['locked'] == 0 ) )
		{
			$editRight = TRUE;
		}
		
		if ( ( $uLvl[0] >= 2 ) && ( $topic_array['locked'] == 0 ) )
		{
			$editRight = TRUE;
		}
		
		if ( $editRight === FALSE )
		{
			// If the user does not have rights to add new forum
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
				'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
				'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
				'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
			), FALSE ); // We give an unavailable error
		} elseif ( $editRight === TRUE )
		{
			// Access granted
				
			if ( $topic_array['locked'] == "1" )
			{
				// If the topic is locked, output an error message and prevent execution
				$Ctemplate->useTemplate("forum/topic_locked_error", array(
					'TOPIC_ID'	=>	$id
				), FALSE);
			} elseif ( $topic_array['locked'] == "0" )
			{
				// If the topic is open
				if ( !isset($_POST['edit_do']) )
				{
					// If we requested the form to edit the post
					
					if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
					{
						// We output the form with data returned (user doesn't have to enter it again)
						$Ctemplate->useTemplate("forum/posts_edit_form", array(
							'TOPIC_ID'	=>	$id,
							'POST_ID'	=>	$_POST['post_id'], // ID of the topic
							'OTITLE'	=>	$pData['title'], // Post's title (original)
							'POST_TITLE'	=>	$_POST['post_title'], // Post's title (new, returned from error)
							'POST_CONTENT'	=>	$_POST['post_content'], // Post's content
							'START_AT'	=>	$_POST['start_at']
						), FALSE);
					} else {
						// We output general form
						$Ctemplate->useTemplate("forum/posts_edit_form", array(
							'TOPIC_ID'	=>	$id,
							'POST_ID'	=>	$_POST['post_id'], // ID of the topic
							'OTITLE'	=>	$pData['title'], // Post's title (original)
							'POST_TITLE'	=>	$pData['title'], // Post's title (same as original)
							'POST_CONTENT'	=>	$pData['content'],
							'START_AT'	=>	$_POST['start_at']
						), FALSE);
					}
				} elseif ( ( isset($_POST['edit_do']) ) && ( $_POST['edit_do'] == "yes") )
				{
					// If we added the data and requested SQL query
					
					// First, we check whether every required variables were entered
					if ( $_POST['post_content'] == NULL ) // Post's contet
					{
						$Ctemplate->useTemplate("forum/posts_edit_variable_error", array(
							'VARIABLE'	=>	"{LANG_POSTS_POST}", // Errornous variable name
							'TOPIC_ID'	=>	$id,
							'POST_ID'	=>	$_POST['post_id'], // ID of the post
							'POST_TITLE'	=>	$_POST['post_title'], // Post's title
							'POST_CONTENT'	=>	$_POST['post_content'], // Post content (should be empty)
							'START_AT'	=>	$_POST['start_at']
						), FALSE);
						
						// We terminate the script
						$Ctemplate->useStaticTemplate("forum/posts_foot", FALSE); // Footer
						DoFooter();
						exit;
					}
					
					// Every variable has value, do the SQL query.
					$pEdit = $Cmysql->Query("UPDATE posts SET ".
						"title='" .$Cmysql->EscapeString($_POST['post_title']). "',
						content='" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['post_content'])). "' WHERE " .
						"id='" .$Cmysql->EscapeString($_POST['post_id']). "'");
					
					// $pEdit is TRUE if we succeeded
					// $pEdit is FALSE if we failed
					
					if ( $pEdit == FALSE )
					{
						// Failed to edit the post
						$Ctemplate->useTemplate("forum/posts_edit_error", array(
							'TOPIC_ID'	=>	$id,
							'POST_ID'	=>	$_POST['post_id'], // ID of the post
							'POST_TITLE'	=>	$_POST['post_title'], // Post's title
							'POST_CONTENT'	=>	$_POST['post_content'], // Post content
							'START_AT'	=>	$_POST['start_at']
						), FALSE); // Output a retry form
					} elseif ( $pEdit == TRUE )
					{
						// Edited the post
						$Ctemplate->useTemplate("forum/posts_edit_success", array(
							'TOPIC_ID'	=>	$id,
							'TITLE'	=>	(@$_POST['post_title'] == NULL ? "No title" : @$_POST['post_title']), // Post's title
							'START_AT'	=>	$_POST['start_at']
						), FALSE); // Output a success form
					}
				}
			}
		}
	}
	/* Editing a post */
	
	/* Deleting a post */
	if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "delete" ) && ( isset($_POST['post_id']) ) )
	{
		// Get poster's userID
		$pUID = mysql_fetch_row($Cmysql->Query("SELECT createuser, topicid, id FROM posts WHERE id='" .$Cmysql->EscapeString($_POST['post_id']). "'"));
		$topic_array = mysql_fetch_assoc($Cmysql->Query("SELECT locked FROM topics WHERE id='" .$pUID[1]. "'")); // Data of the topic
			
		// Check whether the user has rights to delete a post
		// The user has rights to delete a post if:
		// * the topic is open
		// * AND the post is his/her post
		// * OR he/she is a moderator+ user
		// * AND the post is the last post of the topic (if the user is not mod+)
		$deleteRight = FALSE; // By default, the user does not have the right to delete a post
		if ( ( $pUID[0] == $_SESSION['uid'] ) && ( $pUID[2] == $lastPost_inTopic[0] ) && ( $topic_array['locked'] == 0 ) )
		{
			$deleteRight = TRUE;
		}
		
		if ( ( $uLvl[0] >= 2 ) && ( $topic_array['locked'] == 0 ) )
		{
			$deleteRight = TRUE;
		}
		
		if ( $deleteRight === FALSE )
		{
			// If the user does not have rights to delete the post
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
				'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
				'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
				'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
			), FALSE ); // We give an unavailable error
		} elseif ( $deleteRight === TRUE )
		{
			// Access granted
				
			if ( $topic_array['locked'] == "1" )
			{
				// If the topic is locked, output an error message and prevent execution
				$Ctemplate->useTemplate("forum/topic_locked_error", array(
					'TOPIC_ID'	=>	$id
				), FALSE);
			} elseif ( $topic_array['locked'] == "0" )
			{
				// If the topic is open
				
				// Delete the post
				$pDel = $Cmysql->Query("DELETE FROM posts WHERE id='" .$Cmysql->EscapeString($_POST['post_id']). "'");
				
				// $pDel is TRUE if we succeeded
				// $pDel is FALSE if we failed
				
				if ( $pDel == FALSE )
				{
					// Failed to delete the post
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
						'BODY'	=>	"{LANG_POSTS_DELETE_SQL_ERROR}", // Error text
						'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
					), FALSE ); // We give an error
					
					$Ctemplate->useTemplate("forum/posts_backtolist", array(
						'TOPIC_ID'	=>	$id,
						'START_AT'	=>	$_POST['start_at']
					), FALSE); // Return button
				} elseif ( $pDel == TRUE )
				{
					// Deleted the post
					
					// Remove one from the user's post count
					$uPostCount = mysql_fetch_row($Cmysql->Query("SELECT post_count FROM users WHERE id='" .$pUID[0]. "'"));
					$Cmysql->Query("UPDATE users SET post_count=" .($uPostCount[0] - 1). " WHERE id='" .$pUID[0]. "'");
					
					$Ctemplate->useTemplate("successbox", array(
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
						'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
						'BODY'	=>	"{LANG_POSTS_DELETE_SUCCESS_HEAD}", // Success text
						'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
					), FALSE ); // We give success
					
					$Ctemplate->useTemplate("forum/posts_backtolist", array(
						'TOPIC_ID'	=>	$id,
						'START_AT'	=>	$_POST['start_at']
					), FALSE); // Return button
				}
			}
		}
	}
	/* Deleting a post */
	
	/* Listing posts */
	if ( !isset($_POST['action']) )
	{
		$topic_array = mysql_fetch_assoc($Cmysql->Query("SELECT title, forumid, locked FROM topics WHERE id='" .$Cmysql->EscapeString($id). "'")); // Data of the topic
		
		$fName = mysql_fetch_row($Cmysql->Query("SELECT title FROM forums WHERE id='" .$topic_array['forumid']. "'")); // $fName[0] is the name of the forum the topic (containing the posts) is in
		
		$tName = $topic_array['title']; // Name of the topic
		
		if ( $uLvl[0] == "0" )
		{
			// If the user is a guest, give no option to post
			$new_post_button = "<br>"; // We need to set the variable to avoid errors
		} elseif ( ( $uLvl[0] >= $fMLvl[0] ) && ( $uLvl[0] != "0" ) )
		{
			// If the user has the rights to view the forum and is logged in
			// set different new post buttons for later use
			
			if ( $topic_array['locked'] == 0 )
			{
				// If the topic isn't locked
				$new_post_button = $Ctemplate->useTemplate("forum/posts_new", array(
					'TOPIC_ID'	=>	$id
				), TRUE); // Set the new post button to a variable for later use
			} elseif ( $topic_array['locked'] == 1 )
			{
				// If the topic is locked
				$new_post_button = NULL; // Set the variable for later use
			}
		}
		
		$Ctemplate->useTemplate("forum/posts_list_head", array(
			'FORUMID'	=>	$topic_array['forumid'], // ID of the forum the topic is in
			'FORUM_NAME'	=>	$fName[0],
			'TOPIC_NAME'	=>	$tName,
			'NEW_POST'	=>	$new_post_button
		), FALSE); // Output opening of posts table
		
		/**
		 * The post list is split, based on user setting.
		 * Because of it, we need to generate a page switcher by using the 'LIMIT start, count'
		 * syntax.
		 */
		
		$usr_post_split_value = $_SESSION['forum_post_count_per_page']; // Use the user's preference (queried from session)
		
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
			$uData = mysql_fetch_assoc($Cmysql->Query("SELECT username, regdate, loggedin, avatar_filename, post_count FROM users WHERE id='" .$Cmysql->EscapeString($row['createuser']). "'"));
			
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
			
			// Use seperate post template for posts having and not having title
			if ( $row['title'] == NULL )
			{
				$WithOrWithout = "without"; // Use template without title
			} else {
				$WithOrWithout = "with"; // Use template with title
			}
			
			// Check whether the user has the rights to edit a post
			// The user has rights to edit a post if:
			// * the topic is open
			// * AND the post is his/her post
			// * OR he/she is a moderator+ user
			// * AND the post is the last post of the topic (if the user is not mod+)
			$editRight = FALSE; // By default, the user does not have the right to edit a post
			if ( ( $row['createuser'] == $_SESSION['uid'] ) && ( $row['id'] == $lastPost_inTopic[0] ) && ( $topic_array['locked'] == 0 ) )
			{
				$editRight = TRUE;
			}
			
			if ( ( $uLvl[0] >= 2 ) && ( $topic_array['locked'] == 0 ) )
			{
				$editRight = TRUE;
			}
			
			// Check whether the user has rights to delete a post
			// The user has rights to delete a post if:
			// * the topic is open
			// * AND the post is his/her post
			// * OR he/she is a moderator+ user
			// * AND the post is the last post of the topic (if the user is not mod+)
			$deleteRight = FALSE; // By default, the user does not have the right to delete a post
			if ( ( $row['createuser'] == $_SESSION['uid'] ) && ( $row['id'] == $lastPost_inTopic[0] ) && ( $topic_array['locked'] == 0 ) )
			{
				$deleteRight = TRUE;
			}
			
			if ( ( $uLvl[0] >= 2 ) && ( $topic_array['locked'] == 0 ) )
			{
				$deleteRight = TRUE;
			}
			
			$Ctemplate->useTemplate("forum/posts_row_" .$WithOrWithout. "_title", array(
				'ID'	=>	$row['id'], // Post ID
				'USERID'	=>	$row['createuser'], // Poster's ID
				'USERNAME'	=>	$uData['username'], // Poster's name
				'IMGSRC'	=>	$poster_avatar, // Poster's avatar (or your theme's default if poster does not have one)
				'REGDATE'	=>	fDate($uData['regdate']), // Poster's registration date
				'POST_COUNT'	=>	$uData['post_count'], // Post count of the user
				'LOG_STATUS'	=>	($uData['loggedin'] == 1 ? "online" : "offline"), // Logged in or out picture
				'LOG_ALT'	=>	"{LANG_" . ($uData['loggedin'] == 1 ? "ONLINE" : "OFFLINE"). "}", // Alternate text for log_status picture
				'TITLE'	=>	$row['title'], // Post title
				'DATE'	=>	fDate($row['createdate']), // Post date
				'TEXT'	=>	bbDecode($row['content']), // The post itself
				'EDIT'	=>	( $editRight === TRUE ? $Ctemplate->useTemplate("forum/posts_edit", array(
					'POST_ID'	=>	$row['id'],
					'TOPIC_ID'	=>	$id,
					'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
				), TRUE) : NULL ),
				'DELETE'	=>	( $deleteRight === TRUE ? $Ctemplate->useTemplate("forum/posts_delete", array(
					'POST_ID'	=>	$row['id'],
					'TOPIC_ID'	=>	$id,
					'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
				), TRUE) : NULL ),
			), FALSE); // Output one row for the post
			
			$WithOrWithout = ""; // Clear
		}
		
		$Ctemplate->useStaticTemplate("forum/posts_list_foot", FALSE);
		
		/* Pager */
		if ( $post_pages > 0 )
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
		/* Pager */
	}
	/* Listing posts */
}

$Ctemplate->useStaticTemplate("forum/posts_foot", FALSE); // Footer
DoFooter();
?>
