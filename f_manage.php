<?php
 /**
 * WhispyForum script file - f_manage.php
 * 
 * Freeuniversity Organizer script
 *  - Adding lectures
 *  - Listing lectures
 *  - Editing and deleting lectures
 *  - Generating register of attendees to sign
 *  - List student attendance based on f_class
 * 
 * WhispyForum
 */

if ( ( @$_GET['action'] === "register" ) && ( @$_GET['id'] != NULL ) && ( @$_GET['hour'] != NULL ) )
{
	// If we requested to obtain a register of students
	// attending a lecture to sign on the spot,
	// we load the "safeload" environment
	// to prevent framework generation.
	include("includes/safeload.php"); // Load the environment but not the framework
	
	$Ctemplate->useStaticTemplate("freeuniversity/print_head", FALSE); // Output the header for printing

} else {
	// Requests on anything else is done with the framework loaded
	include("includes/load.php"); // Load webpage
	dieOnModule("freeuniversity"); // We use dieOnModule only here, because the safeload does not have dieOnModule
	
	$Ctemplate->useStaticTemplate("freeuniversity/manage_head", FALSE); // Output standard header
}

$uLvl = $Cusers->getLevel(); // Get user level from database

if ( $uLvl < 2 )
{
	// If the user has lesser rights than a Moderator, disallow access.
	$Ctemplate->useTemplate("errormessage", array(
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
		'BODY'	=>	"{LANG_REQUIRES_MODERATOR}", // Error text
		'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
	), FALSE ); // Give rights error
} elseif ( $uLvl >= 2 ) {
	if ( @$_GET['action'] === "register" )
	{
		// If we were requested to generate a register of students attending the lecture
		
		// Check whether we passed the required variables
		if ( ( @$_GET['id'] === NULL ) || ( @$_GET['hour'] === NULL ) )
		{
			// If somehow there wasn't a lecture ID and an attendance hour
			// variables passed with the call, we terminate execution.
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'  =>      "Nuvola_apps_error.png",
				'TITLE' =>      "{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}",
				'BODY'  =>      "{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING_BODY}",
				'ALT'   =>      "{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}"
			), FALSE); // Output an error message
		} elseif ( ( @$_GET['id'] != NULL ) && ( !in_array(@$_GET['hour'], array(1, 2, 3, 4, 12, 23, 34)) ) )
		{
			// Another possible mishap: ID is present but the hour variable points to an invalid hour
			$Ctemplate->useTemplate("errormessage", array(
				'PICTURE_NAME'  =>      "Nuvola_filesystems_folder_locked.png",
				'TITLE' =>      "{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
				'BODY'  =>      "{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
				'ALT'   =>      "{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
			), FALSE); // Output an error message
		} elseif ( ( @$_GET['id'] != NULL ) && ( in_array(@$_GET['hour'], array(1, 2, 3, 4, 12, 23, 34)) ) )
		{
			// Query the lecture's title from the database
			$lRow = mysql_fetch_row($Cmysql->Query("SELECT title FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_GET['id']). "'"));
			
			if ( $lRow === FALSE )
			{
				// If there is lecture ID is invalid, we terminate execution with an error message
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_INVALID}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_INVALID_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_INVALID}"
				), FALSE);
				
				$Ctemplate->useStaticTemplate("freeuniversity/print_foot", FALSE);
				DoFooter();
				exit;
			}
			
			// We, based on the hour variable, query the list of students from the database
			switch ( $_GET['hour'] )
			{
				case 1:
				case 2:
				case 3:
				case 4:
					// If we requested a single-hour query, we query the users
					$students = $Cmysql->Query("SELECT username, f_class FROM users WHERE f_hour" .$Cmysql->EscapeString($_GET['hour']). "='" .$Cmysql->EscapeString($_GET['id']). "' ORDER BY username ASC");
					
					break;
				case 12:
				case 23:
				case 34:
					// If we are querying a double-hour lecture,
					// we only query users for whom both f_hourX colums are the same
					// (Which, by normal operation, everyone attending the double-hour)
					
					// Split the doublehour's value
					if ( $_GET['hour'] === "12" )
					{
						$hour1 = 1;
						$hour2 = 2;
					} elseif ( $_GET['hour'] === "23" )
					{
						$hour1 = 2;
						$hour2 = 3;
					} elseif ( $_GET['hour'] === "34" )
					{
						$hour1 = 3;
						$hour2 = 4;
					}

					// Get the list of students
					$students = $Cmysql->Query("SELECT username, f_class FROM users WHERE f_hour" .$hour1. "='" .$Cmysql->EscapeString($_GET['id']). "' AND f_hour" .$hour2. "='" .$Cmysql->EscapeString($_GET['id']) ."' ORDER BY username ASC");
					
					break;
			}
			
			// Set the $hour to the current value
			$hour = $_GET['hour'];
			
			// Generate the string hour value for the template to use
			$lblHour = $wf_lang['{LANG_FREEUNIVERSITY_HOUR_' .$hour. '}'];
			
			
			// Open the attendance register table
			$Ctemplate->useTemplate("freeuniversity/print_table_open", array(
				'TITLE'	=>	$lRow[0],
				'HOUR'	=>	$lblHour
			), FALSE);
			
			while ( $sRow = mysql_fetch_assoc($students) )
			{
				// We go through the students result one-by-one and output a row
				$Ctemplate->useTemplate("freeuniversity/print_table_row", array(
					'USERNAME'	=>	$sRow['username'],
					'CLASS'	=>	$sRow['f_class']
				), FALSE);
			}
			
			// Close the attendance table
			$Ctemplate->useStaticTemplate("freeuniversity/print_table_close", FALSE);
		}
		
		// Terminate execution
		$Ctemplate->useStaticTemplate("freeuniversity/print_foot", FALSE); // Footer
		DoFooter();
		exit;
	}

	if ( @$_GET['f_class'] != NULL )
	{
		// If we have an f_class value passed trough,
		// it means we requested an f_class-based group roster.
		
		// Output header
		$Ctemplate->useTemplate("freeuniversity/roster_head", array(
			'CLASS'	=>	$_GET['f_class']
		), FALSE);
		
		// Get the list of students from the database
		if ( $_GET['f_class'] === "all" )
		{
			// If we requested all (totally all) student, we query all and order them into classes and then alphabetically
			$students = $Cmysql->Query("SELECT username, userLevel, f_class, activated, f_hour1, f_hour2, f_hour3, f_hour4 FROM users ORDER BY f_class ASC, username ASC");
		} else {
			// If we requested only a single f_class group, we query students from there
			$students = $Cmysql->Query("SELECT username, userLevel, activated, f_hour1, f_hour2, f_hour3, f_hour4 FROM users WHERE f_class='" .$Cmysql->EscapeString($_GET['f_class']). "' ORDER BY username ASC");
		}
		
		while ( $studRow = mysql_fetch_assoc($students) )
		{
			// Going trough every student row, output a row to the table
			
			// Get the data of lectures for the 4 lectures the student is attending
			for ( $i = 1; $i <= 4; $i++ )
			{
				$lRow[$i] = mysql_fetch_assoc($Cmysql->Query("SELECT title, lect1_2, lect2_3, lect3_4 FROM f_lectures WHERE id='" .$studRow['f_hour' .$i]. "'"));
			}
			
			// Define an empty container for every hour-cell of the current row
			$hRow = array();
			
			// Perform check on the first hour
			if ( $lRow[1] !== FALSE )
			{
				// If the student is attending a lecture in the first hour, we check for double-hours
			
				if ( $lRow[1]['lect1_2'] === "1" )
				{
					// If the lecture is a double-hour one, prepare a double-hour row
					$hRow[1] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	40, // Width of cell (2x20 because double-hour)
						'COLSPAN'	=>	2, // Column spanning (2 because double-hour)
						'LECTURE_TITLE'	=>	$lRow[1]['title'] // Title of the lecture
					), TRUE);
				} elseif ( $lRow[1]['lect1_2'] === "0" )
				{
					// If the lecture is a single-hour one, prepare single-hour row
					$hRow[1] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	20, // Width of cell (1x20 because single-hour)
						'COLSPAN'	=>	1, // Column spanning (1 because single-hour)
						'LECTURE_TITLE'	=>	$lRow[1]['title'] // Title of the lecture
					), TRUE);
				}
			} elseif ( $lRow[1] === FALSE )
			{
				// If the user is not attending a lecture in the first hour, we fill the embed container with empty row
				$hRow[1] = $Ctemplate->useStaticTemplate("freeuniversity/roster_row_empty", TRUE);
			}
			
			// Perform the same check on the rest of the hours
			if ( $lRow[2] !== FALSE )
			{
				if ( ( $lRow[2]['lect1_2'] === "1" ) && ( $lRow[2]['lect2_3'] === "0" ) )
				{
					// If the lecture is a double-hour one, fill in empty row because
					// the row was already filled when checking the previous one
					$hRow[2] = NULL;
				} elseif ( ( $lRow[2]['lect1_2'] === "0" ) && ( $lRow[2]['lect2_3'] === "1" ) )
				{
					$hRow[2] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	40,
						'COLSPAN'	=>	2,
						'LECTURE_TITLE'	=>	$lRow[2]['title']
					), TRUE);
				} elseif ( ( $lRow[2]['lect1_2'] === "0" ) && ( $lRow[2]['lect2_3'] === "0" ) )
				{
					$hRow[2] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	20,
						'COLSPAN'	=>	1,
						'LECTURE_TITLE'	=>	$lRow[2]['title']
					), TRUE);
				}
			} elseif ( $lRow[2] === FALSE )
			{
				$hRow[2] = $Ctemplate->useStaticTemplate("freeuniversity/roster_row_empty", TRUE);
			}
			
			if ( $lRow[3] !== FALSE )
			{
				if ( ( $lRow[3]['lect2_3'] === "1" ) && ( $lRow[3]['lect3_4'] === "0" ) )
				{
					$hRow[3] = NULL;
				} elseif ( ( $lRow[3]['lect2_3'] === "0" ) && ( $lRow[3]['lect3_4'] === "1" ) )
				{
					$hRow[3] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	40,
						'COLSPAN'	=>	2,
						'LECTURE_TITLE'	=>	$lRow[3]['title']
					), TRUE);
				} elseif ( ( $lRow[3]['lect2_3'] === "0" ) && ( $lRow[3]['lect3_4'] === "0" ) )
				{
					$hRow[3] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	20,
						'COLSPAN'	=>	1,
						'LECTURE_TITLE'	=>	$lRow[3]['title']
					), TRUE);
				}
			} elseif ( $lRow[3] === FALSE )
			{
				$hRow[3] = $Ctemplate->useStaticTemplate("freeuniversity/roster_row_empty", TRUE);
			}
			
			if ( $lRow[4] !== FALSE )
			{
				if ( $lRow[4]['lect3_4'] === "1" )
				{
					$hRow[4] = NULL;
				} elseif ( $lRow[4]['lect3_4'] === "0" )
				{
					$hRow[4] = $Ctemplate->useTemplate("freeuniversity/roster_row_hour", array(
						'WIDTH'	=>	10,
						'COLSPAN'	=>	1,
						'LECTURE_TITLE'	=>	$lRow[4]['title']
					), TRUE);
				}
			} elseif ( $lRow[4] === FALSE )
			{
				$hRow[4] = $Ctemplate->useStaticTemplate("freeuniversity/roster_row_empty", TRUE);
			}
			
			// Prefix the username with rank if rank is present
			switch ($studRow['userLevel'])
			{
				case "2":
					$studRow['username'] = '<sup>(' .$wf_lang['{LANG_MODERATOR}']. ')</sup>&nbsp;' .$studRow['username'];
					break;
				case "3":
					$studRow['username'] = '<sup>(' .$wf_lang['{LANG_ADMINISTRATOR}']. ')</sup>&nbsp;' .$studRow['username'];
					break;
				case "4":
					$studRow['username'] = '<sup>(' .$wf_lang['{LANG_ROOT}']. ')</sup>&nbsp;' .$studRow['username'];
					break;
			}
			
			if ( $studRow['activated'] === "0" )
			{
				// If the user is not activated, we prefix the username with a notice
				$studRow['username'] = '<sub>(' .$wf_lang['{LANG_NOT_ACTIVATED}']. ')</sub>' .$studRow['username'];
			}
			
			if ( $_GET['f_class'] === "all" )
			{
				// If we requested every single student from the database, we append the value
				// of f_class to the end of the username
				$studRow['username'] .= "&nbsp;<sup>(" .$studRow['f_class']. ")</sup>";
			}
			
			$Ctemplate->useTemplate("freeuniversity/roster_row", array(
				'USERNAME'	=>	$studRow['username'], // Username of the student
				'HOUR1'	=>	$hRow[1],
				'HOUR2'	=>	$hRow[2],
				'HOUR3'	=>	$hRow[3],
				'HOUR4'	=>	$hRow[4]
			), FALSE); // Output a row for the current student, embedding the hour cells too
		}
		
		// Terminate execution
		$Ctemplate->useStaticTemplate("freeuniversity/roster_foot", FALSE);
		DoFooter();
		exit;
	}
	
	switch ( @$_POST['action'] )
	{
		// We make a switch based on what action the user requires
		case "newlecture":
			// --- Adding new lecture ---
			
			// Check whether the user is able to add the new lecture
			if ( $uLvl < 3 )
			{
				// Only Administrators or higher are able to add lectures
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
					'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
					'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
					'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
				), FALSE ); // Give rights error
			} elseif ( $uLvl >= 3) {
				// If the user is able, let him/her add the lecture
				
				switch ( @$_POST['step'] )
				{
					// The lecture adding method is chunked up into some steps
					case "lecture-data":
					default:
						// This step is when the user is requested to add lecture data
						
						if ( @$_POST['error_goback'] == "yes" )
						{
							// If this request is an error return, fill the form from the
							// already entered data.
							
							$Ctemplate->useTemplate("freeuniversity/new_form", array(
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1_CHECKED'	=>	( @$_POST['hour1'] === "1" ? " checked" : NULL ),
								'HOUR2_CHECKED'	=>	( @$_POST['hour2'] === "1" ? " checked" : NULL ),
								'HOUR3_CHECKED'	=>	( @$_POST['hour3'] === "1" ? " checked" : NULL ),
								'HOUR4_CHECKED'	=>	( @$_POST['hour4'] === "1" ? " checked" : NULL ),
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12_CHECKED'	=>	( @$_POST['hour12'] === "1" ? " checked" : NULL ),
								'HOUR23_CHECKED'	=>	( @$_POST['hour23'] === "1" ? " checked" : NULL ),
								'HOUR34_CHECKED'	=>	( @$_POST['hour34'] === "1" ? " checked" : NULL ),
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
						} else {
							// Request is a plain one, we create a general form
							
							$Ctemplate->useTemplate("freeuniversity/new_form", array(
								'TITLE'	=>	NULL,
								'DESCRIPTION'	=>	NULL,
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1_CHECKED'	=>	NULL,
								'HOUR2_CHECKED'	=>	NULL,
								'HOUR3_CHECKED'	=>	NULL,
								'HOUR4_CHECKED'	=>	NULL,
								
								// Lecture limits (capita)
								'LIMIT1'	=>	0,
								'LIMIT2'	=>	0,
								'LIMIT3'	=>	0,
								'LIMIT4'	=>	0,
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12_CHECKED'	=>	NULL,
								'HOUR23_CHECKED'	=>	NULL,
								'HOUR34_CHECKED'	=>	NULL,
								
								// Lecture limits (capita)
								'LIMIT12'	=>	0,
								'LIMIT23'	=>	0,
								'LIMIT34'	=>	0
							), FALSE);
						}
						
						break;
					case "store-lecture":
						// If the administrator entered everything to the form
						// in the previous step, we parse and if possible, store the lecture.
						
						// If the title is empty, we suspend execution
						if ( @$_POST['title'] == NULL )
						{
							// Output an error message prompting the user to
							// enter the TITLE of the lecture
							$Ctemplate->useTemplate("freeuniversity/new_variable_error", array(
								'VARIABLE'	=>	"{LANG_FREEUNIVERSITY_LECTURE_TITLE}", // Missing variable's name
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						// Check whether we wanted to add the lecture with
						// double-hours in 1-2 and 2-3
						// or in 2-3 and 3-4.
						// Don't let the user meddle with the timeline.
						if (
							( ( @$_POST['hour12'] == "1" ) && ( @$_POST['hour23'] == "1" ) )
							||
							( ( @$_POST['hour23'] == "1" ) && ( @$_POST['hour34'] == "1" ) )
						)
						{
							// Output an error message prompting the user to fix the imput
							$Ctemplate->useTemplate("freeuniversity/new_multiple_doublehour_error", array(
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						// If the limits are lower than zero, we error
						if ( ( @$_POST['limit1'] < 0 ) || ( @$_POST['limit2'] < 0 ) || ( @$_POST['limit3'] < 0 ) || ( @$_POST['limit4'] < 0 ) || ( @$_POST['limit12'] < 0 ) || ( @$_POST['limit23'] < 0 ) || ( @$_POST['limit34'] < 0 ) )
						{
							$Ctemplate->useTemplate("freeuniversity/new_limit_error", array(
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						// Because double-hour setups are overwriting single-hour settings,
						// we will populate an array one by one with the final data.
						$dLecture = array(
							'title'	=>	$_POST['title'],
							'description'	=>	@$_POST['description'],
							
							/* Single-hour lecture setup */
							'hour1'	=>	( @$_POST['hour1'] === "1" ? TRUE : FALSE ),
							'hour2'	=>	( @$_POST['hour2'] === "1" ? TRUE : FALSE ),
							'hour3'	=>	( @$_POST['hour3'] === "1" ? TRUE : FALSE ),
							'hour4'	=>	( @$_POST['hour4'] === "1" ? TRUE : FALSE ),
							
							'limit1'	=>	( ( @$_POST['limit1'] >= 0 ) && ( @$_POST['hour1'] === "1" ) ? @$_POST['limit1'] : 0 ),
							'limit2'	=>	( ( @$_POST['limit2'] >= 0 ) && ( @$_POST['hour2'] === "1" ) ? @$_POST['limit2'] : 0 ),
							'limit3'	=>	( ( @$_POST['limit3'] >= 0 ) && ( @$_POST['hour3'] === "1" ) ? @$_POST['limit3'] : 0 ),
							'limit4'	=>	( ( @$_POST['limit4'] >= 0 ) && ( @$_POST['hour4'] === "1" ) ? @$_POST['limit4'] : 0 ),
							
							/* Double-hour setup (off by default) */
							'hour12'	=>	FALSE,
							'hour23'	=>	FALSE,
							'hour34'	=>	FALSE
						);
						
						// After the single-hour setup has been stored into $dLecture,
						// move forward parsing the double-hour configuration.
						
						if ( @$_POST['hour12'] === "1" )
						{
							// If we decided to make #1 and #2 a double-hour, set the limit12 to the array,
							// and if the user decided to setup both single-hour #1 and #2 and double-hour
							// #1-#2, warn him/her about the override.
							
							// Set hour 1 and 2 to true,
							// and set limits of the double-hour to that value
							$dLecture['hour1'] = TRUE;
							$dLecture['hour2'] = TRUE;
							$dLecture['limit1'] = @$_POST['limit12'];
							$dLecture['limit2'] = @$_POST['limit12'];
							
							// Set 1-2 as double hour
							$dLecture['hour12'] = TRUE;
						}
						
						// Do the same for doublehour 2-3 and 3-4
						if ( @$_POST['hour23'] === "1" )
						{
							$dLecture['hour2'] = TRUE;
							$dLecture['hour3'] = TRUE;
							$dLecture['limit2'] = @$_POST['limit23'];
							$dLecture['limit3'] = @$_POST['limit23'];
							
							$dLecture['hour23'] = TRUE;
						}
						
						if ( @$_POST['hour34'] === "1" )
						{
							$dLecture['hour3'] = TRUE;
							$dLecture['hour4'] = TRUE;
							$dLecture['limit3'] = @$_POST['limit34'];
							$dLecture['limit4'] = @$_POST['limit34'];
							
							$dLecture['hour34'] = TRUE;
						}
						
						// Add $dLecture into the database
						if ( config("freeuniversity_allow") == "on" )
						{
							$lectAdd = $Cmysql->Query("INSERT INTO f_lectures(title, description, hour1, hour2, hour3, hour4, limit1, limit2, limit3, limit4, lect1_2, lect2_3, lect3_4) VALUES (
							'" .$Cmysql->EscapeString($dLecture['title']). "',
							'" .$Cmysql->EscapeString(str_replace("'", "\'", $dLecture['description'])). "',
							'" .$dLecture['hour1']. "',
							'" .$dLecture['hour2']. "',
							'" .$dLecture['hour3']. "',
							'" .$dLecture['hour4']. "',
							'" .$dLecture['limit1']. "',
							'" .$dLecture['limit2']. "',
							'" .$dLecture['limit3']. "',
							'" .$dLecture['limit4']. "',
							'" .$dLecture['hour12']. "',
							'" .$dLecture['hour23']. "',
							'" .$dLecture['hour34']. "')");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$lectAdd = FALSE;
						}
						
						if ( $lectAdd === FALSE )
						{
							// If the execution failed, notice the user
							// and offer chance to retry the execution.
							$Ctemplate->useTemplate("freeuniversity/new_error", array(
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
						} elseif ( $lectAdd === TRUE )
						{
							// If succeeded, notice the user
							$Ctemplate->useStaticTemplate("freeuniversity/new_success", FALSE);
						}
						
						break;
				}
			}
			
			break;
		case "edit":
			// --- Editing a lecture ---
			
			// Check whether the user is able to add the new lecture
			if ( $uLvl < 3 )
			{
				// Only Administrators or higher are able to edit lectures
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
					'TITLE'	=>	"{LANG_INSUFFICIENT_RIGHTS}", // Error title
					'BODY'	=>	"{LANG_REQUIRES_ADMIN}", // Error text
					'ALT'	=>	"{LANG_PERMISSIONS_ERROR}", // Alternate picture text
				), FALSE ); // Give rights error
			} elseif ( $uLvl >= 3) {
				// If the user is able, let him/her edit the lecture
				
				// Check whether an ID was passed through, if not, output error
				if ( @$_POST['id'] == NULL )
				{
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
						'TITLE'	=>	"{LANG_FREEUNIVERSITY_INVALID}",
						'BODY'	=>	"{LANG_FREEUNIVERSITY_INVALID_BODY}",
						'ALT'	=>	"{LANG_FREEUNIVERSITY_INVALID}"
					), FALSE);
					$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
					
					$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE);
					DoFooter();
					exit;
				}
				
				// Check whether the lecture we want to edit exists
				$lecture = mysql_fetch_assoc($Cmysql->Query("SELECT title, description, hour1, hour2, hour3, hour4, limit1, limit2, limit3, limit4, lect1_2, lect2_3, lect3_4 FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
				
				// Check whether the lecture we wanted to edit exists, if not, return with an error
				if ( $lecture === FALSE )
				{
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
						'TITLE'	=>	"{LANG_FREEUNIVERSITY_INVALID}",
						'BODY'	=>	"{LANG_FREEUNIVERSITY_INVALID_BODY}",
						'ALT'	=>	"{LANG_FREEUNIVERSITY_INVALID}"
					), FALSE);
					$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
					
					$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE);
					DoFooter();
					exit;
				}
				
				// Get the number of students attending this lecture in every hours
				$numStud[1] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour1='" .$Cmysql->EscapeString($_POST['id']). "'"));
				$numStud[2] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour2='" .$Cmysql->EscapeString($_POST['id']). "'"));
				$numStud[3] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour3='" .$Cmysql->EscapeString($_POST['id']). "'"));
				$numStud[4] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour4='" .$Cmysql->EscapeString($_POST['id']). "'"));
				
				// Define an empty container for temporary calculation data
				// This just prepares the data stored in the database into a proper,
				// user-readable and editable output for the edit template.
				$lData = array(
					'limits'	=>	array(
						1	=>	$lecture['limit1'],
						2	=>	$lecture['limit2'],
						3	=>	$lecture['limit3'],
						4	=>	$lecture['limit4'],
						12	=>	0,
						23	=>	0,
						34	=>	0
					),
					'held'	=>	array(
						1	=>	( $lecture['hour1'] === "1" ? TRUE : FALSE ),
						2	=>	( $lecture['hour2'] === "1" ? TRUE : FALSE ),
						3	=>	( $lecture['hour3'] === "1" ? TRUE : FALSE ),
						4	=>	( $lecture['hour4'] === "1" ? TRUE : FALSE ),
						12	=>	FALSE,
						23	=>	FALSE,
						34	=>	FALSE
					),
					'numstud'	=>	array(
						1	=>	$numStud[1][0],
						2	=>	$numStud[2][0],
						3	=>	$numStud[3][0],
						4	=>	$numStud[4][0],
						12	=>	0,
						23	=>	0,
						34	=>	0
					)
				);
				
				// Perform data morphing
				if ( $lecture['lect1_2'] == "1" )
				{
					// If the lecture is a double-hour one in the first one
					// we update the container properly for double hours
					
					$lData['limits'][1] = 0;
					$lData['limits'][2] = 0;
					$lData['limits'][12] = $lecture['limit1'];
					
					$lData['held'][1] = FALSE;
					$lData['held'][2] = FALSE;
					$lData['held'][12] = TRUE;
					
					$lData['numstud'][1] = 0;
					$lData['numstud'][2] = 0;
					$lData['numstud'][12] = $numStud[1][0];
				}
				
				if ( $lecture['lect2_3'] == "1" )
				{
					$lData['limits'][2] = 0;
					$lData['limits'][3] = 0;
					$lData['limits'][23] = $lecture['limit2'];
					
					$lData['held'][2] = FALSE;
					$lData['held'][3] = FALSE;
					$lData['held'][23] = TRUE;
					
					$lData['numstud'][2] = 0;
					$lData['numstud'][3] = 0;
					$lData['numstud'][23] = $numStud[2][0];
				}
				
				if ( $lecture['lect3_4'] == "1" )
				{
					$lData['limits'][3] = 0;
					$lData['limits'][4] = 0;
					$lData['limits'][34] = $lecture['limit3'];
					
					$lData['held'][3] = FALSE;
					$lData['held'][4] = FALSE;
					$lData['held'][34] = TRUE;
					
					$lData['numstud'][3] = 0;
					$lData['numstud'][4] = 0;
					$lData['numstud'][34] = $numStud[3][0];
				}
				
				switch ( @$_POST['step'] )
				{
					// The lecture editing method is chunked up into some steps
					case "lecture-data":
					default:
						// This step is when the user is requested to edit lecture data
						
						if ( @$_POST['error_goback'] == "yes" )
						{
							// If this request is an error return, fill the form from the
							// already entered data.
							
							$Ctemplate->useTemplate("freeuniversity/edit_form", array(
								'ID'	=>	@$_POST['id'],
								'TITLE'	=>	@$_POST['title'],
								'O_TITLE'	=>	$lecture['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1_CHECKED'	=>	( @$_POST['hour1'] === "1" ? " checked" : NULL ),
								'HOUR2_CHECKED'	=>	( @$_POST['hour2'] === "1" ? " checked" : NULL ),
								'HOUR3_CHECKED'	=>	( @$_POST['hour3'] === "1" ? " checked" : NULL ),
								'HOUR4_CHECKED'	=>	( @$_POST['hour4'] === "1" ? " checked" : NULL ),
								
								// Students currently attending the lectures
								'NUMSTUD_1'	=>	$lData['numstud'][1],
								'NUMSTUD_2'	=>	$lData['numstud'][2],
								'NUMSTUD_3'	=>	$lData['numstud'][3],
								'NUMSTUD_4'	=>	$lData['numstud'][4],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12_CHECKED'	=>	( @$_POST['hour12'] === "1" ? " checked" : NULL ),
								'HOUR23_CHECKED'	=>	( @$_POST['hour23'] === "1" ? " checked" : NULL ),
								'HOUR34_CHECKED'	=>	( @$_POST['hour34'] === "1" ? " checked" : NULL ),
								
								// Students currently attending the lectures
								'NUMSTUD_12'	=>	$lData['numstud'][12],
								'NUMSTUD_23'	=>	$lData['numstud'][23],
								'NUMSTUD_34'	=>	$lData['numstud'][34],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
						} else {
							// Request is a plain one, we create a general form with the current data
							
							$Ctemplate->useTemplate("freeuniversity/edit_form", array(
								'ID'	=>	@$_POST['id'],
								'TITLE'	=>	$lecture['title'],
								'O_TITLE'	=>	$lecture['title'],
								'DESCRIPTION'	=>	$lecture['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1_CHECKED'	=>	( $lData['held'][1] === TRUE ? " checked" : NULL ),
								'HOUR2_CHECKED'	=>	( $lData['held'][2] === TRUE ? " checked" : NULL ),
								'HOUR3_CHECKED'	=>	( $lData['held'][3] === TRUE ? " checked" : NULL ),
								'HOUR4_CHECKED'	=>	( $lData['held'][4] === TRUE ? " checked" : NULL ),
								
								// Students currently attending the lectures
								'NUMSTUD_1'	=>	$lData['numstud'][1],
								'NUMSTUD_2'	=>	$lData['numstud'][2],
								'NUMSTUD_3'	=>	$lData['numstud'][3],
								'NUMSTUD_4'	=>	$lData['numstud'][4],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	$lData['limits'][1],
								'LIMIT2'	=>	$lData['limits'][2],
								'LIMIT3'	=>	$lData['limits'][3],
								'LIMIT4'	=>	$lData['limits'][4],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12_CHECKED'	=>	( $lData['held'][12] === TRUE ? " checked" : NULL ),
								'HOUR23_CHECKED'	=>	( $lData['held'][23] === TRUE ? " checked" : NULL ),
								'HOUR34_CHECKED'	=>	( $lData['held'][34] === TRUE ? " checked" : NULL ),
								
								// Students currently attending the lectures
								'NUMSTUD_12'	=>	$lData['numstud'][12],
								'NUMSTUD_23'	=>	$lData['numstud'][23],
								'NUMSTUD_34'	=>	$lData['numstud'][34],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	$lData['limits'][12],
								'LIMIT23'	=>	$lData['limits'][23],
								'LIMIT34'	=>	$lData['limits'][34]
							), FALSE);
						}
						
						break;
					case "store-lecture":
						// If the administrator entered everything to the form
						// in the previous step, we parse and if possible, store the lecture.
						
						// If the title is empty, we suspend execution
						if ( @$_POST['title'] == NULL )
						{
							// Output an error message prompting the user to
							// enter the TITLE of the lecture
							$Ctemplate->useTemplate("freeuniversity/edit_variable_error", array(
								'VARIABLE'	=>	"{LANG_FREEUNIVERSITY_LECTURE_TITLE}", // Missing variable's name
								'ID'	=>	$_POST['id'],
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						// Check whether we wanted to add the lecture with
						// double-hours in 1-2 and 2-3
						// or in 2-3 and 3-4.
						// Don't let the user meddle with the timeline.
						if ( 
							( ( @$_POST['hour12'] == "1" ) && ( @$_POST['hour23'] == "1" ) )
							||
							( ( @$_POST['hour23'] == "1" ) && ( @$_POST['hour34'] == "1" ) )
						)
						{
							// Output an error message prompting the user to fix the imput
							$Ctemplate->useTemplate("freeuniversity/edit_multiple_doublehour_error", array(
								'ID'	=>	$_POST['id'],
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						if (
							( @$_POST['limit1'] < 0 ) ||
							( @$_POST['limit2'] < 0 ) ||
							( @$_POST['limit3'] < 0 ) ||
							( @$_POST['limit4'] < 0 ) ||
							( @$_POST['limit12'] < 0 ) ||
							( @$_POST['limit23'] < 0 ) ||
							( @$_POST['limit34'] < 0 ) ||
							
							( @$_POST['limit1'] < $lData['numstud'][1] ) ||
							( @$_POST['limit2'] < $lData['numstud'][2] ) ||
							( @$_POST['limit3'] < $lData['numstud'][3] ) ||
							( @$_POST['limit4'] < $lData['numstud'][4] ) ||
							( @$_POST['limit12'] < $lData['numstud'][12] ) ||
							( @$_POST['limit23'] < $lData['numstud'][23] ) ||
							( @$_POST['limit34'] < $lData['numstud'][34] )
						)
						{
							$Ctemplate->useTemplate("freeuniversity/edit_limit_error", array(
								'ID'	=>	$_POST['id'],
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
							
							// Terminate execution
							$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
							DoFooter();
							exit;
						}
						
						// Because double-hour setups are overwriting single-hour settings,
						// we will populate an array one by one with the final data.
						$dLecture = array(
							'id'	=>	$Cmysql->EscapeString($_POST['id']),
							'title'	=>	$_POST['title'],
							'description'	=>	@$_POST['description'],
							
							/* Single-hour lecture setup */
							'hour1'	=>	( @$_POST['hour1'] === "1" ? TRUE : FALSE ),
							'hour2'	=>	( @$_POST['hour2'] === "1" ? TRUE : FALSE ),
							'hour3'	=>	( @$_POST['hour3'] === "1" ? TRUE : FALSE ),
							'hour4'	=>	( @$_POST['hour4'] === "1" ? TRUE : FALSE ),
							
							'limit1'	=>	( ( @$_POST['limit1'] >= 0 ) && ( @$_POST['hour1'] === "1" ) ? @$_POST['limit1'] : 0 ),
							'limit2'	=>	( ( @$_POST['limit2'] >= 0 ) && ( @$_POST['hour2'] === "1" ) ? @$_POST['limit2'] : 0 ),
							'limit3'	=>	( ( @$_POST['limit3'] >= 0 ) && ( @$_POST['hour3'] === "1" ) ? @$_POST['limit3'] : 0 ),
							'limit4'	=>	( ( @$_POST['limit4'] >= 0 ) && ( @$_POST['hour4'] === "1" ) ? @$_POST['limit4'] : 0 ),
							
							/* Double-hour setup (off by default) */
							'hour12'	=>	FALSE,
							'hour23'	=>	FALSE,
							'hour34'	=>	FALSE
						);
						
						// After the single-hour setup has been stored into $dLecture,
						// move forward parsing the double-hour configuration.
						
						if ( @$_POST['hour12'] === "1" )
						{
							// If we decided to make #1 and #2 a double-hour, set the limit12 to the array,
							// and if the user decided to setup both single-hour #1 and #2 and double-hour
							// #1-#2, warn him/her about the override.
							
							// Set hour 1 and 2 to true,
							// and set limits of the double-hour to that value
							$dLecture['hour1'] = TRUE;
							$dLecture['hour2'] = TRUE;
							$dLecture['limit1'] = @$_POST['limit12'];
							$dLecture['limit2'] = @$_POST['limit12'];
							
							// Set 1-2 as double hour
							$dLecture['hour12'] = TRUE;
						}
						
						// Do the same for doublehour 2-3 and 3-4
						if ( @$_POST['hour23'] === "1" )
						{
							$dLecture['hour2'] = TRUE;
							$dLecture['hour3'] = TRUE;
							$dLecture['limit2'] = @$_POST['limit23'];
							$dLecture['limit3'] = @$_POST['limit23'];
							
							$dLecture['hour23'] = TRUE;
						}
						
						if ( @$_POST['hour34'] === "1" )
						{
							$dLecture['hour3'] = TRUE;
							$dLecture['hour4'] = TRUE;
							$dLecture['limit3'] = @$_POST['limit34'];
							$dLecture['limit4'] = @$_POST['limit34'];
							
							$dLecture['hour34'] = TRUE;
						}
						
						if ( ( $lData['held'][1] === TRUE ) && ( $dLecture['hour1'] === FALSE ) )
						{
							// If the lecture was previously held in the first hour, but now it is not
							// we release every student attending.
							
							$release[1] = $Cmysql->Query("UPDATE users SET f_hour1=0 WHERE f_hour1='" .$dLecture['id']. "'");
							
							if ( $release[1] === TRUE )
							{
								// If we successfully released, we output a small message
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_1}"
								), FALSE);
							}
						}
						
						// Do the same for the rest of the hours
						if ( ( $lData['held'][2] === TRUE ) && ( $dLecture['hour2'] === FALSE ) )
						{
							$release[2] = $Cmysql->Query("UPDATE users SET f_hour2=0 WHERE f_hour2='" .$dLecture['id']. "'");
							
							if ( $release[2] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_2}"
								), FALSE);
							}
						}
						
						if ( ( $lData['held'][3] === TRUE ) && ( $dLecture['hour3'] === FALSE ) )
						{
							$release[3] = $Cmysql->Query("UPDATE users SET f_hour3=0 WHERE f_hour3='" .$dLecture['id']. "'");
							
							if ( $release[3] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_3}"
								), FALSE);
							}
						}
						
						if ( ( $lData['held'][4] === TRUE ) && ( $dLecture['hour4'] === FALSE ) )
						{
							$release[4] = $Cmysql->Query("UPDATE users SET f_hour4=0 WHERE f_hour4='" .$dLecture['id']. "'");
							
							if ( $release[4] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_4}"
								), FALSE);
							}
						}
						
						if ( ( $lData['held'][12] === TRUE ) && ( $dLecture['hour12'] === FALSE ) )
						{
							$release[12] = $Cmysql->Query("UPDATE users SET f_hour1=0, f_hour2=0 WHERE f_hour1='" .$dLecture['id']. "' AND f_hour2='" .$dLecture['id']. "'");
							
							if ( $release[12] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_12}"
								), FALSE);
							}
						}
						
						if ( ( $lData['held'][23] === TRUE ) && ( $dLecture['hour23'] === FALSE ) )
						{
							$release[23] = $Cmysql->Query("UPDATE users SET f_hour2=0, f_hour3=0 WHERE f_hour2='" .$dLecture['id']. "' AND f_hour3='" .$dLecture['id']. "'");
							
							if ( $release[23] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_23}"
								), FALSE);
							}
						}
						
						if ( ( $lData['held'][34] === TRUE ) && ( $dLecture['hour34'] === FALSE ) )
						{
							$release[34] = $Cmysql->Query("UPDATE users SET f_hour3=0, f_hour4=0 WHERE f_hour3='" .$dLecture['id']. "' AND f_hour4='" .$dLecture['id']. "'");
							
							if ( $release[34] === TRUE )
							{
								$Ctemplate->useTemplate("freeuniversity/edit_released", array(
									'HOUR'	=>	"{LANG_FREEUNIVERSITY_HOUR_34}"
								), FALSE);
							}
						}
						
						// Update the database with the new $dLecture array
						if ( config("freeuniversity_allow") == "on" )
						{
							$lectEdit = $Cmysql->Query("UPDATE f_lectures SET 
							title='" .$Cmysql->EscapeString($dLecture['title']). "',
							description='" .$Cmysql->EscapeString(str_replace("'", "\'", $dLecture['description'])). "',
							hour1='" .$dLecture['hour1']. "',
							hour2='" .$dLecture['hour2']. "',
							hour3='" .$dLecture['hour3']. "',
							hour4='" .$dLecture['hour4']. "',
							limit1='" .$dLecture['limit1']. "',
							limit2='" .$dLecture['limit2']. "',
							limit3='" .$dLecture['limit3']. "',
							limit4='" .$dLecture['limit4']. "',
							lect1_2='" .$dLecture['hour12']. "',
							lect2_3='" .$dLecture['hour23']. "',
							lect3_4='" .$dLecture['hour34']. "' WHERE id='" .$Cmysql->EscapeString($dLecture['id']). "'");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$lectEdit = FALSE;
						}
						
						if ( $lectEdit === FALSE )
						{
							// If the execution failed, notice the user
							// and offer chance to retry the execution.
							$Ctemplate->useTemplate("freeuniversity/edit_error", array(
								'ID'	=>	$_POST['id'],
								'TITLE'	=>	@$_POST['title'],
								'DESCRIPTION'	=>	@$_POST['description'],
								
								/* Single-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR1'	=>	@$_POST['hour1'],
								'HOUR2'	=>	@$_POST['hour2'],
								'HOUR3'	=>	@$_POST['hour3'],
								'HOUR4'	=>	@$_POST['hour4'],
								
								// Lecture limits (capita)
								'LIMIT1'	=>	@$_POST['limit1'],
								'LIMIT2'	=>	@$_POST['limit2'],
								'LIMIT3'	=>	@$_POST['limit3'],
								'LIMIT4'	=>	@$_POST['limit4'],
								
								/* Double-hour lecture setup */
								// Defines whether lecture is held in said hour
								'HOUR12'	=>	@$_POST['hour12'],
								'HOUR23'	=>	@$_POST['hour23'],
								'HOUR34'	=>	@$_POST['hour34'],
								
								// Lecture limits (capita)
								'LIMIT12'	=>	@$_POST['limit12'],
								'LIMIT23'	=>	@$_POST['limit23'],
								'LIMIT34'	=>	@$_POST['limit34']
							), FALSE);
						} elseif ( $lectEdit === TRUE )
						{
							// If succeeded, notice the user
							$Ctemplate->useTemplate("freeuniversity/edit_success", array(
								'LECTURE_TITLE'	=>	$dLecture['title']
							), FALSE);
						}
						
						break;
				}
			}
			
			break;
		case "delete":
			// ----- Request to delete a lecture ----
			
			if ( @$_POST['id'] === NULL )
			{
				// If somehow there wasn't a lecture ID passed with the call, we terminate execution.
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}"
				), FALSE); // Output an error message
				$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
			} elseif ( @$_POST['id'] != NULL )
			{
				// The lecture ID is present
				
				// Get the lecture data from the database
				$lTitle = mysql_fetch_row($Cmysql->Query("SELECT title FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
				
				if ( $lTitle === FALSE )
				{
					// If there is lecture ID is invalid, we terminate execution with an error message
					$Ctemplate->useTemplate("errormessage", array(
						'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
						'TITLE'	=>	"{LANG_FREEUNIVERSITY_INVALID}",
						'BODY'	=>	"{LANG_FREEUNIVERSITY_INVALID_BODY}",
						'ALT'	=>	"{LANG_FREEUNIVERSITY_INVALID}"
					), FALSE);
					$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
					
					$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE);
					DoFooter();
					exit;
				}
				
				// We resign every user from the now to be deleted lecture to prevent database hiccups.
				for ( $i = 1; $i <= 4; $i++ )
				{
					// Going trough every single hour value (f_hour[$i], like: f_hour1, f_hour2, etc.)
					// we delete the user attendances.
					
					if ( config("freeuniversity_allow") == "on" )
					{
						$resign[$i] = $Cmysql->Query("UPDATE users SET f_hour" .$i. "=0 WHERE f_hour" .$i. "='" .$Cmysql->EscapeString($_POST['id']). "'");
					} elseif ( config("freeuniversity_allow") == "off" )
					{
						// Disallow non-read access to Freeuniversity database if it is finalized.
						$resign[$i] = FALSE;
					}
				}
				
				if ( ( $resign[1] === TRUE ) && ( $resign[2] === TRUE ) && ( $resign[3] === TRUE ) && ( $resign[4] === TRUE ) )
				{
					// If every student became unallocated, we remove the lecture itself
					if ( config("freeuniversity_allow") == "on" )
					{
						$lectRemove = $Cmysql->Query("DELETE FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'");
					} elseif ( config("freeuniversity_allow") == "off" )
					{
						// Disallow non-read access to Freeuniversity database if it is finalized.
						$lectRemove = FALSE;
					}
					
					if ( $lectRemove === TRUE )
					{
						// If we successfully removed the lecture, output success and return button
						$Ctemplate->useTemplate("successbox", array(
							'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
							'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_MANAGE_DELETE_SUCCESS}'],
							'BODY'	=>	"{LANG_FREEUNIVERSITY_MANAGE_DELETE_SUCCESS_1}",
							'ALT'	=>	"{LANG_FREEUNIVERSITY_MANAGE_DELETE_SUCCESS}",
							'LECTURE_TITLE'	=>	$lTitle[0]
						), FALSE);
						$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
					} elseif ( $lectRemove === FALSE )
					{
						// If we were unable to remove the lecture, output error and return button
						$Ctemplate->useTemplate("errormessage", array(
							'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
							'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_MANAGE_DELETE_ERROR}'],
							'BODY'	=>	"{LANG_FREEUNIVERSITY_MANAGE_DELETE_ERROR_1}",
							'ALT'	=>	"{LANG_FREEUNIVERSITY_MANAGE_DELETE_ERROR}",
							'LECTURE_TITLE'	=>	$lTitle[0]
						), FALSE);
						$Ctemplate->useStaticTemplate("freeuniversity/manage_retry_button", FALSE);
					}
				}
			}
			
			break;
		case "list":
		default:
			// Main action: listing, if nothing is requested
			// or "list" is requested
			
			// If the user is Administrator or higher, add option to add a lecture
			if ( $uLvl >= 3 )
			{
				$Ctemplate->useTemplate("freeuniversity/manage_newlecture", array(
					'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
				), FALSE);
			}
			
			// --- List f_class values and output panel to let the user query rosters ---
			// Get the list of distinct f_class values from database
			$f_classes = $Cmysql->Query("SELECT COUNT(f_class) AS numStud, f_class FROM users GROUP BY f_class ORDER BY f_class ASC");
			
			// This query will return a result of two colums, the first (numStud) having the count of
			// students in the said f_class group, the second is the value of the f_class of that row.
			
			// Create an empty container for later embed into the output form
			$fRows = NULL;
			while ( $classRow = mysql_fetch_assoc($f_classes) )
			{
				// While going through every single returned row,
				// we fill the container in with values
				$fRows .= $Ctemplate->useTemplate("freeuniversity/manage_roster_embed", array(
					'F_CLASS'	=>	$classRow['f_class'],
					'NUMSTUD'	=>	$classRow['numStud']
				), TRUE);
			}
			
			// Output the form
			$Ctemplate->useTemplate("freeuniversity/manage_roster_form", array(
				'OPTIONS'	=>	$fRows
			), FALSE); // Output the form with button to query
			
			// Open the list table of lectures
			$Ctemplate->useStaticTemplate("freeuniversity/manage_table_open", FALSE);
			
			// Get every lecture into a MySQL result
			$lectures_result = $Cmysql->Query("SELECT id, title, description, hour1, hour2, hour3, hour4, limit1, limit2, limit3, limit4, lect1_2, lect2_3, lect3_4 FROM f_lectures ORDER BY title ASC");
			
			while ( $lRow = mysql_fetch_assoc($lectures_result) )
			{
				// With going trough every single lecture, we make an output row
				
				// Get the students attending the said lecture in every hour
				$numStud1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour1='" .$lRow['id']. "'"));
				$numStud2 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour2='" .$lRow['id']. "'"));
				$numStud3 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour3='" .$lRow['id']. "'"));
				$numStud4 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour4='" .$lRow['id']. "'"));
				
				// Fetch the previously queried information into an array
				$numStud = array(
					1	=>	$numStud1[0],
					2	=>	$numStud2[0],
					3	=>	$numStud3[0],
					4	=>	$numStud4[0]
				);
				
				// Create an empty array for the hour <td> elements
				$hours = array();
				
				// Perform check on the first hour
				if ( $lRow['hour1'] === "1" )
				{
					// If the lecture is held in the first hour, check for double-hourness
					
					if ( $lRow['lect1_2'] === "1" )
					{
						// If the lecture is a double-hour one, prepare a double-hour row
						$hours[1] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	12, // # of hour
							'WIDTH'	=>	20, // Width of cell (2x10 because double-hour)
							'COLSPAN'	=>	2, // Column spanning (2 because double-hour)
							'STUDENT'	=>	$numStud[1], // Number of students attending the first of the double
							'LIMIT'	=>	($lRow['limit1'] === "0" ? NULL : "/".$lRow['limit1']) // Limit of students on first
						), TRUE);
					} elseif ( $lRow['lect1_2'] === "0" )
					{
						// If the lecture is a single-hour one, prepare single-hour row
						$hours[1] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	1, // # of hour
							'WIDTH'	=>	10, // Width of cell (1x10 because single-hour)
							'COLSPAN'	=>	1, // Column spanning (1 because single-hour)
							'STUDENT'	=>	$numStud[1], // Number of students attending
							'LIMIT'	=>	($lRow['limit1'] === "0" ? NULL : "/".$lRow['limit1']) // Limit of students
						), TRUE);
					}
				} elseif ( $lRow['hour1'] === "0" )
				{
					// If the lecture is not held in the first hour, prepare table
					$hours[1] = $Ctemplate->useStaticTemplate("freeuniversity/manage_table_row-empty", TRUE);
				}
				
				// Perform the same check on the rest of the hours
				if ( $lRow['hour2'] === "1" )
				{
					if ( ( $lRow['lect1_2'] === "1" ) && ( $lRow['lect2_3'] === "0" ) )
					{
						// If the lecture is a double-hour one, fill in empty row because
						// the row was already filled when checking the previous one
						$hours[2] = NULL;
					} elseif ( ( $lRow['lect1_2'] === "0" ) && ( $lRow['lect2_3'] === "1" ) )
					{
						$hours[2] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'],
							'HOUR'	=>	23,
							'WIDTH'	=>	20,
							'COLSPAN'	=>	2,
							'STUDENT'	=>	$numStud[2],
							'LIMIT'	=>	($lRow['limit2'] === "0" ? NULL : "/".$lRow['limit2'])
						), TRUE);
					} elseif ( ( $lRow['lect1_2'] === "0" ) && ( $lRow['lect2_3'] === "0" ) )
					{
						$hours[2] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'],
							'HOUR'	=>	2,
							'WIDTH'	=>	10,
							'COLSPAN'	=>	1,
							'STUDENT'	=>	$numStud[2],
							'LIMIT'	=>	($lRow['limit2'] === "0" ? NULL : "/".$lRow['limit2'])
						), TRUE);
					}
				} elseif ( $lRow['hour2'] === "0" )
				{
					$hours[2] = $Ctemplate->useStaticTemplate("freeuniversity/manage_table_row-empty", TRUE);
				}
				
				if ( $lRow['hour3'] === "1" )
				{
					if ( ( $lRow['lect2_3'] === "1" ) && ( $lRow['lect3_4'] === "0" ) )
					{
						$hours[3] = NULL;
					} elseif ( ( $lRow['lect2_3'] === "0" ) && ( $lRow['lect3_4'] === "1" ) )
					{
						$hours[3] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'],
							'HOUR'	=>	34,
							'WIDTH'	=>	20,
							'COLSPAN'	=>	2,
							'STUDENT'	=>	$numStud[3],
							'LIMIT'	=>	($lRow['limit3'] === "0" ? NULL : "/".$lRow['limit3'])
						), TRUE);
					} elseif ( ( $lRow['lect2_3'] === "0" ) && ( $lRow['lect3_4'] === "0" ) )
					{
						$hours[3] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'],
							'HOUR'	=>	3,
							'WIDTH'	=>	10,
							'COLSPAN'	=>	1,
							'STUDENT'	=>	$numStud[3],
							'LIMIT'	=>	($lRow['limit3'] === "0" ? NULL : "/".$lRow['limit3'])
						), TRUE);
					}
				} elseif ( $lRow['hour3'] === "0" )
				{
					$hours[3] = $Ctemplate->useStaticTemplate("freeuniversity/manage_table_row-empty", TRUE);
				}
				
				if ( $lRow['hour4'] === "1" )
				{
					if ( $lRow['lect3_4'] === "1" )
					{
						$hours[4] = NULL;
					} elseif ( $lRow['lect3_4'] === "0" )
					{
						$hours[4] = $Ctemplate->useTemplate("freeuniversity/manage_table_row-embed", array(
							'ID'	=>	$lRow['id'],
							'HOUR'	=>	4,
							'WIDTH'	=>	10,
							'COLSPAN'	=>	1,
							'STUDENT'	=>	$numStud[4],
							'LIMIT'	=>	($lRow['limit4'] === "0" ? NULL : "/".$lRow['limit4'])
						), TRUE);
					}
				} elseif ( $lRow['hour4'] === "0" )
				{
					$hours[4] = $Ctemplate->useStaticTemplate("freeuniversity/manage_table_row-empty", TRUE);
				}
				
				// All the stuff is prepared, so we output the row itself
				$Ctemplate->useTemplate("freeuniversity/manage_table_row", array(
					'TITLE'	=>	$lRow['title'],
					'DESCRIPTION'	=>	( $lRow['description'] === "" ? NULL :
						$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
							'DESCRIPTION'	=>	bbDecode($lRow['description'])
						), TRUE) ), // Description of the lecture (appears in hover box, only if present)
					'HOUR1'	=>	$hours[1],
					'HOUR2'	=>	$hours[2],
					'HOUR3'	=>	$hours[3],
					'HOUR4'	=>	$hours[4],
					'EDIT'	=>	( $uLvl >= 3 ? $Ctemplate->useTemplate("freeuniversity/manage_table_row_edit", array(
						'ID'	=>	$lRow['id'],
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE) : NULL ), // Edit button for Administrators and above
					'DELETE'	=>	( $uLvl >= 3 ? $Ctemplate->useTemplate("freeuniversity/manage_table_row_delete", array(
						'ID'	=>	$lRow['id'],
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE) : NULL ) // Delete button for Administrators and above
				), FALSE);
			}
			
			// Close the lecture list
			$Ctemplate->useStaticTemplate("freeuniversity/manage_table_close", FALSE);
			break;
	}
}

$Ctemplate->useStaticTemplate("freeuniversity/manage_foot", FALSE); // Footer
DoFooter();
?>
