<?php
 /**
 * WhispyForum script file - freeuni3_survey.php
 * 
 * Ending survey
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni3/survey_head", FALSE); // Header

if ( FREEUNI_PHASE != 3 )
{
	// If we aren't in phase 3 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	3, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Befejezés", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
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
// If user is logged in, the panel is accessible
	
	$already_sent_survey = mysql_num_rows($Cmysql->Query("SELECT userid FROM fu3_survey WHERE userid='" .$_SESSION['uid']. "'")); // Query wether the user already sent the survey
	
	if ( $already_sent_survey != 0 )
	{
		// If he/she did
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_mimetypes_log.png", // Survey icon
			'TITLE'	=>	"Te már kitöltötted a kérdőívet", // Error title
			'BODY'	=>	"", // Error text
			'ALT'	=>	"Kérdőív" // Alternate picture text
		), FALSE );

		/*// Terminate the script
		$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
		DoFooter();
		exit;*/
	}
	
	if ( @$_POST['survey_sent'] == "yes" )
	{
		// If we sent the survey
				
		// Check for every mandatory fields having data
		if ( @$_POST['tetszes'] == NULL ) // How much did you liked
		{
			$Ctemplate->useTemplate("freeuni3/survey_variable_error", array(
				'VARIABLE'	=>	"Mennyire tetszett a szabadegyetem?",
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['ujra'] == NULL ) // Do you want it next year
		{
			$Ctemplate->useTemplate("freeuni3/survey_variable_error", array(
				'VARIABLE'	=>	"Szeretnéd, hogy jövőre is megszervezzük?",
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['hasznos'] == NULL ) // Was it worthwhile for you
		{
			$Ctemplate->useTemplate("freeuni3/survey_variable_error", array(
				'VARIABLE'	=>	"Hasznos volt-e a számodra, tanultál-e valami újat?",
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['audience'] == NULL ) // Did you host a lecture, and rate the audience
		{
			$Ctemplate->useTemplate("freeuni3/survey_variable_error", array(
				'VARIABLE'	=>	"Tartottál-e előadást/programot, és ha igen, hogyan értékelnéd a hallgatóság figyelmét/részvételét a tevékenységben?",
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( @$_POST['activia'] == NULL ) // Joke question about a rotten Activia
		{
			$Ctemplate->useTemplate("freeuni3/survey_variable_error", array(
				'VARIABLE'	=>	"Milyen lett a január 16-án lejárt Activia?",
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( ( @$_POST['liked'] == @$_POST['not_liked'] ) && ( @$_POST['liked'] != NULL ) && ( @$_POST['not_liked'] != NULL ) ) // If the most liked and most not liked lecture is the same (it's logical assfuck), but it can be true if both is NULL
		{
			$Ctemplate->useTemplate("freeuni3/survey_likenotlike_error", array(
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Output error box with return option
			
			// Terminate the script
			$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Everything is fine, do SQL
		$survey_store = $Cmysql->Query("INSERT INTO fu3_survey(userid, tetszes, ujra, hasznos, liked, not_liked, moreprograms, audience, tip, boring, aim, activia) VALUES (
			'" .$_SESSION['uid']. "',
			'" .$Cmysql->EscapeString($_POST['tetszes']). "',
			'" .$Cmysql->EscapeString($_POST['ujra']). "',
			'" .$Cmysql->EscapeString($_POST['hasznos']). "',
			'" .$Cmysql->EscapeString(@$_POST['liked']). "',
			'" .$Cmysql->EscapeString(@$_POST['not_liked']). "',
			'" .$Cmysql->EscapeString($_POST['moreprograms']). "',
			'" .$Cmysql->EscapeString($_POST['audience']). "',
			'" .$Cmysql->EscapeString($_POST['tip']). "',
			'" .$Cmysql->EscapeString($_POST['boring']). "',
			'" .$Cmysql->EscapeString($_POST['aim']). "',
			'" .$Cmysql->EscapeString($_POST['activia']).
		"')"); // Store answers in database
		
		if ( $survey_store == FALSE )
		{
			// If we failed storing the data
			$Ctemplate->useTemplate("freeuni3/survey_error", array(
				'TETSZES'	=>	@$_POST['tetszes'],
				'UJRA'	=>	@$_POST['ujra'],
				'HASZNOS'	=>	@$_POST['hasznos'],
				'LIKED'	=>	@$_POST['liked'],
				'NOT_LIKED'	=>	@$_POST['not_liked'],
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				'AUDIENCE'	=>	@$_POST['audience'],
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				'ACTIVIA'	=>	@$_POST['activia']
			), FALSE); // Post error and return box
		} elseif ( $survey_store == TRUE )
		{
			// If we succeeded
			$Ctemplate->useTemplate("successbox", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_mimetypes_log.png", // Survey icon
				'TITLE'	=>	"A válaszod tárolása sikeres volt", // Success title
				'BODY'	=>	"Kösznönjük a részvételt.", // Success text
				'ALT'	=>	"Kérdőív" // Alternate picture text
			), FALSE );
		}
	} else {
		// If we required the survey form
		
		$user_lectures = mysql_fetch_row($Cmysql->Query("SELECT hour1, hour2, hour3, hour4 FROM users WHERE id='" .$_SESSION['uid']. "'")); // Query arranged lecture ids
		
		$lectures = $Cmysql->Query("SELECT id, lecture_name FROM fu2_lectures WHERE id IN ('" .$user_lectures[0]. "', '" .$user_lectures[1]. "', '" .$user_lectures[2]. "', '" .$user_lectures[3]. "')"); // Query lecture name
		
		// Two containers
		$liked = "";
		$not_liked = "";
		
		if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
		{
			// We output the form with data returned (user doesn't have to enter it again)
			
			while ( $row = mysql_fetch_assoc($lectures) )
			{
				// Going through every single lecture, add them to container variables
				// while auto-reselecting the previously checked lectures
				
				if ( @$_POST['liked'] == $row['id'] )
				{
					$liked .= '<option value="' .$row['id']. '" selected>' .$row['lecture_name']. '</option>'."\n";
				} else {
					$liked .= '<option value="' .$row['id']. '">' .$row['lecture_name']. '</option>'."\n";
				}
				
				if ( @$_POST['not_liked'] == $row['id'] )
				{
					$not_liked .= '<option value="' .$row['id']. '" selected>' .$row['lecture_name']. '</option>'."\n";
				} else {
					$not_liked .= '<option value="' .$row['id']. '">' .$row['lecture_name']. '</option>'."\n";
				}
			}
			
			$Ctemplate->useTemplate("freeuni3/survey_form", array(
				/* Tetszes */
				'TETSZES_1'	=>	(@$_POST['tetszes'] == "1" ? "checked" : NULL ),
				'TETSZES_2'	=>	(@$_POST['tetszes'] == "2" ? "checked" : NULL ),
				'TETSZES_3'	=>	(@$_POST['tetszes'] == "3" ? "checked" : NULL ),
				'TETSZES_4'	=>	(@$_POST['tetszes'] == "4" ? "checked" : NULL ),
				'TETSZES_5'	=>	(@$_POST['tetszes'] == "5" ? "checked" : NULL ),
				
				/* Ujra */
				'UJRA_0'	=>	(@$_POST['ujra'] == "0" ? "checked" : NULL ),
				'UJRA_1'	=>	(@$_POST['ujra'] == "1" ? "checked" : NULL ),
				
				/* Hasznos */
				'HASZNOS_1'	=>	(@$_POST['hasznos'] == "1" ? "checked" : NULL ),
				'HASZNOS_2'	=>	(@$_POST['hasznos'] == "2" ? "checked" : NULL ),
				'HASZNOS_3'	=>	(@$_POST['hasznos'] == "3" ? "checked" : NULL ),
				'HASZNOS_4'	=>	(@$_POST['hasznos'] == "4" ? "checked" : NULL ),
				'HASZNOS_5'	=>	(@$_POST['hasznos'] == "5" ? "checked" : NULL ),
				
				'LIKED_OPTIONS'	=>	$liked,
				'NOT_LIKED_OPTIONS'	=>	$not_liked,
				
				'MOREPROGRAMS'	=>	$_POST['moreprograms'],
				
				/* Audience */
				'AUDIENCE_0'	=>	(@$_POST['audience'] == "0" ? "checked" : NULL ),
				'AUDIENCE_1'	=>	(@$_POST['audience'] == "1" ? "checked" : NULL ),
				'AUDIENCE_2'	=>	(@$_POST['audience'] == "2" ? "checked" : NULL ),
				'AUDIENCE_3'	=>	(@$_POST['audience'] == "3" ? "checked" : NULL ),
				'AUDIENCE_4'	=>	(@$_POST['audience'] == "4" ? "checked" : NULL ),
				'AUDIENCE_5'	=>	(@$_POST['audience'] == "5" ? "checked" : NULL ),
				
				'TIP'	=>	$_POST['tip'],
				'BORING'	=>	$_POST['boring'],
				'AIM'	=>	$_POST['aim'],
				
				/* Activia */
				'ACTIVIA_1'	=>	(@$_POST['activia'] == "1" ? "checked" : NULL ),
				'ACTIVIA_2'	=>	(@$_POST['activia'] == "2" ? "checked" : NULL ),
				'ACTIVIA_3'	=>	(@$_POST['activia'] == "3" ? "checked" : NULL ),
			), FALSE); // Survey
		} else {
			while ( $row = mysql_fetch_assoc($lectures) )
			{
				// Going through every single lecture, add them to container variables
				$liked .= '<option value="' .$row['id']. '">' .$row['lecture_name']. '</option>'."\n";
				
				$not_liked .= '<option value="' .$row['id']. '">' .$row['lecture_name']. '</option>'."\n";
			}
			
			// We output general form
			$Ctemplate->useTemplate("freeuni3/survey_form", array(
				'LIKED_OPTIONS'	=>	$liked,
				'NOT_LIKED_OPTIONS'	=>	$not_liked,
				'MOREPROGRAMS'	=>	"",
				'TIP'	=>	"",
				'BORING'	=>	"",
				'AIM'	=>	""
			), FALSE); // Survey
		}
	}

}
$Ctemplate->useStaticTemplate("freeuni3/survey_foot", FALSE); // Footer
DoFooter();
?>