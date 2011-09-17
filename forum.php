<?php
 /**
 * WhispyForum script file - forum.php
 * 
 * Listing forums and managing forum-specific modifying (add, edit, delete) actions
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("forum"); // Die if FORUM is disabled

$Ctemplate->useStaticTemplate("forum/forums_head", FALSE); // Header

// Get the current user's level
$uLvl = $Cusers->getLevel();

/* Creating new forum */
if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "newforum" ) )
{
	// Adding new forum
	if ( $uLvl < 3 )
	{
		// If the user does not have rights to add new forum
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
		), FALSE ); // We give an unavailable error
	} elseif ( $uLvl >= 3 )
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
				// Failed to create the forum
				$Ctemplate->useTemplate("forum/forums_create_error", array(
					'TITLE'	=>	$_POST['title'], // Forum's title
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE); // Output a retry form
			} elseif ( $fCreate == TRUE )
			{
				// Created the forum
				$Ctemplate->useTemplate("forum/forums_create_success", array(
					'TITLE'	=>	$_POST['title'] // Forum's title
				), FALSE); // Output a success form
			}
		}
	}
}
/* Creating new forum */

/* Editing a forum */
if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "edit" ) && ( isset($_POST['forum_id']) ) )
{
	// Editing a forum
	if ( $uLvl < 2 )
	{
		// If the user does not have rights to add new forum
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
		), FALSE ); // We give an unavailable error
	} elseif ( $uLvl >= 2 )
	{
		// Access granted :)
		if ( !isset($_POST['edit_do']) )
		{
			// If we requested the form to add new forum
			
			$fData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM forums WHERE id='" .$Cmysql->EscapeString($_POST['forum_id']). "'"));
			
			if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
			{
				// We output the form with data returned (user doesn't have to enter it again)
				$Ctemplate->useTemplate("forum/forums_edit_form", array(
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'OTITLE'	=>	$fData['title'], // Forum's title (original)
					'TITLE'	=>	$_POST['title'], // Forum's title (new, returned from error)
					'DESC'	=>	$_POST['desc'], // Description
					'0_CHECKED'	=>	($_POST['minlevel'] == 0 ? " checked" : ""), // User min. level 0 (guest)
					'1_CHECKED'	=>	($_POST['minlevel'] == 1 ? " checked" : ""), // User min. level 1 (user)
					'2_CHECKED'	=>	($_POST['minlevel'] == 2 ? " checked" : ""), // User min. level 2 (moderator)
					'3_CHECKED'	=>	($_POST['minlevel'] == 3 ? " checked" : ""), // User min. level 3 (administrator)
					'ADMIN_DISABLED'	=>	($uLvl < 3 ? " disabled" : "") // Disable the User min. level 3 (admin) box if the user is only a moderator
				), FALSE);
			} else {
				// We output general form
				$Ctemplate->useTemplate("forum/forums_edit_form", array(
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'OTITLE'	=>	$fData['title'], // Forum's title (original)
					'TITLE'	=>	$fData['title'], // Forum's title (new, now the same)
					'DESC'	=>	$fData['info'], // Description
					'0_CHECKED'	=>	($fData['minLevel'] == 0 ? " checked" : ""), // User min. level 0 (guest)
					'1_CHECKED'	=>	($fData['minLevel'] == 1 ? " checked" : ""), // User min. level 1 (user)
					'2_CHECKED'	=>	($fData['minLevel'] == 2 ? " checked" : ""), // User min. level 2 (moderator)
					'3_CHECKED'	=>	($fData['minLevel'] == 3 ? " checked" : ""), // User min. level 3 (administrator)
					'ADMIN_DISABLED'	=>	($uLvl < 3 ? " disabled" : "") // Disable the User min. level 3 (admin) box if the user is only a moderator
				), FALSE);
			}
		} elseif ( ( isset($_POST['edit_do']) ) && ( $_POST['edit_do'] == "yes") )
		{
			// If we added the data and requested SQL query
			
			// First, we check whether every required variables were entered
			if ( $_POST['title'] == NULL ) // Forum's title
			{
				$Ctemplate->useTemplate("forum/forums_edit_variable_error", array(
					'VARIABLE'	=>	"{LANG_FORUMS_TITLE}", // Errornous variable name
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'TITLE'	=>	$_POST['title'], // Forum's title (should be empty)
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE);
				
				// We terminate the script
				$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
				DoFooter();
				exit;
			}
			
			if ( @$_POST['minlevel'] == NULL ) // Minimal user level
			{
				$Ctemplate->useTemplate("forum/forums_edit_variable_error", array(
					'VARIABLE'	=>	"{LANG_FORUMS_MINIMAL_LEVEL}", // Errornous variable name
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'TITLE'	=>	$_POST['title'], // Forum's title
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	@$_POST['minlevel'] // Minimal user level (should be empty)
				), FALSE);
				
				// We terminate the script
				$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
				DoFooter();
				exit;
			}
			
			// We reject the user if he or she wants to make a forum
			// Administrator only, but he/she is only a moderator
			if ( ( $uLvl < 3 ) && ( $_POST['minlevel'] == 3 ) )
			{
				$Ctemplate->useTemplate("forum/forums_edit_moderator_error", array(
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'TITLE'	=>	$_POST['title'], // Forum's title (should be empty)
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE); // Error message
				
				// We terminate the script
				$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
				DoFooter();
				exit;
			}
			
			// Every variable has value, do the SQL query.
			$fEdit = $Cmysql->Query("UPDATE forums SET ".
				"title='" .$Cmysql->EscapeString($_POST['title']). "',
				info='" .$Cmysql->EscapeString($_POST['desc']). "',
				minLevel='" .$Cmysql->EscapeString($_POST['minlevel']). "' WHERE " .
				"id='" .$Cmysql->EscapeString($_POST['forum_id']). "'");
			
			// $fEdit is TRUE if we succeeded
			// $fEdit is FALSE if we failed
			
			if ( $fEdit == FALSE )
			{
				// Failed to edit the forum
				$Ctemplate->useTemplate("forum/forums_edit_error", array(
					'FORUM_ID'	=>	$_POST['forum_id'], // ID of the forum
					'TITLE'	=>	$_POST['title'], // Forum's title
					'DESC'	=>	$_POST['desc'], // Description
					'MINLEVEL'	=>	$_POST['minlevel'] // Minimal user level
				), FALSE); // Output a retry form
			} elseif ( $fEdit == TRUE )
			{
				// Edited the forum
				$Ctemplate->useTemplate("forum/forums_edit_success", array(
					'TITLE'	=>	$_POST['title'] // Forum's title
				), FALSE); // Output a success form
			}
		}
	}
}
/* Editing a forum */

/* Deleting a forum */
if ( ( isset($_POST['action']) ) && ( $_POST['action'] == "delete" ) && ( isset($_POST['forum_id']) ) )
{
	// Deleting a forum
	if ( $uLvl < 3 )
	{
		// If the user does not have rights to add new forum
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
		), FALSE ); // We give an unavailable error
	} elseif ( $uLvl >= 3 )
	{
		// Access granted
		
		// Delete the forum
		$fDel = $Cmysql->Query("DELETE FROM forums WHERE id='" .$Cmysql->EscapeString($_POST['forum_id']). "'");
		
		// $fDel is TRUE if we succeeded
		// $fDel is FALSE if we failed
		
		if ( $fDel == FALSE )
		{
			// Failed to delete the forum
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
				'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
				'BODY'	=>	"{LANG_FORUMS_DELETE_SQL_ERROR}", // Error text
				'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
			), FALSE ); // We give an error
			
			$Ctemplate->useStaticTemplate("forum/forums_backtolist", FALSE); // Return button
		} elseif ( $fDel == TRUE )
		{
			// Deleted the forum
			$Ctemplate->useTemplate("successbox", array(
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
				'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
				'BODY'	=>	"{LANG_FORUMS_DELETE_SUCCESS_HEAD}", // Success text
				'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
			), FALSE ); // We give success
			
			// Delete the topics
			$tDel = $Cmysql->Query("DELETE FROM topics WHERE forumid='" .$Cmysql->EscapeString($_POST['forum_id']). "'");
			
			// $tDel is TRUE if we succeeded
			// $tDel is FALSE if we failed
			
			if ( $tDel == FALSE )
			{
				// Failed to delete the topics
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
					'BODY'	=>	"{LANG_FORUMS_DELETE_TOPICS_SQL_ERROR}", // Error text
					'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
			} elseif ( $tDel == TRUE )
			{
				// Deleted the topics
				$Ctemplate->useTemplate("successbox", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
					'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
					'BODY'	=>	"{LANG_FORUMS_DELETE_TOPICS_SUCCESS_HEAD}", // Success text
					'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
			}
			
			// Delete the posts
			
			// Remove one post from the posters' post count
			$posts_in_forum = $Cmysql->Query("SELECT createuser FROM posts WHERE forumid='" .$Cmysql->EscapeString($_POST['forum_id']). "'"); // Query all posts in the recently deleted topic
			
			while ( $prow = mysql_fetch_row($posts_in_forum) )
			{
				// Going through every post in the topic
				$pCount = mysql_fetch_row($Cmysql->Query("SELECT post_count FROM users WHERE id='" .$Cmysql->EscapeString($prow[0]). "'")); // Query the poster's post count
				
				$Cmysql->Query("UPDATE users SET post_count=" .($pCount[0] - 1). " WHERE id='" .$Cmysql->EscapeString($prow[0]). "'"); // Remove one post from count
			}
			
			$pDel = $Cmysql->Query("DELETE FROM posts WHERE forumid='" .$Cmysql->EscapeString($_POST['forum_id']). "'");
			
			// $pDel is TRUE if we succeeded
			// $pDel is FALSE if we failed
			
			if ( $pDel == FALSE )
			{
				// Failed to delete the posts
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
					'BODY'	=>	"{LANG_FORUMS_DELETE_POSTS_SQL_ERROR}", // Error text
					'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
			} elseif ( $pDel == TRUE )
			{
				// Deleted the posts
				$Ctemplate->useTemplate("successbox", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
					'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
					'BODY'	=>	"{LANG_FORUMS_DELETE_POSTS_SUCCESS_HEAD}", // Success text
					'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
			}
			
			$Ctemplate->useStaticTemplate("forum/forums_backtolist", FALSE); // Return button
		}
	}
}
/* Deleting a forum */

