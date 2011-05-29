<?php
 /**
 * WhispyForum script file - profile.php
 * 
 * Viewing one user's profile
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("user/profile_head", FALSE); // Header

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_NO_GUESTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_LOGGEDIN}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}" // Alternate picture text
	), FALSE ); // We give an unavailable error
} elseif ( $_SESSION['log_bool'] == TRUE)
{
// If user is logged in, the profile is accessible

if ( !isset($_GET['id']) )
{
	// If we opened page without specifying ID
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
		'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
		'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// Query the user's data from database
$userData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM users WHERE id='" .$Cmysql->EscapeString($_GET['id']). "'"));

if ( $userData == FALSE )
{
	// If the selected user does not exist
	
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_kuser.png", // User icon
		'TITLE'	=>	"{LANG_PROFILE_NO_USER}", // Error title
		'BODY'	=>	"{LANG_PROFILE_DOES_NOT_EXIST}", // Error text
		'ALT'	=>	"{LANG_PROFILE_NO_USER}" // Alternate picture text
	), FALSE ); // We give an error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
	DoFooter();
	exit;
}

//print "<h4>PROFILE</h4>";
//print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($userData,true))."<br>";

// Define avatar
if ( $userData['avatar_filename'] == NULL )
{
	$avatar = "themes/{THEME_NAME}/default_avatar.png";
} else {
	// If the user have a defined avatar, make it his SESSION avatar
	$avatar = "upload/usr_avatar/" .$userData['avatar_filename'];

	if ( !file_exists("upload/usr_avatar/" .$userData['avatar_filename']) )
	{
		$avatar = "themes/{THEME_NAME}/default_avatar.png";
	}
}

// Define user's level
switch ($userData['userLevel'])
{
	case 1:
		// User
		$levelName = $wf_lang['{LANG_USER}'];
		break;
	case 2:
		// Moderator
		$levelName = $wf_lang['{LANG_MODERATOR}'];
		break;
	case 3:
		// Administrator
		$levelName = $wf_lang['{LANG_ADMINISTRATOR}'];
		break;
	case 4:
		// Root admin
		$levelName = $wf_lang['{LANG_ROOT}'];
		break;
}

// Load the language definition of the language the user is using
include('language/' .$userData['language']. '/definition.php');
// This loads an array $wf_lang_def containing two keys essential for us here

/* Badge data */
$badges = array_keys($Cbadges->badge_array); // Create an array from the badges
$i = 0; // Define a counter on zero
$badge_embed = NULL; // Define the container variable

foreach($badges as $badge)
{
	// Going through every badge, fill the container
	if ( $badge != "LOCKED" )
	{
		// If the badge currently querying is not the 'LOCKED'
		// badge, fill the container. (Don't do the locked, because it's a meta-badge for not yet unlocked badge)
		
		if ( $i === 0 )
		{
			// If the counter is zero, create a new row to the table
			$badge_embed .= "<tr>";
		}
		
		$bData = $Cbadges->CheckBadge($badge, $_GET['id']); // Query the badge's data if the user have already earned it, or the LOCKED badge's data if it's locked for the user
		
		$badge_embed .= $Ctemplate->useTemplate("user/profile_badge", array(
			'BADGE_PIC'	=>	$bData['picture'],
			'BADGE'	=>	$bData['name'],
			'BADGE_TOOLTIP'	=>	$bData['tooltip'],
			'BADGE_EARNDATE'	=>	($bData['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($bData['earndate'])),
		), TRUE); // Put the badge's table cell to the container
		
		$i++; // Add one to i
		
		if ( $i === 4 )
		{
			// If the counter is four, close the opened row (we split badges into four per row)
			$badge_embed .= '</tr>';
			
			// Reset i to zero, so the next badge will be in the new row
			$i = 0;
		}
	}
}

if ( $i != 4 )
{
	// After embedding the badges, 
	// if we haven't filled up a complete row with four badges
	// we need to close the unclosed row to prevent output errors
	$badge_embed .= '</tr>';
}

/* Badge data */

$Ctemplate->useTemplate("user/profile_body", array(
	'USERNAME'	=>	$userData['username'],
	'EMAIL'	=>	$userData['email'],
	'REGDATE'	=>	fDate($userData['regdate']),
	'IMGSRC'	=>	$avatar,
	'LOG_STATUS'	=>	($userData['loggedin'] == 1 ? "online" : "offline"),
	'LOG_ALT'	=>	"{LANG_" . ($userData['loggedin'] == 1 ? "ONLINE" : "OFFLINE"). "}",
	'LEVEL'	=>	$levelName,
	'LANGUAGE'	=>	$wf_lang_def['LOCALIZED_NAME']." (".$wf_lang_def['SHORT_NAME'].")",
	'FORUM_TOPICS_PER_PAGE'	=>	$userData['forum_topic_count_per_page'],
	'FORUM_POSTS_PER_PAGE'	=>	$userData['forum_post_count_per_page'],
	'POST_COUNT'	=>	$userData['post_count'],
	
	'BADGES'	=>	$Cbadges->BadgeCount($_GET['id']),
	'TOTAL_BADGES'	=>	$Cbadges->TotalBadgeCount(),
	'BADGES_PERCENT'	=>	round(( ($Cbadges->BadgeCount($_GET['id']) / $Cbadges->TotalBadgeCount()) * 100), 1),
	
	'BADGES_EMBED'	=>	$badge_embed
), FALSE); // Output profile box

}
$Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
DoFooter();
?>
