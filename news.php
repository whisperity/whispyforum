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

$Ctemplate->useStaticTemplate("news/list_head", FALSE); // Table header

/**
 * The topic list is split, based on user setting.
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
			'TITLE'	=>	$row['title'],
			'DATE'	=>	fdate($row['createdate']). " " .$wf_lang['{LANG_NEWS_BY}'],
			'USERID'	=>	$row['createuser'],
			'NAME'	=>	$postuser['username'],
			
			/* Following variable points are still needed */
			//TEXT
			//MORE
			//EDIT
			//DELETE
		), FALSE);
		
		echo prettyVar($row);
	}
	
	$Ctemplate->useStaticTemplate("news/list_foot", FALSE); // Table footer
	
	if ( $news_pages > 0 )
	{
		// If we have more than one topic list page
		
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