/* Listing forums */
if ( !isset($_POST['action']) )
{
	// Do listing only if we opened the list in plain mode
	
	$Ctemplate->useTemplate("forum/forums_table_open", array(
		'ADMIN_CREATE_NEW_FORUM'	=>	($uLvl >= 3 ?
			$Ctemplate->useStaticTemplate("forum/forums_admin_createnew", TRUE) // Return the button
		: NULL ), // Output button for new forum only if the user is admin or higher
		'ADMIN_ACTIONS'	=>	($uLvl >= 2 ? 
			$Ctemplate->useStaticTemplate("forum/forums_admin_actions", TRUE) // Return the header
		: NULL ) // Output header for admin actions only if the user is moderator or higher
	), FALSE); // Open the table and output header
	
	$forums_data = $Cmysql->Query("SELECT * FROM forums WHERE minLevel <= '" .$uLvl. "'"); // Query down the forums (only which the user has rights to see)
	
	while ( $row = mysql_fetch_assoc($forums_data) )
	{
		// Going through every row of the returned dataset,
		// output rows for forums
		
		// Count the threads in the forum
		$thread_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM topics WHERE forumid='" .$row['id']. "'"));
		
		// Get last post
		$last_post = mysql_fetch_assoc($Cmysql->Query("SELECT id, topicid, createuser, createdate FROM posts WHERE forumid='" .$row['id']. "' ORDER BY createdate DESC LIMIT 1"));
		// and get last poster's name
		$last_post_user = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id='" .$last_post['createuser']. "'"));
		
		// Count the posts in the forum
		$post_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM posts WHERE forumid='" .$row['id']. "'"));
		
		$Ctemplate->useTemplate("forum/forums_table_row", array(
			'FORUM_ID'	=>	$row['id'], // ID of the forum
			'TITLE'	=>	$row['title'], // Forum's title
			'DESC'	=>	$row['info'], // Description
			'CREATE_DATE'	=>	fDate($row['createdate']), // Creation date (human-readable formatted)
			'LAST_POST'	=>	($last_post['createdate'] != NULL ? $Ctemplate->useTemplate("forum/topics_table_row_last_post", array(
				'DATESTAMP'	=>	fDate($last_post['createdate']),
				'NAME'	=>	$last_post_user[0],
				'TOPICID'	=>	$last_post['topicid'],
				'POSTID'	=>	$last_post['id'],
				'USERID'	=>	$last_post['createuser']
			), TRUE) : $wf_lang['{LANG_POSTS_NO}']),
			'THREADS'	=>	$thread_count[0],
			'POSTS'	=>	$post_count[0],
			'EDIT'	=>	($uLvl >= 2 ? 
				$Ctemplate->useTemplate("forum/forums_admin_edit", array(
					'FORUM_ID'	=>	$row['id'] // ID of the forum
				), TRUE) // Return the button
			: NULL ), // Output edit button for admin actions only if the user is moderator or higher
			'DELETE'	=>	($uLvl >= 3 ? 
				$Ctemplate->useTemplate("forum/forums_admin_delete", array(
					'FORUM_ID'	=>	$row['id'] // ID of the forum
				), TRUE)
			: NULL ) // Output delete button for admin actions only if the user is moderator or higher
		), FALSE); // Output row
	}
	
	$Ctemplate->useStaticTemplate("forum/forums_table_close", FALSE); // Close the table
}
/* Listing forums */

$Ctemplate->useStaticTemplate("forum/forums_foot", FALSE); // Footer
DoFooter();
?>
