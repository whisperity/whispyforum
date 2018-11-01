<?php
 /**
 * WhispyForum script file - freeuni2_lecture_select.php
 * 
 * Signing up for lectures
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni2/lecture_select_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 2 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadásokra jelentkezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni2/lecture_select_foot", FALSE); // Footer
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
		// Do picking up
		// So associate the user's set hour to the set lecture
		
		// But we first do some checks
		// We do not associate the user if:
		// The user is already associated in the set hour
		// The selected lecture does not take place in the set hour
		// The selected lecture's limit has been reached for the set hour
		
		// Query the lecture
		$row = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM fu2_lectures WHERE id=" .$Cmysql->EscapeString($_POST['lecture'])));
		
		// Query user's lecture relations
		$uLects = mysql_fetch_assoc($Cmysql->Query("SELECT hour1, hour2, hour3, hour4 FROM users WHERE id=" .$_SESSION['uid']));
		
		$pickup = NULL; // Define the $pickup array (it'll contain the output for hours)
		
		// Output can be:
		// success - pick up for the hour
		// no lecture - lecture does not take place in that hour
		// limit - lecture listeners limit reached
		// already - user already choosed a lecture for this hour
		
		// Check for ALREADY
		if ( @$uLects['hour'.$_POST['hour']] != NULL ) // If the user selected a lecture for set hour
		{
			// Give already error
			$pickup= $Ctemplate->useTemplate("freeuni2/lecture_select_error_already", array(
				'HOUR'	=>	$_POST['hour']
			), TRUE);
		}
			
		// Check for LIMIT
		// Query down users having the lecture picked up for set hour
		$uCount = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hour" .$Cmysql->EscapeString($_POST['hour']). ") FROM users WHERE hour" .$Cmysql->EscapeString($_POST['hour']). "='" .$Cmysql->EscapeString($row['id']). "'"));
		
		if ( ( $uCount[0] >= $row['limit'.$_POST['hour']] ) && ( $row['limit'.$_POST['hour']] != 0 ) ) // If the user count is higher than the limit for set hour, and the lecture has limit (so the limit isn't 0)
		{
			// Give limit error
			$pickup = $Ctemplate->useTemplate("freeuni2/lecture_select_error_limit", array(
				'HOUR'	=>	$_POST['hour']
			), TRUE);
		}
		
		// Check for NO LECTURE
		if ( $row['hour'.$_POST['hour']] == "no" ) // If the lecture does not take place in set hour
		{
			// Give no lecture error
			$pickup = $Ctemplate->useTemplate("freeuni2/lecture_select_error_nolecture", array(
				'HOUR'	=>	$_POST['hour']
			), TRUE);
		}
		
		// Check for SUCCESS
		// $pickup is NULL if the previous ifs haven't modified it
		if ( $pickup == NULL )
		{
			// Pick up the user
			$puQuery = $Cmysql->Query("UPDATE users SET hour" .$Cmysql->EscapeString($_POST['hour'])."='" .$Cmysql->EscapeString($_POST['lecture']). "' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"); // Will be TRUE if we succeed
			
			if ( $puQuery == FALSE )
			{
				// If we failed picking up, give error message
				$Ctemplate->useStaticTemplate("freeuni2/lecture_select_error", FALSE);
			} elseif ( $puQuery == TRUE )
			{
				// If we successfully picked up, give success message
				$Ctemplate->useStaticTemplate("freeuni2/lecture_select_success", FALSE);
			}
		}
	} else {
		// Give a neat lecture list
			
		$myLectures = array(); // Define an empty array
		
		for ($j = 1; $j <= 4; $j++)
		{
			// Listing every four lecture
			$mLectID = mysql_fetch_row($Cmysql->Query("SELECT hour" .$j. " FROM users WHERE id=" .$Cmysql->EscapeString($_SESSION['uid'])));
			
			if ( $mLectID[0] == NULL )
			{
				// If the user leaved the hour without selection
				
				$myLectures[$j] = '<img src="themes/winky/freeuni2_nolecture.gif" alt="Nem választottál">'; // It'll be an X symbol (no)
			} else {
				// If we selected, give info row
			
				$myLectures[$j] = '<img src="themes/winky/freeuni2_yes.gif" alt="Választottál">'; // It'll be an tick symbol (yes)
			}
		}
		
		$Ctemplate->useTemplate("freeuni2/index_my_lectures", array(
			'WIDTH'	=>	"300px", // Box width
			'HOUR1'	=>	$myLectures[1],
			'HOUR2'	=>	$myLectures[2],
			'HOUR3'	=>	$myLectures[3],
			'HOUR4'	=>	$myLectures[4]
		), FALSE); // Give my lectures statictics
		
		// Listing lectures
		$Ctemplate->useStaticTemplate("freeuni2/lecture_select_table_open", FALSE); // Opening table
		
		// Query all lectures
		$lectures = $Cmysql->Query("SELECT * FROM fu2_lectures ORDER BY lecture_name ASC");
		
		// Query user's lecture relations
		$uLects = mysql_fetch_assoc($Cmysql->Query("SELECT hour1, hour2, hour3, hour4 FROM users WHERE id=" .$_SESSION['uid']));
		
		while ( $row = mysql_fetch_assoc($lectures) )
		{
			$pickup = array(); // Define the $pickup array (it'll contain the output for hours)
			
			for ($i = 1; $i <= 4; $i++)
			{
				// We go through a checking for every sigle hour
				// Output can be:
				// form - pick up form for the hour
				// no lecture - lecture does not take place in that hour
				// limit - lecture listeners limit reached
				// already - user already choosed a lecture for this hour
				
				$pickup[$i] = NULL; // Define curret index to prevent errors
				
				// Check for ALREADY
				if ( @$uLects['hour'.$i] != NULL ) // If the user selected a lecture for hour $i
				{
					// Give already error
					$pickup[$i] = $Ctemplate->useStaticTemplate("freeuni2/lecture_select_already", TRUE);
				}
				
				// Check for LIMIT
				// Query down users having the lecture picked up for hour $i
				$uCount = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hour" .$i. ") FROM users WHERE hour" .$i. "='" .$Cmysql->EscapeString($row['id']). "'"));
				
				if ( ( $uCount[0] >= $row['limit'.$i] ) && ( $row['limit'.$i] != 0 ) ) // If the user count is higher than the limit for hour $i, and the lecture has limit (so the limit isn't 0)
				{
					// Give limit error
					$pickup[$i] = $Ctemplate->useStaticTemplate("freeuni2/lecture_select_limit", TRUE);
				}
				
				// Check for NO LECTURE
				if ( $row['hour'.$i] == "no" ) // If the lecture does not take place in hour $i
				{
					// Give no lecture error
					$pickup[$i] = $Ctemplate->useStaticTemplate("freeuni2/lecture_select_nolecture", TRUE);
				}
				
				// Check for FORM
				// $pickup[$i] is NULL if the previous ifs haven't modified it
				if ( $pickup[$i] == NULL )
				{
					// Give form
					$pickup[$i] = $Ctemplate->useTemplate("freeuni2/lecture_select_form", array(
						'HOUR'	=>	$i, // Hour number
						'LECTURE'	=>	$row['id'] // Lecture ID
					), TRUE);
				}
			}	
			
			// Outputting row for every lecture
			$Ctemplate->useTemplate("freeuni2/lecture_select_table_row", array(
				'LECTURE_NAME'	=>	$row['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$row['lecturer'], // Lecturer
				'HOUR1'	=>	$pickup[1],
				'HOUR2'	=>	$pickup[2],
				'HOUR3'	=>	$pickup[3],
				'HOUR4'	=>	$pickup[4]
			), FALSE);
		}
		
		$Ctemplate->useStaticTemplate("freeuni2/lecture_select_table_close", FALSE); // Closing table
	}
}

$Ctemplate->useStaticTemplate("freeuni2/lecture_select_foot", FALSE); // Footer
DoFooter();
?>