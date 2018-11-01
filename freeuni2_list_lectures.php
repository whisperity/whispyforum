<?php
 /**
 * WhispyForum script file - freeuni2_list_lectures.php
 * 
 * Listing lecture data, editing and deleting lectures
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni2/lecture_list_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 2 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	2, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadásokra jelentkezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
	DoFooter();
	exit;
}

// We define the $site variable
$site = "";

// Get user's level
$uDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "' AND osztaly='" .$Cmysql->EscapeString($_SESSION['osztaly']). "'")); // We query the user's data

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
	
	if ( ( isset($_POST['delete']) ) && ( isset($_POST['lecture_id']) ) )
	{
		// If we decided to delete a lecture
		$deleteQuery = $Cmysql->Query("DELETE FROM fu2_lectures WHERE id='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"); // TRUE if we succeed
		
		if ( $deleteQuery == FALSE )
		{
			// If we failed deleting the lecture
			$Ctemplate->useTemplate("errormessage", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
				'TITLE'	=>	"Az előadás törlése nem sikerült.", // Error title
				'BODY'	=>	'<a href="freeuni2_list_lectures.php" alt="Előadások listázása és kezelése"><< Vissza a listához</a>', // Error text
				'ALT'	=>	"Lekérdezésfuttatási hiba" // Alternate picture text
			), FALSE ); // We give an error
		} elseif ( $deleteQuery == TRUE )
		{
			// If we succeeded
			
			// Remove every relation to the previously deleted lecture
			for ($i = 1; $i <= 4; $i++)
			{
				$Cmysql->Query("UPDATE users SET hour" .$i. "=NULL WHERE hour" .$i. "='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"); // This will set hour # to NULL
			}
			
			$Ctemplate->useTemplate("successbox", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
				'TITLE'	=>	"Az előadás törölve", // Success title
				'BODY'	=>	"Azon diákoktól, akiknek az előadás fel volt véve, a jelentkezésük törlődött.<br>".'<a href="freeuni2_list_lectures.php" alt="Előadások listázása és kezelése"><< Vissza a listához</a>', // Success text
				'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
			), FALSE ); // We give a success message
		}
	}
	
	if ( ( isset($_POST['edit']) ) && ( isset($_POST['lecture_id']) ) )
	{
		// If we decided to edit a lecture
		$lectData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM fu2_lectures WHERE id='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"));
		
		$students = array(); // Define the initial array
		for ($i = 1; $i <= 4; $i++)
		{
			if ( $lectData['hour'.$i] == "no" )
			{
				// If the lecture does not take place in the set hour
				$students[$i] = NULL;
			} elseif ( $lectData['hour'.$i] == "yes" )
			{
				// If it does
				
				// Count every students having the particular lecture
				// chosen for the particular hour
				$studCount = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hour" .$i. ") FROM users WHERE hour" .$i. "=" .$lectData['id']));
				
				// Set the student count and limit into the array in this format: 0/0
				$students[$i] = $studCount[0];
			}
		}
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			$Ctemplate->useTemplate("freeuni2/lecture_edit_data", array(
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
				'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
				'HOUR1_YES'	=>	($_POST['hour1'] == "yes" ? " checked" : NULL), // Hour #1 YES
				'HOUR1_NO'	=>	($_POST['hour1'] == "no" ? " checked" : NULL), // Hour #1 NO
				'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
				'STUDENTS1'	=>	$students[1], // Hour #1 student count
				'HOUR2_YES'	=>	($_POST['hour2'] == "yes" ? " checked" : NULL), // Hour #2 YES
				'HOUR2_NO'	=>	($_POST['hour2'] == "no" ? " checked" : NULL), // Hour #2 NO
				'STUDENTS2'	=>	$students[2], // Hour #2 student count
				'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
				'HOUR3_YES'	=>	($_POST['hour3'] == "yes" ? " checked" : NULL), // Hour #3 YES
				'HOUR3_NO'	=>	($_POST['hour3'] == "no" ? " checked" : NULL), // Hour #3 NO
				'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
				'STUDENTS3'	=>	$students[3], // Hour #3 student count
				'HOUR4_YES'	=>	($_POST['hour4'] == "yes" ? " checked" : NULL), // Hour #4 YES
				'HOUR4_NO'	=>	($_POST['hour4'] == "no" ? " checked" : NULL), // Hour #4 NO
				'LIMIT4'	=>	$_POST['limit4'], // Hour #4 limit
				'STUDENTS4'	=>	$students[4] // Hour #4 student count
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("freeuni2/lecture_edit_data", array(
				'LECTURE_ID'	=>	$lectData['id'], // ID of the lecture
				'LECTURE_NAME'	=>	$lectData['lecture_name'], // Name of the lecture
				'LECTURER'	=>	$lectData['lecturer'], // Name of the lecturer
				'HOUR1_YES'	=>	($lectData['hour1'] == "yes" ? " checked" : NULL), // Hour #1 YES
				'HOUR1_NO'	=>	($lectData['hour1'] == "no" ? " checked" : NULL), // Hour #1 NO
				'LIMIT1'	=>	$lectData['limit1'], // Hour #1 limit
				'STUDENTS1'	=>	$students[1], // Hour #1 student count
				'HOUR2_YES'	=>	($lectData['hour2'] == "yes" ? " checked" : NULL), // Hour #2 YES
				'HOUR2_NO'	=>	($lectData['hour2'] == "no" ? " checked" : NULL), // Hour #2 NO
				'LIMIT2'	=>	$lectData['limit2'], // Hour #2 limit
				'STUDENTS2'	=>	$students[2], // Hour #2 student count
				'HOUR3_YES'	=>	($lectData['hour3'] == "yes" ? " checked" : NULL), // Hour #3 YES
				'HOUR3_NO'	=>	($lectData['hour3'] == "no" ? " checked" : NULL), // Hour #3 NO
				'LIMIT3'	=>	$lectData['limit3'], // Hour #3 limit
				'STUDENTS3'	=>	$students[3], // Hour #3 student count
				'HOUR4_YES'	=>	($lectData['hour4'] == "yes" ? " checked" : NULL), // Hour #4 YES
				'HOUR4_NO'	=>	($lectData['hour4'] == "no" ? " checked" : NULL), // Hour #4 NO
				'LIMIT4'	=>	$lectData['limit4'], // Hour #4 limit
				'STUDENTS4'	=>	$students[4] // Hour #4 student count
			), FALSE); // Lecture information
		}
	}
	
	if ( ( isset($_POST['edit_do']) ) && ( isset($_POST['lecture_id']) ) )
	{
		// If we decided to edit a lecture (form is passed, SQL code)
		$lectData = mysql_fetch_assoc($Cmysql->Query("SELECT * FROM fu2_lectures WHERE id='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"));
		
		$students = array(); // Define the initial array
		for ($i = 1; $i <= 4; $i++)
		{
			if ( $lectData['hour'.$i] == "no" )
			{
				// If the lecture does not take place in the set hour
				$students[$i] = NULL;
			} elseif ( $lectData['hour'.$i] == "yes" )
			{
				// If it does
				
				// Count every students having the particular lecture
				// chosen for the particular hour
				$studCount = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hour" .$i. ") FROM users WHERE hour" .$i. "=" .$lectData['id']));
				
				// Set the student count and limit into the array in this format: 0/0
				$students[$i] = $studCount[0];
			}
		}
		
		// First, we do a check whether every required fields have data
		if ( $_POST['lecture_name'] == NULL ) // Name of the lecture
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Előadás címe", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( $_POST['lecturer'] == NULL ) // Name of the lecturer
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Előadó neve", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour1']) ) // Hour #1 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 1. órában", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour2']) ) // Hour #2 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 2. órában", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour3']) ) // Hour #3 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 3. órában", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( !isset($_POST['hour4']) ) // Hour #4 yes/no
		{
			$Ctemplate->useTemplate("freeuni2/lecture_edit_variable_error", array(
				'VARIABLE'	=>	"Lesz-e előadás az 4. órában", // Errorneous variable
				'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
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
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Now, all variables are entered
		
		// Check for limit issues
		// If any of the limits is lower than the current student count, 
		// (and the lecture takes place in that hour), give error
		for ($j = 1; $j <= 4; $j++)
		{
			// Go through every hours
			if ( ($_POST['limit'.$j] < $students[$j]) && ($_POST['hour'.$j] == "yes") )
			{
				$Ctemplate->useTemplate("freeuni2/lecture_edit_limit_error", array(
					'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
					'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
					'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
					'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
					'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
					'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
					'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
					'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
					'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
					'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO (should be empty)
					'LIMIT4'	=>	$_POST['limit4'], // Hour #4 limit
					
					// Limit reduction error
					'HOUR'	=>	$j, // Errorneous hour
					'LIMIT'	=>	$_POST['limit'.$j], // New limit
					'STUDENTS'	=>	$students[$j] // Current student count
				), FALSE);
				
				// Terminate the script
				$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
				DoFooter();
				exit;
			}
		}
		
		// It appears that the new fields
		// are matching every expectations.
		
		// Removing students relations from hours
		for ($l = 1; $l <= 4; $l++)
		{
			// Go through every hours
			
			// If we decided to remove a lecture
			// from an hour, we need to remove the
			// relating student's relation
			
			if ( $_POST['hour'.$l] == "no" )
			{
				$studRemove = $Cmysql->Query("UPDATE users SET hour" .$l. "=NULL WHERE hour" .$l. "='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"); // TRUE/FALSE if we succeed/fail
				
				if ( $studRemove == FALSE )
				{
					// If we failed deleting the lecture
					$Ctemplate->useTemplate("errormessage", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"Sikertelen leválasztás", // Error title
						'BODY'	=>	"Az előadás törölésre került a " .$l. ". órából." .
							" Azon diákok, akik az előadást a megadott órában felvették, nem lettek leválasztva.", // Error text
						'ALT'	=>	"Lekérdezésfuttatási hiba" // Alternate picture text
				), FALSE ); // We give an error
				} elseif ( $studRemove == TRUE )
				{
					$Ctemplate->useTemplate("successbox", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
						'TITLE'	=>	"Sikeres leválasztás", // Success title
						'BODY'	=>	"Az előadás törölésre került a " .$l. ". órából." .
							" Azon diákok, akik az előadást a megadott órában felvették, le lettek választva, az adott órára új előadást kell majd választaniuk.", // Success text
						'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
					), FALSE ); // We give a success message
				}
			}
		}
		
		// Upadting the lecture's database entry
		$updateQuery = $Cmysql->Query("UPDATE fu2_lectures SET 
			lecture_name='" .$Cmysql->EscapeString($_POST['lecture_name']). "',
			lecturer='" .$Cmysql->EscapeString($_POST['lecturer']). "',
			hour1='" .$_POST['hour1']. "',
			hour2='" .$_POST['hour2']. "',
			hour3='" .$_POST['hour3']. "',
			hour4='" .$_POST['hour4']. "',
			limit1='" .$Cmysql->EscapeString($_POST['limit1']). "',
			limit2='" .$Cmysql->EscapeString($_POST['limit2']). "',
			limit3='" .$Cmysql->EscapeString($_POST['limit3']). "',
			limit4='" .$Cmysql->EscapeString($_POST['limit4']). "'" .
			"WHERE id='" .$Cmysql->EscapeString($_POST['lecture_id']). "'"); // Will be true if we succeed
		
		if ( $updateQuery == FALSE )
		{
			// If we failed deleting the lecture
			$Ctemplate->useTemplate("freeuni2/lecture_edit_limit_error", array(
					'LECTURE_ID'	=>	$_POST['lecture_id'], // ID of the lecture
					'LECTURE_NAME'	=>	$_POST['lecture_name'], // Name of the lecture
					'LECTURER'	=>	$_POST['lecturer'], // Name of the lecturer
					'HOUR1'	=>	@$_POST['hour1'], // Hour #1 YES/NO
					'LIMIT1'	=>	$_POST['limit1'], // Hour #1 limit
					'HOUR2'	=>	@$_POST['hour2'], // Hour #2 YES/NO
					'LIMIT2'	=>	$_POST['limit2'], // Hour #2 limit
					'HOUR3'	=>	@$_POST['hour3'], // Hour #3 YES/NO
					'LIMIT3'	=>	$_POST['limit3'], // Hour #3 limit
					'HOUR4'	=>	@$_POST['hour4'], // Hour #4 YES/NO (should be empty)
					'LIMIT4'	=>	$_POST['limit4'], // Hour #4 limit
				), FALSE); // We give an error
		} elseif ( $updateQuery == TRUE )
		{
			// If we succeeded
			$Ctemplate->useStaticTemplate("freeuni2/lecture_edit_success", FALSE); // Give success message
		}
	}
	if ( !isset($_POST['lecture_id']) )
	{
		// If the lecture ID is missing, it means we're
		// getting normal query.
		
		// Generate lecture list
		$Ctemplate->useStaticTemplate("freeuni2/lecture_list_table_open", FALSE); // First, open a table
		
		// Query down all lectures (in alphabetical order)
		$lectureList = $Cmysql->Query("SELECT * FROM fu2_lectures ORDER BY lecture_name ASC");
		
		while ( $row = mysql_fetch_assoc($lectureList) )
		{
			// Go through every lecture
			
			// First, go through every hour and limit variables
			// to store student count and limit
			
			$hourlimit = array(); // Define the initial array
			for ($i = 1; $i <= 4; $i++)
			{
				if ( $row['hour'.$i] == "no" )
				{
					// If the lecture does not take place in the set hour
					$hourlimit[$i] = $Ctemplate->useStaticTemplate("freeuni2/lecture_select_nolecture", TRUE); // We set a red X icon as the value
				} elseif ( $row['hour'.$i] == "yes" )
				{
					// If it does
					
					// Count every students having the particular lecture
					// chosen for the particular hour
					$studCount = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hour" .$i. ") FROM users WHERE hour" .$i. "=" .$row['id']));
					
					// Set the student count and limit into the array in this format: 0/0
					$hourlimit[$i] = '<a href="freeuni2_list_relations.php?id=' .$row['id']. '&hour=' .$i. '" target="_blank">' .$studCount[0]."</a>/".$row['limit'.$i];
				}
			}
			
			// Output table row
			$Ctemplate->useTemplate("freeuni2/lecture_list_table_row", array(
				'LECTURE_ID'	=>	$row['id'],
				'LECTURE_NAME'	=>	$row['lecture_name'],
				'LECTURER'	=>	$row['lecturer'],
				'HOUR1'	=>	$hourlimit[1], // Previously set variable
				'HOUR2'	=>	$hourlimit[2], // Previously set variable
				'HOUR3'	=>	$hourlimit[3], // Previously set variable
				'HOUR4'	=>	$hourlimit[4] // Previously set variable
			), FALSE);
		}
		
		$Ctemplate->useStaticTemplate("freeuni2/lecture_list_table_close", FALSE); // Close the table
	}
}
$Ctemplate->useStaticTemplate("freeuni2/lecture_list_foot", FALSE); // Footer
DoFooter();
?>