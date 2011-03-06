<?php
 /**
 * WhispyForum function file - load.php
 * 
 * Loads all required libraries for usage.
 * 
 * WhispyForum
 */
echo '<link rel="stylesheet" type="text/css" href="themes/winky/style.css">'."\n"; // We load the default stylesheet

/* Libraries */
// Template conductor (we load it before everything because templates are needed to get error messages)
require("templates.class.php");
global $Ctemplate; // Class is global
$Ctemplate = new class_template;
/* Libraries */

/* Free univesity phase configuartion file */
require("freeuniversity_phases.php"); // Load the file

switch ( FREEUNI_PHASE )
{
	// Based on the phase variable, we 
	// define a verbal value as well
	case 0:
		define('FREEUNI_PHASE_TEXT', "Előkészületek");
		break;
	case 1:
		define('FREEUNI_PHASE_TEXT', "Előadók szervezése");
		break;
	case 2:
		define('FREEUNI_PHASE_TEXT', "Előadásokra jelentkezés");
		break;
	case 3:
		define('FREEUNI_PHASE_TEXT', "Befejezés");
		break;
}
/* Free univesity phase configuartion file */

// Generate framework header
$Ctemplate->useTemplate("framework/header", array(
	'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
	'FREEUNI_PHASE_TEXT'	=>	FREEUNI_PHASE_TEXT // Current phase (text)
), FALSE);
/* HEADER */

/* Preload checks */
// Check whether configuration file exists
if ( file_exists("config.php") == 1 )
{
	require("config.php"); // Require and embed it
	
	// Check whether the installation was successful
	if ( !defined('WHISPYFORUM') ) // There is a "DEFINE()" constant in config.php which is a random UUID generated on install
	{
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	"winky", // Theme name
			'PICTURE_NAME'	=>	"Nuvola_apps_package_settings.png", // Text file icon
			'TITLE'	=>	"Corruption!", // Error title
			'BODY'	=>	"WhispyForum appears to be installed, however, the configuration file lacks some important variables. It's advised to reinstall the system. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.', // Error text
			'ALT'	=>	"Corrupt configuration" // Alternate picture text
	), FALSE ); // We output an error message
	exit; // Terminate execution
	} // Else: do nothing
} elseif ( file_exists("config.php") == 0 ) // If not
{
	$Ctemplate->useTemplate("errormessage", array(
		'THEME_NAME'	=>	"winky", // Theme name
		'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Unaviable file icon
		'TITLE'	=>	"Configuration file not found!", // Error title
		'BODY'	=>	"The site's configuration file is missing. It usally means that the engine isn't installed properly. Without configuration, the engine cannot be used, because it can't connect to the database. ".'You can install it by clicking <a href="install.php" alt="Install WhispyForum">here</a> and running the install script.', // Error text
		'ALT'	=>	"File unaviable" // Alternate picture text
	), FALSE ); // We output an error message
	exit; // Terminate execution
}

/* Preload checks */

/* Libraries */
// mySQL database layer
require("mysql.class.php");
global $Cmysql; // Class is global
$Cmysql = new class_mysql;

// users & session manager 
require("users.class.php");
global $Cusers; // Class is global
$Cusers = new class_users;

// general functions
require("includes/functions.php");
/* Libraries */

/* DEVELOPEMENT */
// PH, workaround: output HTTP POST and GET arrays
print "<h4>GET</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_GET,true))."<br>";
print "<h4>POST</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_POST,true))."<br>";
print "<h4>FILES</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_FILES,true))."<br>";

/* START GENERATION */
$Cmysql->Connect(); // Connect to database
$Cusers->Initialize(); // We initialize the userdata

/* DEVELOPEMENT 
print "<h4>SESSION</h4>";
print str_replace(array("\n"," "),array("<br>","&nbsp;"), var_export($_SESSION,true))."<br>"; */

/* FRAMEWORK */

$Ctemplate->useStaticTemplate("framework/left", FALSE); // Center table and left menubar begin
/* Left menubar */
	$Cusers->DoUserForm(); // Do login form or userbox
	$Ctemplate->DoMenuBars('LEFT'); // Do right menubar
/* Left menubar */

$Ctemplate->useStaticTemplate("framework/center", FALSE); // Closing left menubar and opening center

function DoFooter()
{
	global $Ctemplate, $Cmysql; // Load classes
	
	$Ctemplate->useStaticTemplate("framework/right", FALSE); // Close center table and right menubar begin
		
		if ( FREEUNI_PHASE == 1 )
		{
			// If we're in the first phase
			// Give performer info box
			$num_students = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM users"));
			$num_performers = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers"));
			
			$pending = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='pending'"));
			$will_come = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='agreed'"));
			$wont_come = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='refused'"));
			$unallocated = mysql_fetch_row($Cmysql->Query("SELECT COUNT(id) FROM fu_performers WHERE status='unallocated'"));
			
			$Ctemplate->useTemplate("freeuni/index_statistics", array(
				'NUM_STUDENTS'	=>	$num_students[0], // Number of students
				'NUM_PERFORMERS'	=>	$num_performers[0], // Number of performes (total)
				'PENDING'	=>	$pending[0], // Performers pending (waiting for response)
				'WILL_COME'	=>	$will_come[0], // Performers agreed
				'WONT_COME'	=>	$wont_come[0], // Performers rejected
				'UNALLOCATED'	=>	$unallocated[0], // Number of performers waiting to be allocated to a student
				'WIDTH'	=>	"100%" // Box width 
			), FALSE); // Give global statistics page
		}
		
		if ( FREEUNI_PHASE == 2 )
		{
			// If we're in the second phase,
			// give lecture list
			
			if ( $_SESSION['log_bool'] == TRUE )
			{
				// If the user is logged in
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
					'WIDTH'	=>	"100%", // Box width 
					'HOUR1'	=>	$myLectures[1],
					'HOUR2'	=>	$myLectures[2],
					'HOUR3'	=>	$myLectures[3],
					'HOUR4'	=>	$myLectures[4]
				), FALSE); // Give my lectures statictics
			} elseif ( $_SESSION['log_bool'] == FALSE )
			{	
				// If the user is a guest
				// give a dummy guest box
				$Ctemplate->useStaticTemplate("freeuni2/index_my_lectures_guest", FALSE);
			}
		}
		
		$Ctemplate->DoMenuBars('RIGHT'); // Do right menubar
	$Ctemplate->useStaticTemplate("framework/footer", FALSE); // Close right menubar and generate footer
	/* FOOTER */
	
	$Ctemplate->useTemplate("framework/footer_close", array(
		'WIDTH'	=>	"100%" // Box width
	), FALSE); // Close footer
	
	$Cmysql->Disconnect(); // Disconnect from database
	
}

/* FRAMEWORK */

/* END GENERATION */
?>