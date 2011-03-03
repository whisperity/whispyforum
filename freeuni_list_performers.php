<?php
 /**
 * WhispyForum script file - freeuni_list_performers.php
 * 
 * Listing performers in a sophisticated format
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni/list_performers_head", FALSE); // Header

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
$userDBArray = mysql_fetch_assoc($Cmysql->Query("SELECT userLevel FROM users WHERE username='" .$Cmysql->EscapeString($_SESSION['username']). "' AND pwd='" .$Cmysql->EscapeString($_SESSION['pwd']). "'")); // We query the user's data

if ( ( isset($_POST['performer_id']) ) && ( @$_POST['remove'] == "true" ) )
{
	// If we decided to remove a performer
	// Check whether the user is an admin
	if ( $userDBArray['userLevel'] >= 3 )
	{
		// If user is an admin, do remove
		$remove_performer = $Cmysql->Query("DELETE FROM fu_performers WHERE id=" .$Cmysql->EscapeString($_POST['performer_id']));
		
		// $remove_performer is TRUE or FALSE based on success
		
		if ( $remove_performer == FALSE )
		{
			// If there were errors unallocating the performer
			$Ctemplate->useTemplate("errormessage", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
				'TITLE'	=>	"Az előadót nem lehet törölni", // Error title
				'BODY'	=>	"A lekérdezést nem sikerült lefuttatni. Az előadó az adatbázisban marad.", // Error text
				'ALT'	=>	"SQL hiba" // Alternate picture text
			), FALSE ); // We give an error
		} elseif ( $remove_performer == TRUE )
		{
			// If there wasn't errors, give success
			$Ctemplate->useTemplate("successbox", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
				'TITLE'	=>	"Az előadó törölve.", // Success title
				'BODY'	=>	"Az előadó törölve az adatbázisból", // Success text
				'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
			), FALSE ); // We give a success message
			
			// Remove relation
			// First, check if there's any present relations
			$relations = mysql_fetch_row($Cmysql->Query("SELECT * FROM fu_perf_user_relation WHERE performer_id='" .$Cmysql->EscapeString($_POST['performer_id']). "'"));
			
			if ( $relations == TRUE )
			{
				
				// If there's present relations, remove them
				$relation_remove = $Cmysql->Query("DELETE FROM fu_perf_user_relation WHERE performer_id='" .$Cmysql->EscapeString($_POST['performer_id']). "'");
				
				// $relation_remove is TRUE or FALSE based on success
				if ( $relation_remove == FALSE )
				{
					// If we failed removing the relation
					$Ctemplate->useTemplate("messagebox", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_locked.png", // Locked folder icon
						'TITLE'	=>	"A foglalás nem törölhető", // Error title
						'BODY'	=>	"Az előadó ki lett törölve, ám még mindig hozzá van egy felhasználóhoz rendelve. Az inkozisztens állapot helyreáll... (idővel)", // Error text
						'ALT'	=>	"SQL hiba" // Alternate picture text
					), FALSE ); // We give a message (orange box)
				} elseif ( $relation_remove == TRUE )
				{
					// If we succeeded removing the relation
					$Ctemplate->useTemplate("successbox", array(
						'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
						'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
						'TITLE'	=>	"A foglalás törölve", // Success title
						'BODY'	=>	"Az előadóhoz tartozó foglalás megszűnt.", // Success text
						'ALT'	=>	"Sikeres lekérdezés" // Alternate picture text
					), FALSE ); // We give a success message
				}
			} elseif ( $relations == FALSE )
			{
				// If there isn't relations returning to the query
				// we give an orange messagebox, because it means two things
				// there isn't relations
				// sql error
				
				$Ctemplate->useTemplate("messagebox", array(
					'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
					'PICTURE_NAME'	=>	"Nuvola_apps_ksnapshot.png", // Screen+camera icon
					'TITLE'	=>	"Az előadóhoz nem található hozzárendelés", // Message title
					'BODY'	=>	"Az üzenet két dolgot jelölhet: <ul>
						<li>Ténylegesen nincs hozzárendelés. Ebben az esetben a törlés sikeresen végrehajtódott.</li>
						<li>Lennének hozzárendelések, de az adatbázis nem kérdezhető le. Ebben az esetben ellenőrizd a hozzáférést.</li>
						</ul>", // Message text
					'ALT'	=>	"Értesítés" // Alternate picture text
				), FALSE ); // We give a general message
			}
		}
	} else {
		// Give error
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"Nincs jogosoultságod!", // Error title
			'BODY'	=>	"Az előadó törléséhez Adminisztrátori jogosultság szükséges!", // Error text
			'ALT'	=>	"Nem elegendő jogkör!" // Alternate picture text
		), FALSE ); // We give an unaviable error
	}
}

if ( ( isset($_POST['performer_id']) ) && ( @$_POST['edit'] == "true" ) )
{
	// If we decided to remove a performer
	// Check whether the user is an admin
	if ( $userDBArray['userLevel'] >= 3 )
	{
		// If the user is an admin, allow edition
		
		if (@$_POST['edit_do'] == "yes")
		{
			// If we requested doing edition
			$edit_query = $Cmysql->Query("UPDATE fu_performers SET comments='" .
				$Cmysql->EscapeString($_POST['comments']). "' WHERE id='" .
				$Cmysql->EscapeString($_POST['performer_id']). "'"); // TRUE if we succeed, FALSE if we fail
			
			if ( $edit_query == FALSE )
			{
				// If we failed, give error and return form
				$Ctemplate->useTemplate("freeuni/list_edit_error", array(
					'PERFORMER_ID'	=>	$_POST['performer_id'], // ID of the performer
					'COMMENTS'	=>	$_POST['comments'] // Comments
				), FALSE);
			} elseif ( $edit_query == TRUE )
			{
				// If we succeeded, give success message
				$Ctemplate->useStaticTemplate("freeuni/list_edit_success", FALSE);
			}
		} else {
			// Query the comments
			$comments = mysql_fetch_row($Cmysql->Query("SELECT comments FROM fu_performers WHERE id='" .
				$Cmysql->EscapeString($_POST['performer_id']). "'"));
			
			if ( @$_POST['error_goback'] == "yes" ) // If user is redirected because of an error
			{
				// We output the form with data returned (user doesn't have to enter it again)
				$Ctemplate->useTemplate("freeuni/list_edit_data", array(
					'PERFORMER_ID'	=>	$_POST['performer_id'], // ID of the performer
					'COMMENTS'	=>	$_POST['comments'] // Comments
				), FALSE);
			} else {
				// We output general form
				$Ctemplate->useTemplate("freeuni/list_edit_data", array(
					'PERFORMER_ID'	=>	$_POST['performer_id'], // ID of the performer
					'COMMENTS'	=>	$comments[0] // Current comments
				), FALSE); // Login information
			}
		}
	} else {
		// Give error
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_apps_agent.png", // Security officer icon
			'TITLE'	=>	"Nincs jogosoultságod!", // Error title
			'BODY'	=>	"Az előadó módosításához Adminisztrátori jogosultság szükséges!", // Error text
			'ALT'	=>	"Nem elegendő jogkör!" // Alternate picture text
		), FALSE ); // We give an unaviable error
	}
}


// First we list performers who are done
// and will come

// If we in normal mode, this is printer-friendliness off
if ( !isset($_GET['printer']) )
{
	$_GET['printer'] = 0;
}



$Ctemplate->useTemplate("freeuni/list_table_open", array(
	'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
	'ADMIN_EDIT_HEAD'	=> (($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ?
		$Ctemplate->useTemplate("freeuni/list_performers_edit_header", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'ADMINLOGO'	=>	$Ctemplate->useTemplate("adminlogo", array(
				'THEME_NAME'	=>	$_SESSION['theme_name']
			), TRUE) // Administrator logo
		), TRUE) : NULL), // If the user is an admin, give header row for edit buttons, otherwise, 
	'ADMIN_REMOVE_HEAD'	=> (($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ?
		$Ctemplate->useTemplate("freeuni/list_performers_delete_header", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'ADMINLOGO'	=>	$Ctemplate->useTemplate("adminlogo", array(
				'THEME_NAME'	=>	$_SESSION['theme_name']
			), TRUE) // Administrator logo
		), TRUE) : NULL) // If the user is an admin, give header row for delete buttons, otherwise, output nothing (using embedded templates)
), FALSE); // Opening the list table

$pWillCome = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='agreed'");

while ( $row_wc = mysql_fetch_assoc($pWillCome) )
{
	// Get linked user's name
	$userID_wc = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_wc['id'])); // Get the user's ID from relational table
	
	if ( $userID_wc[0] != NULL ) // Select only if there's present relation
	{
		$username_wc = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_wc[0])); // Get the username from the user's table
	} elseif ( $userID_wc[0] == NULL )
	{
		// If there isn't present relation
		$username_wc[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate("freeuni/list_performers_tablerow", array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#83A90A"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_wc['pName'],
		'STUDENT_NAME'	=>	$username_wc[0],
		'EMAIL'	=>	$row_wc['email'],
		'TELEPHONE'	=>	$row_wc['telephone'],
		'COMMENTS'	=>	substr($row_wc['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Vállalja",
		'ADMIN_EDIT_PERFORMER'	=>	( ($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_edit", array(
				'PERFORMER_ID'	=>	$row_wc['id'] // ID of the performer
			), TRUE) : NULL), // If the user is an admin, give edit button
		'ADMIN_DELETE_PERFORMER'	=>	( ($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_delete", array(
				'PERFORMER_ID'	=>	$row_wc['id'] // ID of the performer
			), TRUE) : NULL) // If the user is an admin, give delete button
	), FALSE);
}

// Pending performers
$pPending = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='pending'");

while ( $row_p = mysql_fetch_assoc($pPending) )
{
	// Get linked user's name
	$userID_p = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_p['id'])); // Get the user's ID from relational table
	
	if ( $userID_p[0] != NULL ) // Select only if there's present relation
	{
		$username_p = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_p[0])); // Get the username from the user's table
	} elseif ( $userID_p[0] == NULL )
	{
		// If there isn't present relation
		$username_p[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate("freeuni/list_performers_tablerow", array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#417FCC"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_p['pName'],
		'STUDENT_NAME'	=>	$username_p[0],
		'EMAIL'	=>	$row_p['email'],
		'TELEPHONE'	=>	$row_p['telephone'],
		'COMMENTS'	=>	substr($row_p['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Függőben",
		'ADMIN_EDIT_PERFORMER'	=>	( ($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_edit", array(
				'PERFORMER_ID'	=>	$row_p['id'] // ID of the performer
			), TRUE) : NULL), // If the user is an admin, give edit button
		'ADMIN_DELETE_PERFORMER'	=>	(($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_delete", array(
				'PERFORMER_ID'	=>	$row_p['id'] // ID of the performer
			), TRUE) : NULL) // If the user is an admin, give delete button
	), FALSE);
}

// Unallocated performers
$pUnallocated = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='unallocated'");

while ( $row_u = mysql_fetch_assoc($pUnallocated) )
{
	// Get linked user's name
	$userID_u = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_u['id'])); // Get the user's ID from relational table
	
	if ( $userID_u[0] != NULL ) // Select only if there's present relation
	{
		$username_u = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_u[0])); // Get the username from the user's table
	} elseif ( $userID_u[0] == NULL )
	{
		// If there isn't present relation
		$username_u[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate("freeuni/list_performers_tablerow", array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#B4CDEC"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_u['pName'],
		'STUDENT_NAME'	=>	$username_u[0],
		'EMAIL'	=>	$row_u['email'],
		'TELEPHONE'	=>	$row_u['telephone'],
		'COMMENTS'	=>	substr($row_u['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Szabad",
		'ADMIN_EDIT_PERFORMER'	=>	( ($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_edit", array(
				'PERFORMER_ID'	=>	$row_u['id'] // ID of the performer
			), TRUE) : NULL), // If the user is an admin, give edit button
		'ADMIN_DELETE_PERFORMER'	=>	(($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_delete", array(
				'PERFORMER_ID'	=>	$row_u['id'] // ID of the performer
			), TRUE) : NULL) // If the user is an admin, give delete button
	), FALSE);
}

// Refused performers
$pRefused = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='refused'");

while ( $row_r = mysql_fetch_assoc($pRefused) )
{
	// Get linked user's name
	$userID_r = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id=" .$row_r['id'])); // Get the user's ID from relational table
	
	if ( $userID_r[0] != NULL ) // Select only if there's present relation
	{
		$username_r = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $userID_r[0])); // Get the username from the user's table
	} elseif ( $userID_r[0] == NULL )
	{
		// If there isn't present relation
		$username_r[0] = ""; // It will be zero string
	}
	
	// Generate rows
	$Ctemplate->useTemplate("freeuni/list_performers_tablerow", array(
		'BGCOLOR'	=>	($_GET['printer']==1 ? "" : "#E58800"), // Row background color (nothing if in printer friendly mode)
		'PERFORMER_NAME'	=>	$row_r['pName'],
		'STUDENT_NAME'	=>	$username_r[0],
		'EMAIL'	=>	$row_r['email'],
		'TELEPHONE'	=>	$row_r['telephone'],
		'COMMENTS'	=>	substr($row_r['comments'], 0, 128), // First 128 characters of comments
		'STATUS'	=>	"Nem vállalja",
		'ADMIN_EDIT_PERFORMER'	=>	( ($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_edit", array(
				'PERFORMER_ID'	=>	$row_r['id'] // ID of the performer
			), TRUE) : NULL), // If the user is an admin, give edit button
		'ADMIN_DELETE_PERFORMER'	=>	(($userDBArray['userLevel'] >= 3) && ($_GET['printer'] != 1) ? 
			$Ctemplate->useTemplate("freeuni/list_performers_delete", array(
				'PERFORMER_ID'	=>	$row_r['id'] // ID of the performer
			), TRUE) : NULL) // If the user is an admin, give delete button
	), FALSE);
}

$Ctemplate->useStaticTemplate("freeuni/list_table_close", FALSE); // Closing the list table
}

$Ctemplate->useStaticTemplate("freeuni/list_performers_foot", FALSE); // Footer
DoFooter();
?>