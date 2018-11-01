<?php
 /**
 * WhispyForum script file - freeuni2_p1_list_performers.php
 * 
 * Listing performers in a sophisticated format (this is phase one thing)
 * but this file is a backwards compatible implementation in read-only mode.
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni/list_performers_head_p2", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 1 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadások szervezése (visszafele kompatibilitás)", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni/list_performers_foot", FALSE); // Footer
	DoFooter();
	exit;
}

if ( $_SESSION['log_bool'] == FALSE )
{
	// If the user is a guest
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"A weboldal nem érhető el vendégek számára!", // Error title
		'BODY'	=>	"A lap megtekintéséhez bejelentkezett felhasználónak kell lenned.<br><br>Kérlek használd a bejelentkezési űrlapot a bejelentkezéshez. Utána megtekintheted a tartalmat!", // Error text
		'ALT'	=>	"Házirendhiba" // Alternate picture text
	), FALSE ); // We give an unaviable error	
} elseif ( $_SESSION['log_bool'] == TRUE)
{
$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "' AND osztaly='" .$Cmysql->EscapeString($_SESSION['osztaly']). "'")); // We query the user's data

if ( $userDBArray['userLevel'] < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"Hozzáférési szint hiba", // Error title
		'BODY'	=>	"A lap megtekintéséhez Adminisztrátori, vagy magasabb jogkörrel kell rendelkezned", // Error text
		'ALT'	=>	"Házirendhiba" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $userDBArray['userLevel'] >= 3 )
{

// First we list performers who are done
// and will come

// If we in normal mode, this is printer-friendliness off
if ( !isset($_GET['printer']) )
{
	$_GET['printer'] = 0;
}

$row_template = "freeuni/list_performers_tablerow"; // We will later use the normal table rows


$Ctemplate->useTemplate("freeuni/list_table_open", array(
	'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
	'ADMIN_EDIT_HEAD'	=> "",
	'ADMIN_UNLOCK_HEAD'	=> "",
	'ADMIN_REMOVE_HEAD'	=> ""
), FALSE); // Opening the list table

$pWillCome = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='agreed'");

while ( $row_wc = mysql_fetch_assoc($pWillCome) )
{
	// Get linked user's name
	$userID_wc = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_wc['id'])); // Get the user's ID from relational table
	
	if ( $userID_wc[0] != NULL ) // Select only if there's present relation
	{
		$username_wc = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_wc[0])); // Get the username from the user's table
	} elseif ( $userID_wc[0] == NULL )
	{
		// If there isn't present relation
		$username_wc[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate($row_template, array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#83A90A"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_wc['pName'],
		'STUDENT_NAME'	=>	$username_wc[0],
		'EMAIL'	=>	$row_wc['email'],
		'TELEPHONE'	=>	$row_wc['telephone'],
		'COMMENTS'	=>	substr($row_wc['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Vállalja"
	), FALSE);
}

// Pending performers
$pPending = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='pending'");

while ( $row_p = mysql_fetch_assoc($pPending) )
{
	// Get linked user's name
	$userID_p = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_p['id'])); // Get the user's ID from relational table
	
	if ( $userID_p[0] != NULL ) // Select only if there's present relation
	{
		$username_p = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_p[0])); // Get the username from the user's table
	} elseif ( $userID_p[0] == NULL )
	{
		// If there isn't present relation
		$username_p[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate($row_template, array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#417FCC"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_p['pName'],
		'STUDENT_NAME'	=>	$username_p[0],
		'EMAIL'	=>	$row_p['email'],
		'TELEPHONE'	=>	$row_p['telephone'],
		'COMMENTS'	=>	substr($row_p['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Függőben"
	), FALSE);
}

// Unallocated performers
$pUnallocated = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='unallocated'");

while ( $row_u = mysql_fetch_assoc($pUnallocated) )
{
	// Get linked user's name
	$userID_u = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_u['id'])); // Get the user's ID from relational table
	
	if ( $userID_u[0] != NULL ) // Select only if there's present relation
	{
		$username_u = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_u[0])); // Get the username from the user's table
	} elseif ( $userID_u[0] == NULL )
	{
		// If there isn't present relation
		$username_u[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate($row_template, array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#B4CDEC"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_u['pName'],
		'STUDENT_NAME'	=>	$username_u[0],
		'EMAIL'	=>	$row_u['email'],
		'TELEPHONE'	=>	$row_u['telephone'],
		'COMMENTS'	=>	substr($row_u['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Szabad"
	), FALSE);
}

// Refused performers
$pRefused = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='refused'");

while ( $row_r = mysql_fetch_assoc($pRefused) )
{
	// Get linked user's name
	$userID_r = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_r['id'])); // Get the user's ID from relational table
	
	if ( $userID_r[0] != NULL ) // Select only if there's present relation
	{
		$username_r = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_r[0])); // Get the username from the user's table
	} elseif ( $userID_r[0] == NULL )
	{
		// If there isn't present relation
		$username_r[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate($row_template, array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#E58800"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_r['pName'],
		'STUDENT_NAME'	=>	$username_r[0],
		'EMAIL'	=>	$row_r['email'],
		'TELEPHONE'	=>	$row_r['telephone'],
		'COMMENTS'	=>	substr($row_r['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Nem vállalja"
	), FALSE);
}

$Ctemplate->useStaticTemplate("freeuni/list_table_close", FALSE); // Closing the list table
}
}

$Ctemplate->useStaticTemplate("freeuni/list_performers_foot", FALSE); // Footer
DoFooter();
?>