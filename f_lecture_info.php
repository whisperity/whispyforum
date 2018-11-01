<?php
 /**
 * WhispyForum script file - f_lecture_info.php
 * 
 * Freeuniversity Organizer script
 *  - Shows the detailed lecture information for a particular lecture
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
dieOnModule("freeuniversity"); // Die if FREEUNIVERSITY is disabled

$Ctemplate->useStaticTemplate("freeuniversity/info_head", FALSE); // Header

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
	
    // Halt if the mandatory parameter is not passed
    if ( !isset($_GET['id']) )
    {
        // If we opened page without specifying ID
        $Ctemplate->useTemplate("errormessage", array(
            'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
            'TITLE'	=>	"{LANG_MISSING_PARAMETERS}", // Error title
            'BODY'	=>	"{LANG_MISSING_PARAMETERS_BODY}", // Error text
            'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
        ), FALSE ); // We give an error

        // We terminate execution
        $Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
        DoFooter();
        exit;
    }
    
    $lect_row = mysql_fetch_assoc($Cmysql->Query("SELECT title, description FROM f_lectures WHERE id='" . $Cmysql->EscapeString($_GET['id']. "'")));
    
    if ( $lect_row === FALSE )
    {
        $Ctemplate->useTemplate("errormessage", array(
            'PICTURE_NAME'	=>	"Nuvola_apps_terminal.png", // Terminal icon
            'TITLE'	=>	"Az előadás nem létezik", // Error title
            'BODY'	=>	"A megadott azonosítószámú előadás nem létezik az adatbázisban.", // Error text
            'ALT'	=>	"{LANG_MISSING_PARAMETERS}" // Alternate picture text
        ), FALSE ); // We give an error

        // We terminate execution
        $Ctemplate->useStaticTemplate("user/profile_foot", FALSE); // Footer
        DoFooter();
        exit;
    }
    
    $Ctemplate->useTemplate("freeuniversity/info", array(
        'TITLE' =>  $lect_row['title'],
        'DESCRIPTION'   =>  bbDecode($lect_row['description'])
    ), FALSE);
    
}

$Ctemplate->useStaticTemplate("freeuniversity/info_foot", FALSE); // Footer
DoFooter();
?>
