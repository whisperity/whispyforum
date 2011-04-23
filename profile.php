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
	), FALSE ); // We give an unaviable error
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
$firstpost = $Cbadges->CheckBadge('FIRSTPOST', $_GET['id']);
$avatar_badge = $Cbadges->CheckBadge('AVATAR', $_GET['id']);
$fifty_post = $Cbadges->CheckBadge('FIFTY_POST', $_GET['id']);
$twentyfifty_post = $Cbadges->CheckBadge('TWENTYFIFTY_POST', $_GET['id']);
$fivehundred_post = $Cbadges->CheckBadge('FIVEHUNDRED_POST', $_GET['id']);
$thousand_post = $Cbadges->CheckBadge('THOUSAND_POST', $_GET['id']);

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
	
	/* FIRSTPOST badge */
	'BADGE_FIRSTPOST_PIC'	=>	$firstpost['picture'],
	'BADGE_FIRSTPOST'	=>	$firstpost['name'],
	'BADGE_FIRSTPOST_TOOLTIP'	=>	$firstpost['tooltip'],
	'BADGE_FIRSTPOST_EARNDATE'	=>	($firstpost['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($firstpost['earndate'])),
	
	/* AVATAR badge */
	'BADGE_AVATAR_PIC'	=>	$avatar_badge['picture'],
	'BADGE_AVATAR'	=>	$avatar_badge['name'],
	'BADGE_AVATAR_TOOLTIP'	=>	$avatar_badge['tooltip'],
	'BADGE_AVATAR_EARNDATE'	=>	($avatar_badge['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($avatar_badge['earndate'])),
	
	/* FIFTY_POST badge */
	'BADGE_FIFTY_POST_PIC'	=>	$fifty_post['picture'],
	'BADGE_FIFTY_POST'	=>	$fifty_post['name'],
	'BADGE_FIFTY_POST_TOOLTIP'	=>	$fifty_post['tooltip'],
	'BADGE_FIFTY_POST_EARNDATE'	=>	($fifty_post['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($fifty_post['earndate'])),
	
	/* TWENTYFIFTY_POST badge */
	'BADGE_TWENTYFIFTY_POST_PIC'	=>	$twentyfifty_post['picture'],
	'BADGE_TWENTYFIFTY_POST'	=>	$twentyfifty_post['name'],
	'BADGE_TWENTYFIFTY_POST_TOOLTIP'	=>	$twentyfifty_post['tooltip'],
	'BADGE_TWENTYFIFTY_POST_EARNDATE'	=>	($twentyfifty_post['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($twentyfifty_post['earndate'])),
	
	/* FIVEHUNDRED_POST badge */
	'BADGE_FIVEHUNDRED_POST_PIC'	=>	$fivehundred_post['picture'],
	'BADGE_FIVEHUNDRED_POST'	=>	$fivehundred_post['name'],
	'BADGE_FIVEHUNDRED_POST_TOOLTIP'	=>	$fivehundred_post['tooltip'],
	'BADGE_FIVEHUNDRED_POST_EARNDATE'	=>	($fivehundred_post['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($fivehundred_post['earndate'])),
	
	/* THOUSAND_POST badge */
	'BADGE_THOUSAND_POST_PIC'	=>	$thousand_post['picture'],
	'BADGE_THOUSAND_POST'	=>	$thousand_post['name'],
	'BADGE_THOUSAND_POST_TOOLTIP'	=>	$thousand_post['tooltip'],
	'BADGE_THOUSAND_POST_EARNDATE'	=>	($thousand_post['earndate'] == 0 ? $wf_lang['{LANG_NOT_YET}'] : fDate($thousand_post['earndate']))
), FALSE); // Output profile box

}
$Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
DoFooter();
?>