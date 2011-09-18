<?php
 /**
 * WhispyForum script file - news.php
 * 
 * Listing news
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("news"); // Die if NEWS is disabled

$Ctemplate->useStaticTemplate("news/head", FALSE); // Header

$uLvl = $Cusers->getLevel(); // Get user level from database

if ( @$_POST['action'] == "newentry" )
{
	// If requested a form to add a new news entry
	
	if ( $uLvl < 2 )
	{
		// If the user is on lower level than Moderator
		
		// First, generate the variable which stores the
		// name of the level to be on to post news
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_TOPICS_THIS_FORUM_REQUIRES_MODERATOR}", // Error text
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
		
		// First, generate the variable which stores the
		// name of the level to be on to post news
		
		$Ctemplate->useTemplate("errormessage", array(
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
			'BODY'	=>	"{LANG_TOPICS_THIS_FORUM_REQUIRES_MODERATOR}", // Error text
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
	
	if ( $entry_add === TRUE )
	{
		// If failed to add entry, output error
		$Ctemplate->useTemplate("news/newentry_error", array(
			'TITLE'	=>	@$_POST['title'], // Title
			'DESCRIPTION'	=>	@$_POST['description'], // Description
			'CONTENT'	=>	@$_POST['content'], // Content (missing)
			'COMMENTABLE'	=>	( @$_POST['commentable'] == "yes" ? "yes" : "no" ) // Comments on/off on entry
		), FALSE);
	} elseif ( $entry_add === FALSE )
	{
		// If succeeded, redirect user to news list
		$Ctemplate->useTemplate("news/newentry_success", array(
			'TITLE'	=>	$_POST['title']
		), FALSE);
	}
	
	// Terminate execution
	$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
	DoFooter();
	exit;
}

$Ctemplate->useTemplate("news/list_head", array(
	'NEW_ENTRY'	=>	( $Cusers->getLevel() >= 2 ? 
		$Ctemplate->useStaticTemplate("news/list_newentry", TRUE) : NULL)
), FALSE); // Table header

/**
 * The news list is split, based on user setting.
 * Because of it, we need to generate a page switcher by using the 'LIMIT start, count'
 * syntax.
 */

$usr_news_split_value = 1; // Use the user's preference (queried from session)

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
		'DESCRIPTION'	=>	$row['description'], // Short description of entry
		
		//MORE
		//EDIT
		//DELETE
	), FALSE);
	
	echo prettyVar($row);
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

$Ctemplate->useStaticTemplate("news/foot", FALSE); // Footer
DoFooter();
?>
