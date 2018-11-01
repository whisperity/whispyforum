<?php
 /**
 * WhispyForum script file - freeuni2_list_relations.php
 * 
 * Listing students who picked up a particular lecture in a particular hour
 * 
 * WhispyForum
 */

include("includes/safeload.php"); // Load webpage (without framework, because this page should open in _blank (new page) targe)
$Ctemplate->useStaticTemplate("freeuni2/student_list_head", FALSE); // Header

if ( FREEUNI_PHASE != 2 )
{
	// If we aren't in phase 2 (see ./freeuniversity_phases.php)
	
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

if ( ( isset($_POST['stud_id']) ) && ( isset($_POST['hour']) ) )
{
	// If we requested to forced kick a student from a lecture in an hour
	$forced_kick = $Cmysql->Query("UPDATE users SET hour" .$Cmysql->EscapeString($_POST['hour']). "=NULL WHERE id='" .$Cmysql->EscapeString($_POST['stud_id']). "'");
	
	if ( $forced_kick == FALSE )
	{
		// If there were errors deleting the menu
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
			'TITLE'	=>	"Nem sikerült a diák kirúgása", // Error title
			'BODY'	=>	"Adatbázishiba történt", // Error text
			'ALT'	=>	"Lekérdezési hiba" // Alternate picture text
		), FALSE ); // We give an error
	} elseif ( $forced_kick == TRUE )
	{
		// If we succeeded deleting the menu
		$Ctemplate->useTemplate("successbox", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
			'TITLE'	=>	"A diák sikeresen kirúgva", // Success title
			'BODY'	=>	"A diák eltávolítására az óráról sikeres volt.", // Success text
			'ALT'	=>	"Lekérdezés sikere" // Alternate picture text
		), FALSE ); // We give a success message
	}
	
	$Ctemplate->useTemplate("freeuni2/relation_list_back", array(
		'LECT_ID'	=>	$_POST['id'],
		'HOUR'	=>	$_POST['hour']
	), FALSE); // Return button
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("freeuni2/student_list_foot", FALSE); // Footer
	DoFooter();
	exit;
}

if ( ( !isset($_GET['id']) ) || ( !isset($_GET['hour']) ) )
{
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
		'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
		'TITLE'	=>	"Hiányzó paraméterek", // Error title
		'BODY'	=>	"Egy vagy több kötelező paraméter nem lett átadva.", // Error text
		'ALT'	=>	"Hiányzó paraméterek" // Alternate picture text
	), FALSE ); // We give an unaviable error
	
	// We terminate execution
	$Ctemplate->useStaticTemplate("freeuni2/student_list_foot", FALSE); // Footer
	DoFooter();
	exit;
}

$diakok = $Cmysql->Query("SELECT id, username, osztaly FROM users WHERE hour" .$Cmysql->EscapeString($_GET['hour']). "='" .$Cmysql->EscapeString($_GET['id']). "' ORDER BY username ASC"); // Query all students who selected the required lecture in the required hour

$eloadas_neve = mysql_fetch_row($Cmysql->Query("SELECT lecture_name FROM fu2_lectures WHERE id='" .$Cmysql->EscapeString($_GET['id']). "'")); // Query name of lecture

$diak_szam = mysql_num_rows($diakok);

$Ctemplate->useTemplate("freeuni2/relation_list_table_open", array(
	'HOUR'	=>	$_GET['hour'],
	'LECTURE_NAME'	=>	$eloadas_neve[0],
	'STUD_COUNT'	=>	$diak_szam
), FALSE); // Output header

while ( $row = mysql_fetch_assoc($diakok) )
{
	$Ctemplate->useTemplate("freeuni2/relation_list_table_row", array(
		'ID'	=>	$row['id'],
		'USERNAME'	=>	$row['username'],
		'OSZTALY'	=>	$row['osztaly'],
		'HOUR'	=>	$_GET['hour'],
		'LECT_ID'	=>	$_GET['id']
	), FALSE); // Output row for each student
}

$Ctemplate->useStaticTemplate("freeuni2/relation_list_table_close", FALSE);

}
$Ctemplate->useStaticTemplate("freeuni2/student_list_foot", FALSE); // Footer
DoFooter();
?>