<?php
 /**
 * WhispyForum script file - f_schedule.php
 * 
 * Freeuniversity Organizer script
 *  - List lectures (for students)
 *  - Attend lectures
 *  - Revoke attendance
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("freeuniversity"); // Die if FREEUNIVERSITY is disabled

$Ctemplate->useStaticTemplate("freeuniversity/schedule_head", FALSE); // Header

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
	// If user is logged in, the page is accessible
	
	switch ( @$_POST['action'] )
	{
		// We make a switch based on what action the user requires
		case "attend":
			// ----- Request to sign the user up for a lecture ----
			
			if ( ( @$_POST['id'] === NULL ) || ( @$_POST['hour'] === NULL ) )
			{
				// If somehow there wasn't a lecture ID and an attendance hour
				// variables passed with the call, we terminate execution.
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}"
				), FALSE); // Output an error message
				$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
			} elseif ( ( @$_POST['id'] != NULL ) && ( !in_array(@$_POST['hour'], array(1, 2, 3, 4, 12, 23, 34, 123, 234, 1234)) ) )
			{
				// Another possible mishap: ID is present but the hour variable points to an invalid hour
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
				), FALSE); // Output an error message
				$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
			} elseif ( ( @$_POST['id'] != NULL ) && ( in_array(@$_POST['hour'], array(1, 2, 3, 4, 12, 23, 34, 123, 234, 1234)) ) )
			{
				// The lecture ID is present and the hour variable points to a valid option
				// This means that we are allowed to attend the lecture, but before doing so
				// we perform the following initals checks on the attendance:
				//   - the lecture ID is valid and points to an existing lecture
				//   - the user is "free" (not attending another lecture) in said hour
				//   - the lecture is held in the said hour
				//   - there is at least one free spot (or limit is 0) for the lecture
				//
				// (Double-hour lectures means double check on both hours)
				
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
					$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
					
					$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
					DoFooter();
					exit;
				}
				
				switch ( $_POST['hour'] )
				{
					// We create an multiple brach of events
					// based on the hour variable the user requested
					case 1:
					case 2:
					case 3:
					case 4:
						// If we attempt to go on a single-hour setup, perform checks on said hour
						
						// Get the user's attendance lecture ID for hour #
						$uLectureID = mysql_fetch_row($Cmysql->Query("SELECT f_hour" .$Cmysql->EscapeString($_POST['hour']). " FROM users WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"));
						
						// Get whether the lecture is held in hour #
						$lectHeld = mysql_fetch_row($Cmysql->Query("SELECT hour" .$Cmysql->EscapeString($_POST['hour']). " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the lecture limit for said hour
						$limit = mysql_fetch_row($Cmysql->Query("SELECT limit" .$Cmysql->EscapeString($_POST['hour']). " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the number of students attending said lecture in said hour
						$numStudents = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$Cmysql->EscapeString($_POST['hour']). "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Perform checks (we need all of them to be TRUE to continue)
						if (
							( $uLectureID[0] === "0" ) &&
							( $lectHeld[0] === "1" ) &&
							( ( $numStudents[0] < $limit[0] ) || ( $limit[0] === "0" ) )
						)
						{
							// If all conditions are met, we allow the user to sign up
							if ( config("freeuniversity_allow") == "on" )
							{
								$signedUp = $Cmysql->Query("UPDATE users SET f_hour" .$Cmysql->EscapeString($_POST['hour']). "='" .$Cmysql->EscapeString($_POST['id']). "' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
							} elseif ( config("freeuniversity_allow") == "off" )
							{
								// Disallow non-read access to Freeuniversity database if it is finalized.
								$signedUp = FALSE;
							}
							
							if ( $signedUp === TRUE )
							{
								log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásra ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' jelentkezett a(z) ' .$_POST['hour']. '. órában.');
								
								// If the user was able to sign up, we output a success message
								$Ctemplate->useTemplate("successbox", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							} elseif ( $signedUp === FALSE )
							{
								// If we were unable to sign up the user
								// (at this point due to an SQL error),
								// we output an error message
								$Ctemplate->useTemplate("errormessage", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							}
						} else {
							// If the user is unable to attend due to one or more of the conditions
							// not fitting, we output an error message.
							
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 12:
					case 23:
					case 34:
						// If we attempt to sign-up on a double-hour setup, we need to perform the condition
						// checks the same way as single-hour attempts, but twice on both hours.
						
						// Split the double-hour code to two single-hour codes. (These are STRING values!)
						if ( $_POST['hour'] === "12" )
						{
							$hour1 = "1";
							$hour2 = "2";
						} elseif ( $_POST['hour'] === "23" )
						{
							$hour1 = "2";
							$hour2 = "3";
						} elseif ( $_POST['hour'] === "34" )
						{
							$hour1 = "3";
							$hour2 = "4";
						}
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectDouble = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1. "_" .$hour2. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectDouble[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Get the user's attendance lecture ID for hour #
						$uLectureID = mysql_fetch_row($Cmysql->Query("SELECT f_hour" .$hour1. ", f_hour" .$hour2. " FROM users WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"));
						
						// Get whether the lecture is held in hour #
						$lectHeld = mysql_fetch_row($Cmysql->Query("SELECT hour" .$hour1. ", hour" .$hour2. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the lecture limit for said hour
						$limit = mysql_fetch_row($Cmysql->Query("SELECT limit" .$hour1. ", limit" .$hour2. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the number of students attending said lecture in said hour
						$numStudents[0] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[1] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Perform checks (we need all of them to be TRUE to continue)
						if (
							( $uLectureID[0] === "0" ) && ( $uLectureID[1] === "0" ) &&
							( $lectHeld[0] === "1" ) && ( $lectHeld[1] === "1" ) &&
							( ( $numStudents[0][0] < $limit[0] ) || ( $limit[0] === "0" ) ) &&
							( ( $numStudents[1][0] < $limit[1] ) || ( $limit[1] === "0" ) )
						)
						{
							// If all conditions are met, we allow the user to sign up (for both hours)
							if ( config("freeuniversity_allow") == "on" )
							{
								$signedUp = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
							} elseif ( config("freeuniversity_allow") == "off" )
							{
								// Disallow non-read access to Freeuniversity database if it is finalized.
								$signedUp = FALSE;
							}
							
							if ( $signedUp === TRUE )
							{
								log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásra ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' jelentkezett a(z) ' .$hour1. '-' .$hour2. '. órában.');
								
								// If the user was able to sign up, we output a success message
								$Ctemplate->useTemplate("successbox", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							} elseif ( $signedUp === FALSE )
							{
								// If we were unable to sign up the user
								// (at this point due to an SQL error),
								// we output an error message
								$Ctemplate->useTemplate("errormessage", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							}
						} else {
							// If the user is unable to attend due to one or more of the conditions
							// not fitting, we output an error message.
							
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 123:
					case 234:
						if ( $_POST['hour'] === "123" )
						{
							$hour1 = "1";
							$hour2 = "2";
							$hour3 = "3";
						} elseif ( $_POST['hour'] === "234" )
						{
							$hour1 = "2";
							$hour2 = "3";
							$hour3 = "4";
						}
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectTriple = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1.$hour2.$hour3. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectTriple[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Get the user's attendance lecture ID for hour #
						$uLectureID = mysql_fetch_row($Cmysql->Query("SELECT f_hour" .$hour1. ", f_hour" .$hour2. ", f_hour" .$hour3. " FROM users WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"));
						
						// Get whether the lecture is held in hour #
						$lectHeld = mysql_fetch_row($Cmysql->Query("SELECT hour" .$hour1. ", hour" .$hour2. ", hour" .$hour3. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the lecture limit for said hour
						$limit = mysql_fetch_row($Cmysql->Query("SELECT limit" .$hour1. ", limit" .$hour2. ", limit" .$hour3. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the number of students attending said lecture in said hour
						$numStudents[0] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[1] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[2] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour3. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Perform checks (we need all of them to be TRUE to continue)
						if (
							( $uLectureID[0] === "0" ) && ( $uLectureID[1] === "0" ) && ( $uLectureID[2] === "0" ) &&
							( $lectHeld[0] === "1" ) && ( $lectHeld[1] === "1" ) && ( $lectHeld[2] === "1" ) &&
							( ( $numStudents[0][0] < $limit[0] ) || ( $limit[0] === "0" ) ) &&
							( ( $numStudents[1][0] < $limit[1] ) || ( $limit[1] === "0" ) ) &&
							( ( $numStudents[2][0] < $limit[2] ) || ( $limit[2] === "0" ) )
						)
						{
							// If all conditions are met, we allow the user to sign up (for both hours)
							if ( config("freeuniversity_allow") == "on" )
							{
								$signedUp = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour3. "='" .$Cmysql->EscapeString($_POST['id']). "' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
							} elseif ( config("freeuniversity_allow") == "off" )
							{
								// Disallow non-read access to Freeuniversity database if it is finalized.
								$signedUp = FALSE;
							}
							
							if ( $signedUp === TRUE )
							{
								log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásra ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' jelentkezett a(z) ' .$hour1. '-' .$hour2. '-' .$hour3. '. órában.');
								
								// If the user was able to sign up, we output a success message
								$Ctemplate->useTemplate("successbox", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							} elseif ( $signedUp === FALSE )
							{
								// If we were unable to sign up the user
								// (at this point due to an SQL error),
								// we output an error message
								$Ctemplate->useTemplate("errormessage", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							}
						} else {
							// If the user is unable to attend due to one or more of the conditions
							// not fitting, we output an error message.
							
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 1234:
						$hour1 = "1";
						$hour2 = "2";
						$hour3 = "3";
						$hour4 = "4";
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectQuad = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1.$hour2.$hour3.$hour4. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectQuad[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Get the user's attendance lecture ID for hour #
						$uLectureID = mysql_fetch_row($Cmysql->Query("SELECT f_hour" .$hour1. ", f_hour" .$hour2. ", f_hour" .$hour3. ", f_hour" .$hour4. " FROM users WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'"));
						
						// Get whether the lecture is held in hour #
						$lectHeld = mysql_fetch_row($Cmysql->Query("SELECT hour" .$hour1. ", hour" .$hour2. ", hour" .$hour3. ", hour" .$hour4. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the lecture limit for said hour
						$limit = mysql_fetch_row($Cmysql->Query("SELECT limit" .$hour1. ", limit" .$hour2. ", limit" .$hour3. ", limit" .$hour4. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Get the number of students attending said lecture in said hour
						$numStudents[0] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[1] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[2] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour3. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						$numStudents[3] = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users WHERE f_hour" .$hour4. "='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						// Perform checks (we need all of them to be TRUE to continue)
						if (
							( $uLectureID[0] === "0" ) && ( $uLectureID[1] === "0" ) && ( $uLectureID[2] === "0" ) && ( $uLectureID[3] === "0" ) &&
							( $lectHeld[0] === "1" ) && ( $lectHeld[1] === "1" ) && ( $lectHeld[2] === "1" ) && ( $lectHeld[3] === "1" ) &&
							( ( $numStudents[0][0] < $limit[0] ) || ( $limit[0] === "0" ) ) &&
							( ( $numStudents[1][0] < $limit[1] ) || ( $limit[1] === "0" ) ) &&
							( ( $numStudents[2][0] < $limit[2] ) || ( $limit[2] === "0" ) ) &&
							( ( $numStudents[3][0] < $limit[3] ) || ( $limit[3] === "0" ) )
						)
						{
							// If all conditions are met, we allow the user to sign up (for both hours)
							if ( config("freeuniversity_allow") == "on" )
							{
								$signedUp = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour2. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour3. "='" .$Cmysql->EscapeString($_POST['id']). "', f_hour" .$hour4. "='" .$Cmysql->EscapeString($_POST['id']). "' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
							} elseif ( config("freeuniversity_allow") == "off" )
							{
								// Disallow non-read access to Freeuniversity database if it is finalized.
								$signedUp = FALSE;
							}
							
							if ( $signedUp === TRUE )
							{
								log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásra ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' jelentkezett a(z) ' .$hour1. '-' .$hour2. '-' .$hour3. '-' .$hour4. '. órában.');
								
								// If the user was able to sign up, we output a success message
								$Ctemplate->useTemplate("successbox", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_SIGNEDUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							} elseif ( $signedUp === FALSE )
							{
								// If we were unable to sign up the user
								// (at this point due to an SQL error),
								// we output an error message
								$Ctemplate->useTemplate("errormessage", array(
									'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
									'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}'],
									'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP_BODY}",
									'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_SIGNUP}",
									'LECTURE_TITLE'	=>	$lTitle[0]
								), FALSE);
								$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							}
						} else {
							// If the user is unable to attend due to one or more of the conditions
							// not fitting, we output an error message.
							
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_CANNOT_ATTEND}",
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
				}
			}
			
			break;
		case "resign":
			// ----- Request to revoke a user's attendance from a lecture ----
			
			if ( ( @$_POST['id'] === NULL ) || ( @$_POST['hour'] === NULL ) )
			{
				// If somehow there wasn't a lecture ID and an attendance hour
				// variables passed with the call, we terminate execution.
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_DIRECT_OPENING}"
				), FALSE); // Output an error message
				$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
			} elseif ( ( @$_POST['id'] != NULL ) && ( !in_array(@$_POST['hour'], array(1, 2, 3, 4, 12, 23, 34, 123, 234, 1234)) ) )
			{
				// Another possible mishap: ID is present but the hour variable points to an invalid hour
				$Ctemplate->useTemplate("errormessage", array(
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
					'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
					'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
					'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
				), FALSE); // Output an error message
				$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
			} elseif ( ( @$_POST['id'] != NULL ) && ( in_array(@$_POST['hour'], array(1, 2, 3, 4, 12, 23, 34, 123, 234, 1234)) ) )
			{
				// The lecture ID is present and the hour variable points to a valid option
				// This means that we are allowed to resign from attending the lecture.
				
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
					$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
					
					$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
					DoFooter();
					exit;
				}
				
				switch ( $_POST['hour'] )
				{
					// We create an multiple brach of events
					// based on the hour variable the user requested
					case 1:
					case 2:
					case 3:
					case 4:
						// If we attempt to go revoke a single-hour setup, resign the user
						if ( config("freeuniversity_allow") == "on" )
						{
							$resigned = $Cmysql->Query("UPDATE users SET f_hour" .$Cmysql->EscapeString($_POST['hour']). "='0' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$resigned = FALSE;
						}
						
						if ( $resigned === TRUE )
						{
							log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásról ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' leiratkozott a(z) ' .$_POST['hour']. '. órában.');
							
							// If the user was able to resign, we output a success message
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'BODY'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED_BODY}'],
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						} elseif ( $resigned === FALSE )
						{
							// If we were unable to resign the user
							// (at this point due to an SQL error),
							// we output an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
								'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}'],
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 12:
					case 23:
					case 34:
						// If we attempt to resign from a double-hour setup, we resign the user from two hours.
						
						// Split the double-hour code to two single-hour codes. (These are STRING values!)
						if ( $_POST['hour'] === "12" )
						{
							$hour1 = "1";
							$hour2 = "2";
						} elseif ( $_POST['hour'] === "23" )
						{
							$hour1 = "2";
							$hour2 = "3";
						} elseif ( $_POST['hour'] === "34" )
						{
							$hour1 = "3";
							$hour2 = "4";
						}
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectDouble = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1. "_" .$hour2. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectDouble[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Resign the user
						if ( config("freeuniversity_allow") == "on" )
						{
							$resigned = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='0', f_hour" .$hour2. "='0' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$resigned = FALSE;
						}
						
						if ( $resigned === TRUE )
						{
							log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásról ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' leiratkozott a(z) ' .$hour1. '-' .$hour2. '. órában.');
							
							// If the user was able to resign, we output a success message
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'BODY'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED_BODY}'],
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						} elseif ( $resigned === FALSE )
						{
							// If we were unable to resign the user
							// (at this point due to an SQL error),
							// we output an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
								'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}'],
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 123:
					case 234:
						if ( $_POST['hour'] === "123" )
						{
							$hour1 = "1";
							$hour2 = "2";
							$hour3 = "3";
						} elseif ( $_POST['hour'] === "234" )
						{
							$hour1 = "2";
							$hour2 = "3";
							$hour3 = "4";
						}
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectTriple = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1.$hour2.$hour3. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectTriple[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Resign the user
						if ( config("freeuniversity_allow") == "on" )
						{
							$resigned = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='0', f_hour" .$hour2. "='0', f_hour" .$hour3. "='0' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$resigned = FALSE;
						}
						
						if ( $resigned === TRUE )
						{
							log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásról ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' leiratkozott a(z) ' .$hour1. '-' .$hour2. '-' .$hour3. '. órában.');
							
							// If the user was able to resign, we output a success message
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'BODY'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED_BODY}'],
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						} elseif ( $resigned === FALSE )
						{
							// If we were unable to resign the user
							// (at this point due to an SQL error),
							// we output an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
								'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}'],
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
					case 1234:
						$hour1 = "1";
						$hour2 = "2";
						$hour3 = "3";
						$hour4 = "4";
						
						// Get whether the lecture is a double-hour lecture and perform a check on it
						$lectQuad = mysql_fetch_row($Cmysql->Query("SELECT lect" .$hour1.$hour2.$hour3.$hour4. " FROM f_lectures WHERE id='" .$Cmysql->EscapeString($_POST['id']). "'"));
						
						if ( $lectQuad[0] === "0" )
						{
							// If there is lecture is not a double-hour lecture, we terminate execution with an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}",
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_INVALID_HOUR}"
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
							
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE);
							DoFooter();
							exit;
						}
						
						// Resign the user
						if ( config("freeuniversity_allow") == "on" )
						{
							$resigned = $Cmysql->Query("UPDATE users SET f_hour" .$hour1. "='0', f_hour" .$hour2. "='0', f_hour" .$hour3. "='0', f_hour" .$hour4. "='0' WHERE id='" .$Cmysql->EscapeString($_SESSION['uid']). "'");
						} elseif ( config("freeuniversity_allow") == "off" )
						{
							// Disallow non-read access to Freeuniversity database if it is finalized.
							$resigned = FALSE;
						}
						
						if ( $resigned === TRUE )
						{
							log_append('A(z) ' .$lTitle[0]. ' (#' .$_POST['id']. ') előadásról ' .$_SESSION['username']. ' (#' .$_SESSION['uid']. ')' .
										' leiratkozott a(z) ' .$hour1. '-' .$hour2. '-' .$hour3. '-' .$hour4. '. órában.');
							
							// If the user was able to resign, we output a success message
							$Ctemplate->useTemplate("successbox", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_korganizer.png",
								'TITLE'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'BODY'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED_BODY}'],
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_RESIGNED}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						} elseif ( $resigned === FALSE )
						{
							// If we were unable to resign the user
							// (at this point due to an SQL error),
							// we output an error message
							$Ctemplate->useTemplate("errormessage", array(
								'PICTURE_NAME'	=>	"Nuvola_apps_error.png",
								'TITLE'	=>	$wf_lang['{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}'],
								'BODY'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN_BODY}",
								'ALT'	=>	"{LANG_FREEUNIVERSITY_SCHEDULE_UNABLE_RESIGN}",
								'LECTURE_TITLE'	=>	$lTitle[0]
							), FALSE);
							$Ctemplate->useStaticTemplate("freeuniversity/schedule_retry_button", FALSE);
						}
						
						break;
				}
			}
			
			break;
		case "list":
		default:
			// ----- Default action ------
			// ----- Output own lectures and form a resign table -----
			// Get the current user's lecture IDs for each hour
			$uLect = mysql_fetch_assoc($Cmysql->Query("SELECT f_hour1, f_hour2, f_hour3, f_hour4 FROM users WHERE id='" .$_SESSION['uid']. "'"));
			
			// Define an empty container for the lecture rows
			$rRow = array();
			
			// Create the resign table row for the first hour
			if ( $uLect['f_hour1'] != 0 )
			{
				// If the user is attending a lecture, check
				// whether that lecture is a double-hour one
				$double[12] = mysql_fetch_row($Cmysql->Query("SELECT lect1_2 FROM f_lectures WHERE id='" .$uLect['f_hour1']. "'"));
				
				// Get the title and the description of this lecture
				$lect[1] = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, description FROM f_lectures WHERE id='" .$uLect['f_hour1']. "'"));
				
				if ( ( $double[12][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour1'] ) )
				{
					// If the lecture is a double-hour one, and the user is attending
					// the same one in the second hour, we fill a double-hour row
					$rRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	2, // Row span of cell (2 because double-hour)
						'ID'	=>	$uLect['f_hour1'], // ID of the lecture
						'HOUR'	=>	12, // The # of hour the button represents
						'TITLE'	=>	$lect[1]['title'], // Title of the lecture
						'DESCRIPTION'	=>	( $lect[1]['description'] === "" || $lect[1]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[1]['id']
							), TRUE) ), // Description of the lecture (if present)
						'HEIGHT'	=>	40, // Height of the row (2x20 because double-hour)
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				} elseif ( $double[12][0] === "0" )
				{
					// If the lecture is a single-hour one, we will a single-hour row
					$rRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	1, // Row span of cell (1 because single-hour)
						'ID'	=>	$uLect['f_hour1'], // ID of the lecture
						'HOUR'	=>	1, // The # of hour the button represents
						'TITLE'	=>	$lect[1]['title'], // Title of the lecture
						'DESCRIPTION'	=>	( $lect[1]['description'] === "" || $lect[1]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[1]['id']
							), TRUE) ), // Description of the lecture (if present)
						'HEIGHT'	=>	20, // Height of row (1x20 becuase single-hour)
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
				
				$triple[123] = mysql_fetch_row($Cmysql->Query("SELECT lect123 FROM f_lectures WHERE id='" .$uLect['f_hour1']. "'"));
				if ( ( $triple[123][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour1'] ) && ( $uLect['f_hour3'] === $uLect['f_hour1'] ) )
				{
					$rRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	3, // Row span of cell (2 because double-hour)
						'ID'	=>	$uLect['f_hour1'], // ID of the lecture
						'HOUR'	=>	123, // The # of hour the button represents
						'TITLE'	=>	$lect[1]['title'], // Title of the lecture
						'DESCRIPTION'	=>	( $lect[1]['description'] === "" || $lect[1]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[1]['id']
							), TRUE) ), // Description of the lecture (if present)
						'HEIGHT'	=>	40, // Height of the row (2x20 because double-hour)
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
				
				$quad[1234] =  mysql_fetch_row($Cmysql->Query("SELECT lect1234 FROM f_lectures WHERE id='" .$uLect['f_hour1']. "'"));
				if ( ( $quad[1234][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour1'] ) && ( $uLect['f_hour3'] === $uLect['f_hour1'] ) && ( $uLect['f_hour4'] === $uLect['f_hour1'] ) )
				{
					$rRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	4, // Row span of cell (2 because double-hour)
						'ID'	=>	$uLect['f_hour1'], // ID of the lecture
						'HOUR'	=>	1234, // The # of hour the button represents
						'TITLE'	=>	$lect[1]['title'], // Title of the lecture
						'DESCRIPTION'	=>	( $lect[1]['description'] === "" || $lect[1]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[1]['id']
							), TRUE) ), // Description of the lecture (if present)
						'HEIGHT'	=>	40, // Height of the row (2x20 because double-hour)
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
			} elseif ( $uLect['f_hour1'] === "0" )
			{
				// If the user is not attending anything, we return an empty row
				$rRow[1] = $Ctemplate->useStaticTemplate("freeuniversity/schedule_resign_row_empty", TRUE);
			}
			
			// Do the same for the rest of the hours
			if ( $uLect['f_hour2'] != 0 )
			{
				$double[12] = mysql_fetch_row($Cmysql->Query("SELECT lect1_2 FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				$double[23] = mysql_fetch_row($Cmysql->Query("SELECT lect2_3 FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				$triple[123] = mysql_fetch_row($Cmysql->Query("SELECT lect123 FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				$triple[234] = mysql_fetch_row($Cmysql->Query("SELECT lect234 FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				$quad[1234] =  mysql_fetch_row($Cmysql->Query("SELECT lect1234 FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				$lect[2] = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, description FROM f_lectures WHERE id='" .$uLect['f_hour2']. "'"));
				
				if ( ( $double[12][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour2'] ) )
				{
					// If the lecture is a double-hour one, and the user is attending
					// the same one in the previous hour, we fill an empty row,
					// because the row is already filled when the previous hour was checked.
					
					$rRow[2] = NULL;
				} elseif ( ( $triple[123][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour2'] ) && ( $uLect['f_hour3'] === $uLect['f_hour2'] ) )
				{
					$rRow[2] = NULL;
				} elseif ( ( $quad[1234][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour2'] ) && ( $uLect['f_hour3'] === $uLect['f_hour1'] ) && ( $uLect['f_hour4'] === $uLect['f_hour2'] ) )
				{
					$rRow[2] = NULL;
				} elseif ( ( $double[23][0] === "1" ) && ( $uLect['f_hour3'] === $uLect['f_hour2'] ) )
				{
					$rRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	2,
						'ID'	=>	$uLect['f_hour2'],
						'HOUR'	=>	23,
						'TITLE'	=>	$lect[2]['title'],
						'DESCRIPTION'	=>	( $lect[2]['description'] === "" || $lect[2]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[2]['id']
							), TRUE) ),
						'HEIGHT'	=>	40,
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				} elseif ( ( $double[12][0] === "0" ) && ( $double[23][0] === "0" ) )
				{
					$rRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	1,
						'ID'	=>	$uLect['f_hour2'],
						'HOUR'	=>	2,
						'TITLE'	=>	$lect[2]['title'],
						'DESCRIPTION'	=>	( $lect[2]['description'] === "" || $lect[2]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[2]['id']
							), TRUE) ),
						'HEIGHT'	=>	20,
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
				
				if ( ( $triple[234][0] === "1" ) && ( $uLect['f_hour3'] === $uLect['f_hour2'] ) && ( $uLect['f_hour4'] === $uLect['f_hour2'] ) )
				{
					$rRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	3, // Row span of cell (2 because double-hour)
						'ID'	=>	$uLect['f_hour2'], // ID of the lecture
						'HOUR'	=>	234, // The # of hour the button represents
						'TITLE'	=>	$lect[2]['title'], // Title of the lecture
						'DESCRIPTION'	=>	( $lect[2]['description'] === "" || $lect[2]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[2]['id']
							), TRUE) ), // Description of the lecture (if present)
						'HEIGHT'	=>	40, // Height of the row (2x20 because double-hour)
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
			} elseif ( $uLect['f_hour2'] === "0" )
			{
				$rRow[2] = $Ctemplate->useStaticTemplate("freeuniversity/schedule_resign_row_empty", TRUE);
			}
			
			if ( $uLect['f_hour3'] != 0 )
			{
				$double[23] = mysql_fetch_row($Cmysql->Query("SELECT lect2_3 FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				$double[34] = mysql_fetch_row($Cmysql->Query("SELECT lect3_4 FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				$triple[123] = mysql_fetch_row($Cmysql->Query("SELECT lect123 FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				$triple[234] = mysql_fetch_row($Cmysql->Query("SELECT lect234 FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				$quad[1234] =  mysql_fetch_row($Cmysql->Query("SELECT lect1234 FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				$lect[3] = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, description FROM f_lectures WHERE id='" .$uLect['f_hour3']. "'"));
				
				if ( ( $double[23][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour3'] ) )
				{
					$rRow[3] = NULL;
				} elseif ( ( $triple[123][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour3'] ) && ( $uLect['f_hour2'] === $uLect['f_hour3'] ) )
				{
					$rRow[3] = NULL;
				} elseif ( ( $triple[234][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour3'] ) && ( $uLect['f_hour4'] === $uLect['f_hour3'] ) )
				{
					$rRow[3] = NULL;
				} elseif ( ( $quad[1234][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour3'] ) && ( $uLect['f_hour2'] === $uLect['f_hour3'] ) && ( $uLect['f_hour4'] === $uLect['f_hour3'] ) )
				{
					$rRow[3] = NULL;
				} elseif ( ( $double[34][0] === "1" ) && ( $uLect['f_hour4'] === $uLect['f_hour3'] ) )
				{
					$rRow[3] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	2,
						'ID'	=>	$uLect['f_hour3'],
						'HOUR'	=>	34,
						'TITLE'	=>	$lect[3]['title'],
						'DESCRIPTION'	=>	( $lect[3]['description'] === "" || $lect[3]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[3]['id']
							), TRUE) ),
						'HEIGHT'	=>	40,
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				} elseif ( ( $double[23][0] === "0" ) && ( $double[34][0] === "0" ) )
				{
					$rRow[3] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	1,
						'ID'	=>	$uLect['f_hour3'],
						'HOUR'	=>	3,
						'TITLE'	=>	$lect[3]['title'],
						'DESCRIPTION'	=>	( $lect[3]['description'] === "" || $lect[3]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[3]['id']
							), TRUE) ),
						'HEIGHT'	=>	20,
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
			} elseif ( $uLect['f_hour3'] === "0" )
			{
				$rRow[3] = $Ctemplate->useStaticTemplate("freeuniversity/schedule_resign_row_empty", TRUE);
			}
			
			if ( $uLect['f_hour4'] != 0 )
			{
				$double[34] = mysql_fetch_row($Cmysql->Query("SELECT lect3_4 FROM f_lectures WHERE id='" .$uLect['f_hour4']. "'"));
				$triple[234] = mysql_fetch_row($Cmysql->Query("SELECT lect234 FROM f_lectures WHERE id='" .$uLect['f_hour4']. "'"));
				$quad[1234] =  mysql_fetch_row($Cmysql->Query("SELECT lect1234 FROM f_lectures WHERE id='" .$uLect['f_hour4']. "'"));
				$lect[4] = mysql_fetch_assoc($Cmysql->Query("SELECT id, title, description FROM f_lectures WHERE id='" .$uLect['f_hour4']. "'"));
				
				if ( ( $double[34][0] === "1" ) && ( $uLect['f_hour4'] === $uLect['f_hour3'] ) )
				{
					$rRow[4] = NULL;
				} elseif ( ( $triple[234][0] === "1" ) && ( $uLect['f_hour2'] === $uLect['f_hour4'] ) && ( $uLect['f_hour3'] === $uLect['f_hour4'] ) )
				{
					$rRow[4] = NULL;
				} elseif ( ( $quad[1234][0] === "1" ) && ( $uLect['f_hour1'] === $uLect['f_hour4'] ) && ( $uLect['f_hour2'] === $uLect['f_hour4'] ) && ( $uLect['f_hour3'] === $uLect['f_hour4'] ) )
				{
					$rRow[4] = NULL;
				} elseif ( $double[34][0] === "0" )
				{
					$rRow[4] = $Ctemplate->useTemplate("freeuniversity/schedule_resign_row_button", array(
						'ROWSPAN'	=>	1,
						'ID'	=>	$uLect['f_hour4'],
						'HOUR'	=>	4,
						'TITLE'	=>	$lect[4]['title'],
						'DESCRIPTION'	=>	( $lect[4]['description'] === "" || $lect[4]['description'] === null ? NULL :
							$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
								'LECTURE_ID'	=>	$lect[4]['id']
							), TRUE) ),
						'HEIGHT'	=>	20,
						'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
					), TRUE);
				}
			} elseif ( $uLect['f_hour4'] === "0" )
			{
				$rRow[4] = $Ctemplate->useStaticTemplate("freeuniversity/schedule_resign_row_empty", TRUE);
			}
			
			// Output the resign table using the now-filled container
			$Ctemplate->useTemplate("freeuniversity/schedule_resign", array(
				'HOUR1'	=>	$rRow[1],
				'HOUR2'	=>	$rRow[2],
				'HOUR3'	=>	$rRow[3],
				'HOUR4'	=>	$rRow[4]
			), FALSE);
			
			// ----- Listing lectures ------
			// Open the list table with its header
			$Ctemplate->useStaticTemplate("freeuniversity/schedule_table_open", FALSE);
			
			// Get every lecture into a MySQL result
			$lectures_result = $Cmysql->Query("SELECT id, title, description, hour1, hour2, hour3, hour4, limit1, limit2, limit3, limit4, lect1_2, lect2_3, lect3_4, lect123, lect234, lect1234 FROM f_lectures ORDER BY title ASC");
			
			while ( $lRow = mysql_fetch_assoc($lectures_result) )
			{
				// With going trough every single lecture, we make an output row
				
				// ---- Check whether the user is avaiable to sign up for hour # ----
				// Conditions: user can sign up if
				//  - the lecture is held in said hour AND
				//  - there is at least one spot for said hour AND
				//  - the user did not already selected a lecture for said hour
				
				// Define an array which states that the user cannot go
				// to the lecture in said hour by default
				// (the values will turn TRUE if the user can)
				$userCanGo = array(
					1	=>	FALSE,
					2	=>	FALSE,
					3	=>	FALSE,
					4	=>	FALSE,
					12	=>	FALSE,
					23	=>	FALSE,
					34	=>	FALSE,
					123	=>	FALSE,
					234	=>	FALSE,
					1234	=>	FALSE
				);
				
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
				
				if (
					( $lRow['hour1'] == "1" ) &&
					( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
					( $uLect['f_hour1'] === "0" )
				)
				{
					// Check for the said conditions on the first hour
					// If we came this far, it means the user can go on the first hour
					$userCanGo[1] = TRUE;
					
					// ---- Check for double-hour setup on hour1-2 ----
					if ( $lRow['lect1_2'] === "1" )
					{
						// If the current lecture is a double-hour in hour 1 and 2,
						// we perform the checks on the second hour too
						
						// If the lecture is a double-hour setup,
						// we instantly disallow the user from joining
						// on a (nonexistant) single-hour setup.
						
						// The above example can happen if the database derps
						// or the initial entries are misconfigured,
						// because the user is allowed to join a double-hour
						// lecture on single-hour, further messing up the database.
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" )
						)
						{
							// If every condition is met on the second hour,
							// we set the userCanGo array on 1-2 double hour
							$userCanGo[12] = TRUE;
						}
					}
					
					if ($lRow['lect123'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) && ( $lRow['hour3'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[123] = TRUE;
						}
					}
					
					if ($lRow['lect1234'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) && ( $lRow['hour3'] === "1" ) && ( $lRow['hour4'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour3'] === "0" ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[1234] = TRUE;
						}
					}
				}
				
				// Do the same condition check on the rest of the hours
				if (
					( $lRow['hour2'] == "1" ) &&
					( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
					( $uLect['f_hour2'] === "0" )
				)
				{
					$userCanGo[2] = TRUE;
					
					// ---- Check for double-hour setup on hour1-2 ----
					if ( $lRow['lect1_2'] === "1" )
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" )
						)
						{
							$userCanGo[12] = TRUE;
						}
					}
					
					// ---- Check for double-hour setup on hour2-3 ----
					if ( $lRow['lect2_3'] === "1" )
					{
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour3'] === "1" ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[23] = TRUE;
						}
					}
					
					if ($lRow['lect123'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) && ( $lRow['hour3'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[123] = TRUE;
						}
					}
					
					if ($lRow['lect234'] === "1")
					{
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour3'] === "1" ) && ( $lRow['hour4'] === "1" ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour3'] === "0" ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[234] = TRUE;
						}
					}
					
					if ($lRow['lect1234'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) && ( $lRow['hour3'] === "1" ) && ( $lRow['hour4'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" ) &&
							( $uLect['f_hour3'] === "0" ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[1234] = TRUE;
						}
					}
				}
				
				if (
					( $lRow['hour3'] == "1" ) &&
					( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
					( $uLect['f_hour3'] === "0" )
				)
				{
					$userCanGo[3] = TRUE;
					
					// ---- Check for double-hour setup on hour2-3 ----
					if ( $lRow['lect2_3'] === "1" )
					{
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" )
						)
						{
							$userCanGo[23] = TRUE;
						}
					}
					
					// ---- Check for double-hour setup on hour3-4 ----
					if ( $lRow['lect3_4'] === "1" )
					{
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour4'] === "1" ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[34] = TRUE;
						}
					}
					
					if ($lRow['lect123'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) && ( $lRow['hour2'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" ) &&
							( $uLect['f_hour2'] === "0" )
						)
						{
							$userCanGo[123] = TRUE;
						}
					}
					
					if ($lRow['lect234'] === "1")
					{
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) && ( $lRow['hour4'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[234] = TRUE;
						}
					}
					
					if ($lRow['lect1234'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) && ( $lRow['hour2'] === "1" ) && ( $lRow['hour4'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour4'] === "0" )
						)
						{
							$userCanGo[1234] = TRUE;
						}
					}
				}
				
				if (
					( $lRow['hour4'] == "1" ) &&
					( ( $numStud[4] < $lRow['limit4'] ) || ( $lRow['limit4'] === "0" ) ) &&
					( $uLect['f_hour4'] === "0" )
				)
				{
					$userCanGo[4] = TRUE;
					
					// ---- Check for double-hour setup on hour3-4 ----
					if ( $lRow['lect3_4'] === "1" )
					{
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour3'] === "1" ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[34] = TRUE;
						}
					}
					
					if ($lRow['lect234'] === "1")
					{
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[1234] = FALSE;
						
						if (
							( $lRow['hour2'] === "1" ) && ( $lRow['hour3'] === "1" ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[234] = TRUE;
						}
					}
					
					if ($lRow['lect1234'] === "1")
					{
						$userCanGo[1] = FALSE;
						$userCanGo[2] = FALSE;
						$userCanGo[3] = FALSE;
						$userCanGo[4] = FALSE;
						$userCanGo[12] = FALSE;
						$userCanGo[23] = FALSE;
						$userCanGo[34] = FALSE;
						$userCanGo[123] = FALSE;
						$userCanGo[234] = FALSE;
						
						if (
							( $lRow['hour1'] === "1" ) && ( $lRow['hour2'] === "1" ) && ( $lRow['hour3'] === "1" ) &&
							( ( $numStud[1] < $lRow['limit1'] ) || ( $lRow['limit1'] === "0" ) ) &&
							( ( $numStud[2] < $lRow['limit2'] ) || ( $lRow['limit2'] === "0" ) ) &&
							( ( $numStud[3] < $lRow['limit3'] ) || ( $lRow['limit3'] === "0" ) ) &&
							( $uLect['f_hour1'] === "0" ) &&
							( $uLect['f_hour2'] === "0" ) &&
							( $uLect['f_hour3'] === "0" )
						)
						{
							$userCanGo[1234] = TRUE;
						}
					}
				}
				
				// At this point, $userCanGo contains the lecture
				// setup of whether we can or can't output attend buttons.
				
				// First, check for inconsistencies in the $userCanGo array
				// If anything is improper, we panic and forbid the user from attending.
				// Note: The panic action _rewrites_ the $userCanGo array, thus setting every
				// key and value checked after the consistency check to "FALSE" (undefined).
				$panic = FALSE; // By default, everything is fine here.
				if (
					( ( $userCanGo[12] === TRUE ) && ( $userCanGo[23] === TRUE ) ) ||
					( ( $userCanGo[23] === TRUE ) && ( $userCanGo[34] === TRUE ) )
				)
				{
					// Error example one: confilicting double-hour setups
					$panic = TRUE;
				}
				
				if (
					( ( $userCanGo[1] === TRUE ) && ( $userCanGo[2] === TRUE ) && ( $userCanGo[12] === TRUE ) ) ||
					( ( $userCanGo[2] === TRUE ) && ( $userCanGo[3] === TRUE ) && ( $userCanGo[23] === TRUE ) ) ||
					( ( $userCanGo[3] === TRUE ) && ( $userCanGo[4] === TRUE ) && ( $userCanGo[34] === TRUE ) )
				)
				{
					// Error example one: double-hour and single-hour setup in the same time
					$panic = TRUE;
				}
				
				if (
					( ( $userCanGo[12] === TRUE ) && ( $userCanGo[123] === TRUE ) ) ||
					( ( $userCanGo[23] === TRUE ) && ( $userCanGo[123] === TRUE ) ) ||
					( ( $userCanGo[34] === TRUE ) && ( $userCanGo[123] === TRUE ) ) ||
						
					( ( $userCanGo[12] === TRUE ) && ( $userCanGo[234] === TRUE ) ) ||
					( ( $userCanGo[23] === TRUE ) && ( $userCanGo[234] === TRUE ) ) ||
					( ( $userCanGo[34] === TRUE ) && ( $userCanGo[234] === TRUE ) )
				)
				{
					$panic = TRUE;
				}
				
				if (
					( ($userCanGo[1234] === TRUE) && (($userCanGo[1]===TRUE) || ($userCanGo[2]===TRUE) || ($userCanGo[3]===TRUE) || ($userCanGo[4]===TRUE)) )
				)
				{
					$panic = TRUE;
				}
				
				
				// If there was at least one error (so panic is TRUE), we rewrite the userCanGo array
				// and output a panic row instead of the buttons.
				
				// Empty container for text values
				$tRow = array();
				
				if ( $panic === TRUE )
				{
					$userCanGo = array(0	=>	TRUE);
					
					// Fill the panic message to the table row for further parsing
					$tRow = array(
						1	=>	$Ctemplate->useStaticTemplate("freeuniversity/schedule_table_row_panic", TRUE),
						2	=>	NULL,
						3	=>	NULL,
						4	=>	NULL
					);
				} else {
					// ---- Output table rows ----
					
					// ---- Fill the container for HOUR #1 ----
					if ( $userCanGo[1] === TRUE )
					{
						// If the user can go in the first hour, put an Attend button in for him
						$tRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	1, // Column span of row (1 because single-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	1, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[1] === FALSE ) && ( $userCanGo[12] === TRUE ) )
					{
						// If the user cannot go on the first hour alone but can go on double-hour 12,
						// fill a double-hour double-column button for him/her here
						$tRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	2, // Column span of row (2 because double-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	12, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( $userCanGo[123] === TRUE )
					{
						$tRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	3, // Column span of row
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	123, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( $userCanGo[1234] === TRUE )
					{
						$tRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	4, // Column span of row
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	1234, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[1] === FALSE ) && ( $userCanGo[12] === FALSE ) && ( $userCanGo[123] === FALSE ) && ( $userCanGo[1234] === FALSE ) )
					{
						// If the user cannot go on the first hour no matter what, the row is empty
						$tRow[1] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_empty", array(
							'COLSPAN'	=>	1 // Column span of row (1 because single-hour)
						), TRUE);
					}
					
					// ---- Fill the container for HOUR #2 ----
					if ( $userCanGo[2] === TRUE )
					{
						// If the user can go in the second hour, put an Attend button in for him
						$tRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	1, // Column span of row (1 because single-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	2, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[2] === FALSE ) && ( $userCanGo[12] === TRUE ) )
					{
						// If the user cannot go on the second hour alone but can go on double-hour 12,
						// the second hour's column has to be empty (because the double-column
						// has been already set up when checking hour #1)
						$tRow[2] = NULL;
					} elseif ( $userCanGo[123] === TRUE )
					{
						$tRow[2] = NULL;
					} elseif ( $userCanGo[234] === TRUE )
					{
						$tRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	3, // Column span of row
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	234, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[2] === FALSE ) && ( $userCanGo[23] === TRUE ) )
					{
						// If the user cannot go on the second hour alone but can go on double-hour 23,
						// fill in a double-hour double-column Attend button.
						$tRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	2, // Column span of row (2 because double-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	23, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( $userCanGo[1234] === TRUE )
					{
						$tRow[2] = null;
					} elseif ( ( $userCanGo[2] === FALSE ) && ( $userCanGo[12] === FALSE ) && ( $userCanGo[23] === FALSE ) && ( $userCanGo[123] === FALSE ) && ( $userCanGo[234] === FALSE ) && ( $userCanGo[1234] === FALSE ) )
					{
						// If the user cannot go on the second hour no matter what, the row is empty
						$tRow[2] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_empty", array(
							'COLSPAN'	=>	1 // Column span of row (1 because single-hour)
						), TRUE);
					}
					
					// ---- Fill the container for HOUR #3 ----
					if ( $userCanGo[3] === TRUE )
					{
						// If the user can go in the third hour, put an Attend button in for him
						$tRow[3] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	1, // Column span of row (1 because single-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	3, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[3] === FALSE ) && ( $userCanGo[23] === TRUE ) )
					{
						// If the user cannot go on the third hour alone but can go on double-hour 23,
						// the third hour's column has to be empty (because the double-column
						// has been already set up when checking hour #2)
						$tRow[3] = NULL;
					} elseif ( ( $userCanGo[3] === FALSE ) && ( $userCanGo[34] === TRUE ) )
					{
						// If the user cannot go on the third hour alone but can go on double-hour 34,
						// fill in a double-hour double-column Attend button.
						$tRow[3] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	2, // Column span of row (2 because double-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	34, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( $userCanGo[123] === TRUE )
					{
						$tRow[3] = null;
					} elseif ( $userCanGo[234] === TRUE )
					{
						$tRow[3] = null;
					} elseif ( $userCanGo[1234] === TRUE )
					{
						$tRow[3] = null;
					} elseif ( ( $userCanGo[3] === FALSE ) && ( $userCanGo[23] === FALSE ) && ( $userCanGo[34] === FALSE ) && ( $userCanGo[123] === FALSE ) && ( $userCanGo[234] === FALSE ) && ( $userCanGo[1234] === FALSE ) )
					{
						// If the user cannot go on the third hour no matter what, the row is empty
						$tRow[3] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_empty", array(
							'COLSPAN'	=>	1 // Column span of row (1 because single-hour)
						), TRUE);
					}
					
					// ---- Fill the container for HOUR #4 ----
					if ( $userCanGo[4] === TRUE )
					{
						// If the user can go in the fourth hour, put an Attend button in for him
						$tRow[4] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_button", array(
							'COLSPAN'	=>	1, // Column span of row (1 because single-hour)
							'ID'	=>	$lRow['id'], // ID of the lecture
							'HOUR'	=>	4, // The # of hour the button represents
							'DISABLED'	=>	( config("freeuniversity_allow") == "on" ? NULL : " disabled")
						), TRUE);
					} elseif ( ( $userCanGo[4] === FALSE ) && ( $userCanGo[34] === TRUE ) )
					{
						// If the user cannot go on the fourth hour alone but can go on double-hour 34,
						// the fourth hour's column has to be empty (because the double-column
						// has been already set up when checking hour #3)
						$tRow[4] = NULL;
					} elseif ( $userCanGo[234] === TRUE )
					{
						$tRow[4] = null;
					} elseif ( $userCanGo[1234] === TRUE )
					{
						$tRow[4] = null;
					} elseif ( ( $userCanGo[4] === FALSE ) && ( $userCanGo[34] === FALSE ) && ( $userCanGo[234] === FALSE ) && ( $userCanGo[1234] === FALSE ) )
					{
						// If the user cannot go on the fourth hour no matter what, the row is empty
						$tRow[4] = $Ctemplate->useTemplate("freeuniversity/schedule_table_row_empty", array(
							'COLSPAN'	=>	1 // Column span of row (1 because single-hour)
						), TRUE);
					}
				}
				
				$Ctemplate->useTemplate("freeuniversity/schedule_table_row", array(
					'LECTURE_NAME'	=>
						$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturename", array(
							'TITLE'	=>	$lRow['title'], // Title of the lecture
							'DESCRIPTION'	=>	( $lRow['description'] === "" || $lRow['description'] == null ? NULL :
								$Ctemplate->useTemplate("freeuniversity/schedule_table_row_lecturedescription", array(
									'LECTURE_ID'	=>	$lRow['id']
								), TRUE) ) // Description of the lecture (appears in hover box, only if present)
						), TRUE), // Title of the lecture
					
					// Use the previously filled container to fill the current table row
					'HOUR1'	=>	$tRow[1],
					'HOUR2'	=>	$tRow[2],
					'HOUR3'	=>	$tRow[3],
					'HOUR4'	=>	$tRow[4]
				), FALSE);
			}
			
			// Close the table
			$Ctemplate->useStaticTemplate("freeuniversity/schedule_table_close", FALSE);
			
			break;
	}
}

$Ctemplate->useStaticTemplate("freeuniversity/schedule_foot", FALSE); // Footer
DoFooter();
?>
