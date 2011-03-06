<?php
 /**
 * WhispyForum script file - freeuni2_list_students.php
 * 
 * Listing lecture signup data
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni2/student_list_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 1 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadásokra jelentkezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni2/student_list_foot", FALSE); // Footer
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

if ( !isset($_POST['osztaly']) )
{
	// If we didn't get the OSZTALY (school class) variable
	// Generate list from classes
	
	$classesResult = $Cmysql->Query("SELECT COUNT(id), osztaly FROM users GROUP BY osztaly"); // Fetch classes and student count
	
	$Ctemplate->useTemplate("freeuni2/student_list_form_start", array(
		'SELECT_SIZE'	=>	mysql_num_rows($classesResult) // The SELECT list is sized for the number of unique classes
	), FALSE); // Begin <form>
	
	while ( $row = mysql_fetch_row($classesResult) )
	{
		// Going through every class, while generating a form drop-down box
		$Ctemplate->useTemplate("freeuni2/student_list_form_option", array(
			'SELECTED'	=>	" ", // Make the current selected class reselected (currently there isn't selected class, so it's a space)
			'OSZTALY'	=>	$row[1], // Name of the school class
			'COUNT'	=>	$row[0] // Student count
		), FALSE); // Output an option for select drop-down
	}
	
	$Ctemplate->useStaticTemplate("freeuni2/student_list_form_end", FALSE); // End </form>
} elseif ( isset($_POST['osztaly']) )
{
	// If we got the class variable
	// Make list
	
	// First, do the same select form
	
	$classesResult = $Cmysql->Query("SELECT COUNT(id), osztaly FROM users GROUP BY osztaly"); // Fetch classes and student count
	
	$Ctemplate->useStaticTemplate("freeuni2/student_list_form_start", FALSE); // Begin <form>
	
	while ( $row = mysql_fetch_row($classesResult) )
	{
		// Going through every class, while generating a form drop-down box
		$Ctemplate->useTemplate("freeuni2/student_list_form_option", array(
			'SELECTED'	=>	($_POST['osztaly'] == $row[1] ? " selected " : " "), // Make the current selected class reselected
			'OSZTALY'	=>	$row[1], // Name of the school class
			'COUNT'	=>	$row[0] // Student count
		), FALSE); // Output an option for select drop-down
	}
	
	$Ctemplate->useStaticTemplate("freeuni2/student_list_form_end", FALSE); // End </form>
	
	// Make list
	$studentList = $Cmysql->Query("SELECT id, username, hour1, hour2, hour3, hour4 FROM users WHERE osztaly='" .$Cmysql->EscapeString($_POST['osztaly']). "' ORDER BY username ASC"); // Query students
	
	$Ctemplate->useStaticTemplate("freeuni2/student_list_table_open", FALSE); // Opening a table
	
	while ( $sRow = mysql_fetch_assoc($studentList) )
	{
		// Output one table row for each student
		
		$hours = array(); // Define an array for each hour
		
		for ($i = 1; $i <= 4; $i++)
		{
			// Go through each hour value
			
			if ( $sRow['hour'.$i] == NULL )
			{
				// If the student didn't select a lecture
				$hours[$i] = $Ctemplate->useStaticTemplate("freeuni2/student_list_table_row_nopick", TRUE); // No pick icon (returned as a variable)
			} else {
				// If the student did
				
				// Query the lecture's name
				$lectName = mysql_fetch_row($Cmysql->Query("SELECT lecture_name FROM fu2_lectures WHERE id='" .$sRow['hour'.$i]. "'"));
				
				// Store it in the hours array
				$hours[$i] = $lectName[0];
			}
		}
		
		// The hours array contains the name of lectures
		// selected for each hour
		
		$Ctemplate->useTemplate("freeuni2/student_list_table_row", array(
			'ID'	=>	$sRow['id'], // User ID
			'USERNAME'	=>	$sRow['username'], // Name
			'HOUR1'	=>	$hours[1], // Lecture for first hour
			'HOUR2'	=>	$hours[2], // Lecture for second hour
			'HOUR3'	=>	$hours[3], // Lecture for third hour
			'HOUR4'	=>	$hours[4] // Lecture for fourth hour
		), FALSE);
	}
	
	$Ctemplate->useStaticTemplate("freeuni2/student_list_table_close", FALSE); // Closing the table
}

}
$Ctemplate->useStaticTemplate("freeuni2/student_list_foot", FALSE); // Footer
DoFooter();
?>