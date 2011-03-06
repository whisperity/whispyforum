<?php
 /**
 * WhispyForum script file - freeuni2_lecture_add.php
 * 
 * Use to add new lectures to the database
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni2/lecture_add_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 1 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadásokra jelentkezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// We define the $site variable
$site = "";

// Get user's level
$uDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data

if ( $uDBArray['userLevel'] < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"Hozzáférési szint hiba", // Error title
		'BODY'	=>	"A lap megtekintéséhez Adminisztrátori, vagy magasabb jogkörrel kell rendelkezned", // Error text
		'ALT'	=>	"Házirendhiba" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $uDBArray['userLevel'] >= 3 )
{
// If user has the rights, the panel is accessible

if (!isset($_POST['addPos']))
{
	$addPos = 0;
} else {
	$addPos = $_POST['addPos'];
}

// Now, the regPos variable is either 0 or set from HTTP POST

switch ($addPos)
{
	case NULL:
	case 0:
		// Performer's information
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("freeuni2/lecture_add_data", array(
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1_YES'	=>	($_POST['hour1'] == "yes" ? " checked" : NULL), // Hour #1 YES
				'HOUR1_NO'	=>	($_POST['hour1'] == "no" ? " checked" : NULL), // Hour #1 NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2_YES'	=>	($_POST['hour2'] == "yes" ? " checked" : NULL), // Hour #2 YES
				'HOUR2_NO'	=>	($_POST['hour2'] == "no" ? " checked" : NULL), // Hour #2 NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3_YES'	=>	($_POST['hour3'] == "yes" ? " checked" : NULL), // Hour #3 YES
				'HOUR3_NO'	=>	($_POST['hour3'] == "no" ? " checked" : NULL), // Hour #3 NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4_YES'	=>	($_POST['hour4'] == "yes" ? " checked" : NULL), // Hour #4 YES
				'HOUR4_NO'	=>	($_POST['hour4'] == "no" ? " checked" : NULL), // Hour #4 NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("freeuni2/lecture_add_data", array(
				'LECTURE_NAME'	=>	"", // Name of the lecture
				'LECTURER'	=>	"", // Name of the lecturer
				'HOUR1_YES'	=>	"", // Hour #1 YES
				'HOUR1_NO'	=>	"", // Hour #1 NO
				'LIMIT1'	=>	"0", // Hour #1 limit
				'HOUR2_YES'	=>	"", // Hour #2 YES
				'HOUR2_NO'	=>	"", // Hour #2 NO
				'LIMIT2'	=>	"0", // Hour #2 limit
				'HOUR3_YES'	=>	"", // Hour #3 YES
				'HOUR3_NO'	=>	"", // Hour #3 NO
				'LIMIT3'	=>	"0", // Hour #3 limit
				'HOUR4_YES'	=>	"", // Hour #4 YES
				'HOUR4_NO'	=>	"", // Hour #4 NO
				'LIMIT4'	=>	"0" // Hour #4 limit
			), FALSE); // Lecture information
		}
		break;
	case 1:
		// Lecture adding
		
		// First, we do a check whether every required fields have data
		if ( $_POST['lecture_name'] == NULL ) // Name of the lecture
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Előadás címe", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture (should be empty)
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['lecturer'] == NULL ) // Name of the lecturer
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Előadó neve", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer (should be empty)
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour1']) ) // Hour #1 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 1. órában", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO (should be empty)
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour2']) ) // Hour #2 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 2. órában", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO (should be empty)
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour3']) ) // Hour #3 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 3. órában", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO (should be empty)
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour4']) ) // Hour #4 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_add_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 4. órában", // Errorneous variable
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO (should be empty)
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Now, all variables are entered
		
		
		// Everything is fine, we add the performer
		$addQuery = $Cmysql->Query("INSERT INTO fu2_lectures(lecture_name, lecturer, hour1, hour2, hour3, hour4, limit1, limit2, limit3, limit4) VALUES (
			'" .$Cmysql->EscapeString($_POST['lecture_name']). "',
			'" .$Cmysql->EscapeString($_POST['lecturer']). "',
			'" .$_POST['hour1']. "',
			'" .$_POST['hour2']. "',
			'" .$_POST['hour3']. "',
			'" .$_POST['hour4']. "',
			'" .$Cmysql->EscapeString($_POST['limit1']). "',
			'" .$Cmysql->EscapeString($_POST['limit2']). "',
			'" .$Cmysql->EscapeString($_POST['limit3']). "',
			'" .$Cmysql->EscapeString($_POST['limit4']). "')"); // Will be true if we succeed
		
		if ( $addQuery == FALSE )
		{
			// If there were errors during registration
			$Ctemplate->useTemplate("freeuni2/lecture_add_error", array(
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO (should be empty)
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO
				'LIMIT4'	=>	$_POST['limit4'] // Hour #4 limit
			), FALSE); // Give error message and retry form
		} elseif ( $addQuery == TRUE )
		{
			// If registration completed successfully
			$Ctemplate->useStaticTemplate("freeuni2/lecture_add_success", FALSE); // Give success
		}
		break;
}

}
$Ctemplate->useStaticTemplate("freeuni2/lecture_add_foot", FALSE); // Footer
DoFooter();
?>