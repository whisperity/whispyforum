<?php
 /**
 * WhispyForum script file - freeuni_add_performer.php
 * 
 * Use to add new performers to the database
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni/add_performers_head", FALSE); // Header

// We define the $site variable
$site = "";

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
			$Ctemplate->useTemplate("freeuni/add_performers_data", array(
				'PNAME'	=>	$_POST['pname'], // Performer's name
				'EMAIL'	=>	$_POST['email'], // Performer's e-mail address
				'TELEPHONE'	=>	$_POST['telephone'], // Telephone number
				'COMMENTS'	=>	$_POST['comments'] // Comments
			), FALSE);
		} else {
			// We output general form
			$Ctemplate->useTemplate("freeuni/add_performers_data", array(
				'PNAME'	=>	"", // Performer's name
				'EMAIL'	=>	"", // Performer's e-mail address
				'TELEPHONE'	=>	"", // Telephone number
				'COMMENTS'	=>	"" // Comments
			), FALSE); // Performer information
		}
		break;
	case 1:
		// Performer adding
		
		// First, we do a check whether every required fields have data
		if ( $_POST['pname'] == NULL ) // Performer's name
		{
			$Ctemplate->useTemplate("freeuni/add_performers_variable_error", array(
				'VARIABLE'	=>	"Előadó neve", // Errornous variable name
				'PNAME'	=>	$_POST['pname'], // Performer's name (should be empty)
				'EMAIL'	=>	$_POST['email'], // Performer's e-mail address
				'TELEPHONE'	=>	$_POST['telephone'], // Telephone number
				'COMMENTS'	=>	$_POST['comments'] // Comments
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni/add_performers_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		if ( ( $_POST['email'] == NULL ) && ( $_POST['telephone'] == NULL ) ) // E-mail or telephone number
		{
			$Ctemplate->useTemplate("freeuni/add_performers_variable_error", array(
				'PNAME'	=>	$_POST['pname'], // Performer's name
				'EMAIL'	=>	$_POST['email'], // Performer's e-mail address (should be empty)
				'TELEPHONE'	=>	$_POST['telephone'], // Telephone number (should be empty)
				'COMMENTS'	=>	$_POST['comments'] // Comments
			), FALSE);
			
			// We terminate the script
			$Ctemplate->useStaticTemplate("freeuni/add_performers_foot", FALSE); // Footer
			DoFooter();
			exit;
		}
		
		// Now, all variables are entered
		
		
		// Everything is fine, we add the performer
		$addQuery = $Cmysql->Query("INSERT INTO fu_performers(pName, email, telephone, comments, status) VALUES ('" .
			$Cmysql->EscapeString($_POST['pname']). "'," .
			"'" .$Cmysql->EscapeString($_POST['email']). "'," .
			"'" .$Cmysql->EscapeString($_POST['telephone']). "', " .
			"'" .$Cmysql->EscapeString($_POST['comments']). "', 'unallocated')"); // Will be true if we succeed
		
		if ( $addQuery == FALSE )
		{
			// If there were errors during registration
			$Ctemplate->useTemplate("freeuni/add_performers_error", array(
				'VARIABLE'	=>	"E-mail cím vagy telefonszám", // Errornous variable name
				'PNAME'	=>	$_POST['pname'], // Performer's name
				'EMAIL'	=>	$_POST['email'], // Performer's e-mail address (should be empty)
				'TELEPHONE'	=>	$_POST['telephone'], // Telephone number (should be empty)
				'COMMENTS'	=>	$_POST['comments'] // Comments
			), FALSE); // Give error message and retry form
		} elseif ( $addQuery == TRUE )
		{
			// If registration completed successfully
			$Ctemplate->useStaticTemplate("freeuni/add_performers_success", FALSE); // Give success
		}
		break;
}

}
$Ctemplate->useStaticTemplate("freeuni/add_performers_foot", FALSE); // Footer
DoFooter();
?>