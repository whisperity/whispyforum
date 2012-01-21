<?php
 /**
 * WhispyForum script file - news.php
 * 
 * Handling the news managing: listing news, viewing entry, viewing/adding/editing comments and news entries
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("news"); // Die if NEWS is disabled

$Ctemplate->useStaticTemplate("news/head", FALSE); // Header

$uLvl = $Cusers->getLevel(); // Get user level from database

if ( ( @$_GET['action'] == "more" ) && ( @$_GET['id'] != NULL ) )
{
	// If the page was loaded to output all of a news entry,
	// but the variables were passed as HTTP GET, put them to HTTP POST.
	
	$_POST['action'] = @$_GET['action'];
	$_POST['id'] = @$_GET['id'];
	
	// Truncate the GET superglobal
	unset($_GET['action']);
	unset($_GET['id']);
}

if ( @$_POST['action'] == "newentry" )
{
	// If requested a form to add a new news entry
	
	if ( $uLvl < 2 )
	{
		// If the user is on lower level than Moderator
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	$wf_lang['{LANG_NEWS_NEWENTRY_REQUIRES_MODERATOR}'], // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
		), FALSE ); // Give rights error
	} elseif ( $uLvl >= 2 )
	{
		// Output form
		if ( @$_POST['error_goback'] == "yes" )
		{
			// If the user is redirected because of an error, form the data back in.
			$Ctemplate->useTemplate("news/newentry_form", array(
				'TITLE'	=>	$_POST['title'],
				'DESCRIPTION'	=>	$_POST['description'],
				'CONTENT'	=>	$_POST['content'],
				'COMMENTABLE_CHECK'	=>	($_POST['commentable'] == "yes" ? " checked" : NULL )
			), FALSE);
		} else {
			// If plain form requested, give plain form.
			
			$Ctemplate->useTemplate("news/newentry_form", array(
				'TITLE'	=>	NULL,
				'DESCRIPTION'	=>	NULL,
				'CONTENT'	=>	NULL,
				'COMMENTABLE_CHECK'	=>	NULL
			), FALSE);
		}
	}
	
	// Terminate execution
	$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
	DoFooter();
	exit;
} elseif ( @$_POST['action'] == "post_new_entry" )
{
	// If requested SQL operation to add new news entry
	
	if ( $uLvl < 2 )
	{
		// If the user is on lower level than Moderator
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	$wf_lang['{LANG_NEWS_NEWENTRY_REQUIRES_MODERATOR}'], // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
		), FALSE ); // Give rights error
	} elseif ( $uLvl >= 2 )
	{
		// If the user is eligible to post the entry, do it
		
		// First, make sure all required field is entered
		if ( @$_POST['title'] == NULL ) // Title
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/newentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_TITLE}", // Missing variable name
				'TITLE'	=>	@$_POST['title'], // Title (missing)
				'DESCRIPTION'	=>	@$_POST['description'], // Description
				'CONTENT'	=>	@$_POST['content'], // Content
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['description'] == NULL ) // Description
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/newentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_DESCRIPTION}", // Missing variable name
				'TITLE'	=>	@$_POST['title'], // Title
				'DESCRIPTION'	=>	@$_POST['description'], // Description (missing)
				'CONTENT'	=>	@$_POST['content'], // Content
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['content'] == NULL ) // Content
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/newentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_CONTENT}", // Missing variable name
				'TITLE'	=>	@$_POST['title'], // Title
				'DESCRIPTION'	=>	@$_POST['description'], // Description
				'CONTENT'	=>	@$_POST['content'], // Content (missing)
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
	}
	
	// If every variable is entered, do SQL query
	$entry_add = $Cmysql->Query("INSERT INTO news(title, createuser, createdate, description, content, commentable) VALUES (
		'" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['title'])). "',
		'" .$_SESSION['uid']. "',
		'" .time(). "',
		'" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['description'])). "',
		'" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['content'])). "',
		'" .(@$_POST['commentable'] == "yes" ? 1 : 0 ). "')");
	
	if ( $entry_add === FALSE )
	{
		// If failed to add entry, output error
		$Ctemplate->useTemplate("news/newentry_error", array(
			'TITLE'	=>	@$_POST['title'], // Title
			'DESCRIPTION'	=>	@$_POST['description'], // Description
			'CONTENT'	=>	@$_POST['content'], // Content (missing)
			'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
		), FALSE);
	} elseif ( $entry_add === TRUE )
	{
		// If succeeded, redirect user to news list
		$Ctemplate->useTemplate("news/newentry_success", array(
			'TITLE'	=>	$_POST['title']
		), FALSE);
	}
} elseif ( ( @$_POST['action'] == "editentry" ) && ( @$_POST['id'] != NULL ) )
{
	// If requested a form to edit an entry
	
	if ( $uLvl < 2 )
	{
		// If the user is on lower level than Moderator
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	$wf_lang['{LANG_NEWS_EDIT_REQUIRES_MODERATOR}'], // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
		), FALSE ); // Give rights error
	} elseif ( $uLvl >= 2 )
	{
		// Output form
		
		// Fetch information from database
		$nEntry = mysql_fetch_assoc($Cmysql->Query("SELECT title, description, content, commentable FROM news WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
		
		if ( @$_POST['error_goback'] == "yes" )
		{
			// If the user is redirected because of an error, form the data back in.
			$Ctemplate->useTemplate("news/editentry_form", array(
				'TITLE'	=>	$_POST['title'],
				'ORIGINAL_TITLE'	=>	$nEntry['title'],
				'DESCRIPTION'	=>	$_POST['description'],
				'CONTENT'	=>	$_POST['content'],
				'COMMENTABLE_CHECK'	=>	($_POST['commentable'] == "yes" ? " checked" : NULL ),
				'ID'	=>	$_POST['id']
			), FALSE);
		} else {
			// If plain form requested, give the original data
			
			$Ctemplate->useTemplate("news/editentry_form", array(
				'TITLE'	=>	$nEntry['title'],
				'ORIGINAL_TITLE'	=>	$nEntry['title'], // The original and the new title is the same at the first step of editing
				'DESCRIPTION'	=>	$nEntry['description'],
				'CONTENT'	=>	$nEntry['content'],
				'COMMENTABLE_CHECK'	=>	($nEntry['commentable'] == 1 ? " checked" : NULL ),	
				'ID'	=>	$_POST['id']
			), FALSE);
		}
	}
	
	// Terminate execution
	$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
	DoFooter();
	exit;
} elseif ( ( @$_POST['action'] == "do_edit_entry" ) && ( @$_POST['id'] != NULL ) )
{
	// If requested SQL operation to edit the news entry
	
	if ( $uLvl < 2 )
	{
		// If the user is on lower level than Moderator
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	$wf_lang['{LANG_NEWS_EDIT_REQUIRES_MODERATOR}'], // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
		), FALSE ); // Give rights error
	} elseif ( $uLvl >= 2 )
	{
		// If the user is eligible to edit the entry, do it
		
		// First, make sure all required field is entered
		if ( @$_POST['title'] == NULL ) // Title
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/editentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_TITLE}", // Missing variable name
				'ID'	=>	$_POST['id'], // Post ID
				'TITLE'	=>	@$_POST['title'], // Title (missing)
				'DESCRIPTION'	=>	@$_POST['description'], // Description
				'CONTENT'	=>	@$_POST['content'], // Content
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['description'] == NULL ) // Description
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/editentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_DESCRIPTION}", // Missing variable name
				'ID'	=>	$_POST['id'], // Post ID
				'TITLE'	=>	@$_POST['title'], // Title
				'DESCRIPTION'	=>	@$_POST['description'], // Description (missing)
				'CONTENT'	=>	@$_POST['content'], // Content
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['content'] == NULL ) // Content
		{
			// Output error box
			
			$Ctemplate->useTemplate("news/editentry_variable_error", array(
				'VARIABLE'	=>	"{LANG_NEWS_CONTENT}", // Missing variable name
				'ID'	=>	$_POST['id'], // Post ID
				'TITLE'	=>	@$_POST['title'], // Title
				'DESCRIPTION'	=>	@$_POST['description'], // Description
				'CONTENT'	=>	@$_POST['content'], // Content (missing)
				'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
			), FALSE);
			
			// Terminate execution
			$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
			DoFooter();
			exit;
		}
	}
	
	// If every variable is entered, do SQL query
	$entry_edit = $Cmysql->Query("UPDATE news SET 
		title='" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['title'])). "',
		description='" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['description'])). "',
		content='" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['content'])). "',
		commentable='" .(@$_POST['commentable'] == "yes" ? 1 : 0 ). "' WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'");
	
	if ( $entry_edit === FALSE )
	{
		// If failed to add entry, output error
		$Ctemplate->useTemplate("news/editentry_error", array(
			'TITLE'	=>	@$_POST['title'], // Title
			'ID'	=>	$_POST['id'], // Post ID
			'DESCRIPTION'	=>	@$_POST['description'], // Description
			'CONTENT'	=>	@$_POST['content'], // Content (missing)
			'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
		), FALSE);
	} elseif ( $entry_edit === TRUE )
	{
		// If succeeded, redirect user to news list
		$Ctemplate->useTemplate("news/editentry_success", array(
			'ID'	=>	$_POST['id'],
			'TITLE'	=>	$_POST['title']
		), FALSE);
	}
} elseif ( ( @$_POST['action'] == "deleteentry" ) && ( @$_POST['id'] != NULL ) ) 
{
	// If we requested to delete the entry, first, perform initial checks
	// Deleting an entry requires level 3.
	
	if ( $uLvl < 3 )
	{
		// If the user is on lower level than Administrator
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	$wf_lang['{LANG_NEWS_DELETE_REQUIRES_ADMINISTRATOR}'], // Error text
			'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
		), FALSE ); // Give rights error
	} elseif ( $uLvl >= 3 )
	{
		// Do removal of news entry  first
		$nDel = $Cmysql->Query("DELETE FROM news WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'");
		
		if ( $nDel == FALSE )
		{
			// Failed to delete the entry
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
				'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
				'BODY'	=>	"{LANG_NEWS_DELETEENTRY_ERROR}", // Error text
				'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
			), FALSE ); // We give an error
			
			$Ctemplate->useTemplate("news/list_back", array(
				'START_AT'	=>	$_POST['start_at']
			), FALSE); // Return button
		} elseif ( $nDel == TRUE )
		{
			// Deleted the entry
			$Ctemplate->useTemplate("successbox", array(
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Folder with pencil icon
				'TITLE'	=>	"{LANG_SUCCESS_EXCLAMATION}", // Success title
				'BODY'	=>	"{LANG_NEWS_DELETEENTRY_SUCCESS}", // Success text
				'ALT'	=>	"{LANG_SUCCESS_EXCLAMATION}" // Alternate picture text
			), FALSE ); // We give success
			
			// Delete the comments
			$cDel = $Cmysql->Query("DELETE FROM news_comments WHERE news_id='" .$Cmysql->EscapeString($_POST['id']). "'");
			
			if ( $cDel == FALSE )
			{
				// Give error only if we failed removing the comments
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
					'TITLE'	=>	"{LANG_ERROR_EXCLAMATION}", // Error title
					'BODY'	=>	"{LANG_NEWS_DELETEENTRY_COMMENT_ERROR}", // Error text
					'ALT'	=>	"{LANG_ERROR_EXCLAMATION}" // Alternate picture text
				), FALSE ); // We give an error
			}
			
			$Ctemplate->useTemplate("news/list_back", array(
				'START_AT'	=>	$_POST['start_at']
			), FALSE); // Return button
		}
	}
	
	// Terminate execution
	$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
	DoFooter();
	exit;
} elseif ( ( @$_POST['action'] == "more" ) && ( @$_POST['id'] != NULL ) )
{
	// If we requested to give the whole contents of a news entry
	
	// First, we query whether the entry exists,
	// but if we are already there, we only select the needed columns.
	
	$entry_data = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, createuser, createdate, description, content, commentable FROM news WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
	
	if ( $entry_data === FALSE )
	{
		// If the selected entry does not exist, we output an error message and
		// give the user the ability to go back
		
		$Ctemplate->useStaticTemplate("news/entry_missing_error", FALSE);
	} elseif ( $entry_data == TRUE )
	{
		// If the entry is in the database, we further parse and then output the contents
		
		// Select the name of the user posting the entry
		$postuser = mysql_fetch_assoc($Cmysql->Query("SELECT username FROM users WHERE id='" .$entry_data['createuser']. "'"));
		
		$Ctemplate->useTemplate("news/more", array(
			'TITLE'	=>	$entry_data['title'], // News title
			'DATE'	=>	fdate($entry_data['createdate']). " " .$wf_lang['{LANG_NEWS_BY}'], // Post date
			'USERID'	=>	$entry_data['createuser'], // Poster ID
			'NAME'	=>	$postuser['username'], // Poster name
			'DESCRIPTION'	=>	bbDecode($entry_data['description']), // Short description of entry
			'CONTENT'	=>	bbDecode($entry_data['content']), // Full entry text
			
			// Button for administrative tasks
			'EDIT'	=>	( $uLvl >= 2 ? $Ctemplate->useTemplate("News/list_admin_edit", array(
							'ID'	=>	$entry_data['id']
						), TRUE) : NULL),
			'DELETE'	=>	( $uLvl >= 3 ? $Ctemplate->useTemplate("News/list_admin_delete", array(
							'ID'	=>	$entry_data['id'],
							'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
						), TRUE) : NULL),
		), FALSE);
		
		if ( $entry_data['commentable'] == 1 )
		{
			// If the entry is commentable, output the comments
			
			// Decide whether the user is authorized to post a new comment
			$comment_authorized = FALSE; // We need to declare the variable first
			if ( $uLvl >= 1 )
			{
				// If the user is USER or higher, they are authorized to post comments
				$comment_authorized = TRUE;
			}
			
			// Output the beginning for the table
			$Ctemplate->useTemplate("news/comment_list_head", array(
				'NEW_COMMENT'	=>	($comment_authorized ? $Ctemplate->useTemplate(
					"news/comment_list_head_newcomment", array(
						'ENTRY_ID'	=>	$entry_data['id']
					), TRUE) : NULL)
			), FALSE);
			
			// Query all the comments assigned to this news entry from the database
			$comment_result = $Cmysql->Query("SELECT * FROM news_comments WHERE news_id='" .$Cmysql->EscapeString($entry_data['id']). "' ORDER BY createdate DESC");
			
			// Going through every comment entry, output them to the user
			while ( $comment_row = mysql_fetch_assoc($comment_result) )
			{
				// Query commenter's data
				$uData = mysql_fetch_assoc($Cmysql->Query("SELECT username, regdate, loggedin, avatar_filename, news_comment_count FROM users WHERE id='" .$Cmysql->EscapeString($comment_row['createuser']). "'"));
				
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
				
				// Output one row entry
				$Ctemplate->useTemplate("news/comment_row", array(
					'ID'	=>	$comment_row['id'], // Comment ID
					'USERID'	=>	$comment_row['createuser'], // Commenter's ID
					'USERNAME'	=>	$uData['username'], // Commenter's name
					'IMGSRC'	=>	$poster_avatar, // Commenter's avatar (or your theme's default if poster does not have one)
					'REGDATE'	=>	fDate($uData['regdate']), // Commenter's registration date
					'NEWS_COMMENT_COUNT'	=>	$uData['news_comment_count'], // Comment count of the user
					'LOG_STATUS'	=>	($uData['loggedin'] == 1 ? "online" : "offline"), // Logged in or out picture
					'LOG_ALT'	=>	"{LANG_" . ($uData['loggedin'] == 1 ? "ONLINE" : "OFFLINE"). "}", // Alternate text for log_status picture
					'DATE'	=>	fDate($comment_row['createdate']), // Post date
					'TEXT'	=>	bbDecode($comment_row['content']), // The post itself
				), FALSE);
			}
			
			// Output ending for the table containing the comments
			$Ctemplate->useStaticTemplate("news/comment_list_foot", FALSE);
		}
	}
} elseif ( ( @$_POST['action'] == "newcomment" ) && ( @$_POST['id'] != NULL ) )
{
	// If the user decided to post a comment and the news entry's ID was forwarded, let him/her post his/her comment
	
	// Check whether the requested news entry exists
	$entry_data = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, commentable FROM news WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
	
	if ( $entry_data === FALSE )
	{
		// If the selected entry does not exist, we output an error message and
		// give the user the ability to go back
		
		$Ctemplate->useStaticTemplate("news/entry_missing_error", FALSE);
	} elseif ( $entry_data == TRUE )
	{
		// If the entry exists, check whether it is commentable or not
		if ( $entry_data['commentable'] == 0 )
		{
			// If not commentable, output error message
			ambox('ERROR', "Entry is not commentable."); // Placeholder
		} elseif ( $entry_data['commentable'] == 1 )
		{
			// If commentable, output the comment form
			
			if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
			{
				// We output the form with data returned (user doesn't have to enter it again)
				$Ctemplate->useTemplate("news/comment_create_form", array(
					'ENTRY_ID'	=>	$entry_data['id'],
					'ENTRY_TITLE'	=>	$entry_data['title'],
					'CONTENT'	=>	$_POST['content']
				), FALSE);
			} else {
				// We output general form
				$Ctemplate->useTemplate("news/comment_create_form", array(
					'ENTRY_ID'	=>	$entry_data['id'],
					'ENTRY_TITLE'	=>	$entry_data['title'],
					'CONTENT'	=>	NULL
				), FALSE);
			}
		}
	}
} elseif ( ( @$_POST['action'] == "postcomment" ) && ( @$_POST['id'] != NULL ) )
{
	// If the user posted a comment and the news entry's ID was forwarded, store the comment in the database
	
	// Check whether the requested news entry exists
	$entry_data = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, commentable FROM news WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
	
	if ( $entry_data === FALSE )
	{
		// If the selected entry does not exist, we output an error message and
		// give the user the ability to go back
		
		$Ctemplate->useStaticTemplate("news/entry_missing_error", FALSE);
	} elseif ( $entry_data == TRUE )
	{
		// If the entry exists, check whether it is commentable or not
		if ( $entry_data['commentable'] == 0 )
		{
			// If not commentable, output error message
			$Ctemplate->useTemplate("news/comment_create_not_commentable", array(
				'ENTRY_ID'	=>	$_POST['id'] // News entry ID
			), FALSE);
		} elseif ( $entry_data['commentable'] == 1 )
		{
			// If the entry is commentable, do the commenting
			
			// Check whether the comment field is empty
			if ( $_POST['content'] == NULL ) // Content
			{
				$Ctemplate->useTemplate("news/comment_create_variable_error", array(
					'VARIABLE'	=>	"{LANG_NEWS_COMMENTS_COMMENT}", // Errornous variable name
					'ENTRY_ID'	=>	$_POST['id'], // News entry ID
					'CONTENT'	=>	@$_POST['content'] // Password (should be empty)
				), FALSE);
				// We terminate the script
				$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
				DoFooter();
				exit;
			}
			
			// If the required variable is present and has value, do the SQL query
			$comment_store = $Cmysql->Query("INSERT INTO news_comments(news_id, createuser, createdate, content) VALUES 
			('" .$Cmysql->EscapeString($_POST['id']). "',
			'" .$Cmysql->EscapeString($_SESSION['uid']). "',
			'" .time(). "',
			'" .$Cmysql->EscapeString(str_replace("'", "\'", $_POST['content'])). "')");
			
			if ( $comment_store === TRUE )
			{
				// If the query succeeded, output success box and return the user to the news entry
				$Ctemplate->useTemplate("news/comment_create_success", array(
					'ENTRY_ID'	=>	$_POST['id'], // News entry ID
				), FALSE);
			} elseif ( $comment_store === FALSE )
			{
				// If the query failed, output an error box
				$Ctemplate->useTemplate("news/comment_create_error", array(
					'ENTRY_ID'	=>	$_POST['id'], // News entry ID
					'CONTENT'	=>	$_POST['content'] // Password (should be empty)
				), FALSE);
			}
		}
	}
} else {
	// If no variables are fitting the previous cases, we list the news in a short list
	
	$Ctemplate->useTemplate("news/list_head", array(
		'NEW_ENTRY'	=>	( $uLvl >= 2 ? 
			$Ctemplate->useStaticTemplate("news/list_newentry", TRUE) : NULL)
	), FALSE); // Table header
	
	/**
	 * The news list is split, based on user setting.
	 * Because of it, we need to generate a page switcher by using the 'LIMIT start, count'
	 * syntax.
	 */
	
	$usr_news_split_value = $_SESSION['news_split_value']; // Use the user's preference (queried from session)
	
	
	// Query the total number of news
	$news_count = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM news"));
	
	// Generate the number of pages (we need to ceil it up because we need to have integer pages)
	$news_pages = ceil($news_count[0] / $usr_news_split_value);
	
	// Generate the start_at value
	if ( @$_GET['start_at'] == NULL )
	{
		// If the value is missing, we will assume 0 as the start
		$news_start = 0;
	} elseif ( @$_GET['start_at'] != NULL )
	{
		// If we have start value, multiply it with the split value so it'll show the correct page
		$news_start = $_GET['start_at'] * $usr_news_split_value;
	}
	
	// Query the news of the following page
	$news = $Cmysql->Query("SELECT * FROM news ORDER BY createdate DESC LIMIT " .$news_start.", ".$Cmysql->EscapeString($usr_news_split_value));
	
	while ($row = mysql_fetch_assoc($news) )
	{
		// Going through every entry, output a row for it
		
		// Select the name of the user posting the entry
		$postuser = mysql_fetch_assoc($Cmysql->Query("SELECT username FROM users WHERE id='" .$row['createuser']. "'"));
		
		$Ctemplate->useTemplate("news/list_entry", array(
			'TITLE'	=>	$row['title'], // News title
			'DATE'	=>	fdate($row['createdate']). " " .$wf_lang['{LANG_NEWS_BY}'], // Post date
			'USERID'	=>	$row['createuser'], // Poster ID
			'NAME'	=>	$postuser['username'], // Poster name
			'DESCRIPTION'	=>	bbDecode($row['description']), // Short description of entry
			
			// Button to read the whole entry
			'MORE'	=>	$Ctemplate->useTemplate("news/list_more", array(
							'ENTRY_ID'	=>	$row['id']
						), TRUE),
			
			// Buttons for administrative tasks
			'EDIT'	=>	( $uLvl >= 2 ? $Ctemplate->useTemplate("News/list_admin_edit", array(
							'ID'	=>	$row['id']
						), TRUE) : NULL),
			'DELETE'	=>	( $uLvl >= 3 ? $Ctemplate->useTemplate("News/list_admin_delete", array(
							'ID'	=>	$row['id'],
							'START_AT'	=>	(@$_GET['start_at'] == NULL ? '0' : $_GET['start_at'])
						), TRUE) : NULL),
		), FALSE);
	}
	
	$Ctemplate->useStaticTemplate("news/list_foot", FALSE); // Table footer
	
	if ( $news_pages > 0 )
	{
		// If we have more than one news list page
		
		// Generate embedded pager
		$pages = ""; // Define the variable
		for ( $p = 0; $p <= ($news_pages-1); $p++ )
		{
			$pages .= $Ctemplate->useTemplate("news/list_page_embed", array(
				'START_AT'	=>	$p,
				'PAGE_NUMBER'	=>	($p+1)
			), TRUE);
		}
		
		// Output switcher table
		$Ctemplate->useTemplate("news/list_pages_table", array(
			'CURRENT_PAGE'	=>	(@$_GET['start_at']+1), // Number of current page
			'PAGES_EMBED'	=>	$pages, // Embedding the generated pages box
			'PAGE_TOTAL'	=>	$news_pages
		), FALSE);
	}
}

$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
DoFooter();
?>
