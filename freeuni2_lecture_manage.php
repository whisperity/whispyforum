<?php
 /**
 * WhispyForum script file - freeuni2_lecture_manage.php
 * 
 * Managing own (previously signed up to) lectures, and revoking signing
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 1 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadásokra jelentkezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_foot", FALSE); // Footer
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
	// Allowing access
	if ( ( isset($_POST['hour']) ) && ( isset($_POST['lecture']) ) )
	{
		// If we were supplied with hour and lecture numbers
		// Do depicking
		// So deassociate the user's set hour from the set lecture
		
		$dpQuery = $Cmysql->Query("UPDATE users SET hour" .$Cmysql->EscapeString($_POST['hour']). "=NULL WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"); // Will be TRUE if we succeed
		
		if ( $dpQuery == FALSE )
		{
			// If we failed, give error form
			$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_error", FALSE);
		} elseif ( $dpQuery == TRUE )
		{
			// If we succeeded, give success
			$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_success", FALSE);
		}
	} else {
		$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_table_open", FALSE); // Opening table
		
		for ($i = 1; $i <= 4; $i++)
		{
			// Listing every four lecture
			$lectID = mysql_fetch_row($Cmysql->Query("SELECT hour" .$i. " FROM users WHERE id=" .$Cmysql->EscapeString($_SESSION['uid'])));
			
			if ( $lectID[0] == NULL )
			{
				// If the user leaved the hour without selection
				// Output a row
				$Ctemplate->useTemplate("freeuni2/lecture_manage_table_row", array(
					'HOUR'	=>	$i,
					'COLSPAN'	=>	2, // Lecture name column spanning (full row)
					'LECTURE_NAME'	=>	"A megadott órában nem választottál előadást.",
					'LECTURER'	=>	'<a href="freeuni2_lecture_select.php" alt="Előadásokra jelentkezés">Előadás választása</a>',
					'FORM'	=>	NULL
				), FALSE);
			} else {
				// If we selected, give info row
				
				// First, query the lecture data
				$lectData = mysql_fetch_assoc($Cmysql->Query("SELECT lecture_name, lecturer FROM fu2_lectures WHERE id='" .$Cmysql->EscapeString($lectID[0]). "'"));
				$Ctemplate->useTemplate("freeuni2/lecture_manage_table_row", array(
					'HOUR'	=>	$i,
					'COLSPAN'	=>	1, // Lecture name column spanning (normal)
					'LECTURE_NAME'	=>	$lectData['lecture_name'],
					'LECTURER'	=>	$lectData['lecturer'],
					'FORM'	=>	$Ctemplate->useTemplate("freeuni2/lecture_manage_form", array(
						'HOUR'	=>	$i, // Hour #
						'LECTURE'	=>	$lectID[0] // Lecture ID
					), TRUE)
				), FALSE);
			}
		}
		
		$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_table_close", FALSE); // Closing table
	}
}

$Ctemplate->useStaticTemplate("freeuni2/lecture_manage_foot", FALSE); // Footer
DoFooter();
?>