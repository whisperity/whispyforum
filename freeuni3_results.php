<?php
 /**
 * WhispyForum script file - freeuni3_results.php
 * 
 * Ending survey (result output)
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni3/results_head", FALSE); // Header

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

// Get user's level
$uDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "' AND osztaly='" .$Cmysql->EscapeString($_SESSION['osztaly']). "'")); // We query the user's data

if ( $uDBArray['userLevel'] < 3 )
{
	// If the user does not have rights to see the admin panel
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
		'TITLE'	=>	"Hiányos jogkör!", // Error title
		'BODY'	=>	"A lap megtekintéséhez adminisztrátori vagy nagyobb jogokra van szükséged.", // Error text
		'ALT'	=>	"Házirendhiba" // Alternate picture text
	), FALSE ); // We give an unaviable error
} elseif ( $uDBArray['userLevel'] >= 3 )
{
// If user is logged in, the panel is accessible
	
	// Calculate every result from the survey
	// Get data from database and parse it
	
	$osszes_diak = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users")); // All students
	$valaszolok = mysql_fetch_row($Cmysql->Query("SELECT COUNT(userid) FROM fu3_survey")); // Students who filled in the survey
	
	/* Tetszes */
	$tetszes_1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(tetszes) FROM fu3_survey WHERE tetszes='1'"));
	$tetszes_2 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(tetszes) FROM fu3_survey WHERE tetszes='2'"));
	$tetszes_3 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(tetszes) FROM fu3_survey WHERE tetszes='3'"));
	$tetszes_4 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(tetszes) FROM fu3_survey WHERE tetszes='4'"));
	$tetszes_5 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(tetszes) FROM fu3_survey WHERE tetszes='5'"));
	
	/* Ujra */
	$ujra_0 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(ujra) FROM fu3_survey WHERE ujra='0'"));
	$ujra_1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(ujra) FROM fu3_survey WHERE ujra='1'"));
	
	/* Hasznos */
	$hasznos_1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hasznos) FROM fu3_survey WHERE hasznos='1'"));
	$hasznos_2 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hasznos) FROM fu3_survey WHERE hasznos='2'"));
	$hasznos_3 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hasznos) FROM fu3_survey WHERE hasznos='3'"));
	$hasznos_4 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hasznos) FROM fu3_survey WHERE hasznos='4'"));
	$hasznos_5 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(hasznos) FROM fu3_survey WHERE hasznos='5'"));
	
	$Ctemplate->useTemplate("freeuni3/results_table_1", array(
		'OSSZES'	=>	$osszes_diak[0],
		'VALASZOLOK'	=>	$valaszolok[0],
		'SZAZALEK'	=>	(($valaszolok[0] / $osszes_diak[0]) * 100), // Percentage
		
		/* Tetszes */
		'TETSZES_1_NUM'	=>	$tetszes_1[0],
		'TETSZES_1_PER'	=>	(($tetszes_1[0] / $valaszolok[0]) * 100),
		
		'TETSZES_2_NUM'	=>	$tetszes_2[0],
		'TETSZES_2_PER'	=>	(($tetszes_2[0] / $valaszolok[0]) * 100),
		
		'TETSZES_3_NUM'	=>	$tetszes_3[0],
		'TETSZES_3_PER'	=>	(($tetszes_3[0] / $valaszolok[0]) * 100),
		
		'TETSZES_4_NUM'	=>	$tetszes_4[0],
		'TETSZES_4_PER'	=>	(($tetszes_4[0] / $valaszolok[0]) * 100),
		
		'TETSZES_5_NUM'	=>	$tetszes_5[0],
		'TETSZES_5_PER'	=>	(($tetszes_5[0] / $valaszolok[0]) * 100),
		
		/* Ujra */
		'UJRA_0_NUM'	=>	$ujra_0[0],
		'UJRA_0_PER'	=>	(($ujra_0[0] / $valaszolok[0]) * 100),
		
		'UJRA_1_NUM'	=>	$ujra_1[0],
		'UJRA_1_PER'	=>	(($ujra_1[0] / $valaszolok[0]) * 100),
		
		/* Hasznos */
		'HASZNOS_1_NUM'	=>	$hasznos_1[0],
		'HASZNOS_1_PER'	=>	(($hasznos_1[0] / $valaszolok[0]) * 100),
		
		'HASZNOS_2_NUM'	=>	$hasznos_2[0],
		'HASZNOS_2_PER'	=>	(($hasznos_2[0] / $valaszolok[0]) * 100),
		
		'HASZNOS_3_NUM'	=>	$hasznos_3[0],
		'HASZNOS_3_PER'	=>	(($hasznos_3[0] / $valaszolok[0]) * 100),
		
		'HASZNOS_4_NUM'	=>	$hasznos_4[0],
		'HASZNOS_4_PER'	=>	(($hasznos_4[0] / $valaszolok[0]) * 100),
		
		'HASZNOS_5_NUM'	=>	$hasznos_5[0],
		'HASZNOS_5_PER'	=>	(($hasznos_5[0] / $valaszolok[0]) * 100),
	), FALSE); // Output first result table (we had to crop it into pieces)
	
	$lectures = $Cmysql->Query("SELECT id, lecture_name FROM fu2_lectures ORDER BY lecture_name ASC"); // Query all lectures
	
	while ( $row = mysql_fetch_assoc($lectures) )
	{
		// Going through all lectures, output LIKED statistics
		
		$liked = mysql_fetch_row($Cmysql->Query("SELECT COUNT(liked) FROM fu3_survey WHERE liked='" .$Cmysql->EscapeString($row['id']). "'"));
		
		$Ctemplate->useTemplate("freeuni3/results_table_likenotlike_row", array(
			'ID'	=>	$row['id'],
			'LECTURE_NAME'	=>	$row['lecture_name'],
			'STUDENT_NUM'	=>	$liked[0],
			'STUDENT_PER'	=>	(($liked[0] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($lectures); // Free the result
	
	$Ctemplate->useStaticTemplate("freeuni3/results_table_2", FALSE); // Output table, close liked and open not liked parts
	
	$lectures = $Cmysql->Query("SELECT id, lecture_name FROM fu2_lectures ORDER BY lecture_name ASC"); // Query all lectures
	
	while ( $row = mysql_fetch_assoc($lectures) )
	{
		// Going through all lectures, output NOT LIKED statistics
		
		$liked = mysql_fetch_row($Cmysql->Query("SELECT COUNT(liked) FROM fu3_survey WHERE not_liked='" .$Cmysql->EscapeString($row['id']). "'"));
		
		$Ctemplate->useTemplate("freeuni3/results_table_likenotlike_row", array(
			'ID'	=>	$row['id'],
			'LECTURE_NAME'	=>	$row['lecture_name'],
			'STUDENT_NUM'	=>	$liked[0],
			'STUDENT_PER'	=>	(($liked[0] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($lectures); // Free the result
	
	$Ctemplate->useStaticTemplate("freeuni3/results_table_3", FALSE); // Output table, close not liked and open more-programs part
	
	$moreprograms = $Cmysql->Query("SELECT moreprograms, COUNT(userid) FROM fu3_survey GROUP BY moreprograms ORDER BY moreprograms ASC");
	
	while ( $row = mysql_fetch_row($moreprograms) )
	{
		// Going through every answer, output the results
		
		$Ctemplate->useTemplate("freeuni3/results_table_explain_row", array(
			'ANSWER'	=>	$row[0],
			'STUDENT_NUM'	=>	$row[1],
			'STUDENT_PER'	=>	(($row[1] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($moreprograms); // Free the result
	
	/* Audience */
	$audience_0 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='0'"));
	$audience_1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='1'"));
	$audience_2 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='2'"));
	$audience_3 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='3'"));
	$audience_4 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='4'"));
	$audience_5 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(audience) FROM fu3_survey WHERE audience='5'"));
	
	$Ctemplate->useTemplate("freeuni3/results_table_4", array(
		/* Audience */
		'AUDIENCE_0_NUM'	=>	$audience_0[0],
		'AUDIENCE_0_PER'	=>	(($audience_0[0] / $valaszolok[0]) * 100),
		
		'AUDIENCE_1_NUM'	=>	$audience_1[0],
		'AUDIENCE_1_PER'	=>	(($audience_1[0] / $valaszolok[0]) * 100),
		
		'AUDIENCE_2_NUM'	=>	$audience_2[0],
		'AUDIENCE_2_PER'	=>	(($audience_2[0] / $valaszolok[0]) * 100),
		
		'AUDIENCE_3_NUM'	=>	$audience_3[0],
		'AUDIENCE_3_PER'	=>	(($audience_3[0] / $valaszolok[0]) * 100),
		
		'AUDIENCE_4_NUM'	=>	$audience_4[0],
		'AUDIENCE_4_PER'	=>	(($audience_4[0] / $valaszolok[0]) * 100),
		
		'AUDIENCE_5_NUM'	=>	$audience_5[0],
		'AUDIENCE_5_PER'	=>	(($audience_5[0] / $valaszolok[0]) * 100)
	), FALSE); // Output audience results and tip header
	
	$tip = $Cmysql->Query("SELECT tip, COUNT(userid) FROM fu3_survey GROUP BY tip ORDER BY tip ASC");
	
	while ( $row = mysql_fetch_row($tip) )
	{
		// Going through every answer, output the results
		
		$Ctemplate->useTemplate("freeuni3/results_table_explain_row", array(
			'ANSWER'	=>	$row[0],
			'STUDENT_NUM'	=>	$row[1],
			'STUDENT_PER'	=>	(($row[1] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($tip); // Free the result
	
	$Ctemplate->useStaticTemplate("freeuni3/results_table_5", FALSE); // Output table, close tip and open boring part
	
	$boring = $Cmysql->Query("SELECT boring, COUNT(userid) FROM fu3_survey GROUP BY boring ORDER BY boring ASC");
	
	while ( $row = mysql_fetch_row($boring) )
	{
		// Going through every answer, output the results
		
		$Ctemplate->useTemplate("freeuni3/results_table_explain_row", array(
			'ANSWER'	=>	$row[0],
			'STUDENT_NUM'	=>	$row[1],
			'STUDENT_PER'	=>	(($row[1] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($boring); // Free the result
	
	$Ctemplate->useStaticTemplate("freeuni3/results_table_6", FALSE); // Output table, close boring and open aim part
	
	$aim = $Cmysql->Query("SELECT aim, COUNT(userid) FROM fu3_survey GROUP BY aim ORDER BY aim ASC");
	
	while ( $row = mysql_fetch_row($aim) )
	{
		// Going through every answer, output the results
		
		$Ctemplate->useTemplate("freeuni3/results_table_explain_row", array(
			'ANSWER'	=>	$row[0],
			'STUDENT_NUM'	=>	$row[1],
			'STUDENT_PER'	=>	(($row[1] / $valaszolok[0]) * 100)
		), FALSE); // Output one row into the table
	}
	
	unset($row); // Free the array
	mysql_free_result($aim); // Free the result
	
	/* Activia */
	$activia_1 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(activia) FROM fu3_survey WHERE activia='1'"));
	$activia_2 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(activia) FROM fu3_survey WHERE activia='2'"));
	$activia_3 = mysql_fetch_row($Cmysql->Query("SELECT COUNT(activia) FROM fu3_survey WHERE activia='3'"));
	
	$Ctemplate->useTemplate("freeuni3/results_table_7", array(
		/* Activia */		
		'ACTIVIA_1_NUM'	=>	$activia_1[0],
		'ACTIVIA_1_PER'	=>	(($activia_1[0] / $valaszolok[0]) * 100),
		
		'ACTIVIA_2_NUM'	=>	$activia_2[0],
		'ACTIVIA_2_PER'	=>	(($activia_2[0] / $valaszolok[0]) * 100),
		
		'ACTIVIA_3_NUM'	=>	$activia_3[0],
		'ACTIVIA_3_PER'	=>	(($activia_3[0] / $valaszolok[0]) * 100)
	), FALSE); // Output activia results
}
$Ctemplate->useStaticTemplate("freeuni3/results_foot", FALSE); // Footer
DoFooter();
?>