<?php
 /**
 * WhispyForum script file - freeuni_allocate.php
 * 
 * Helps users to allocate performers for themselves
 * 
 * WhispyForum
 */

include("includes/load.php"); // Load webpage
$Ctemplate->useStaticTemplate("freeuni/allocate_head", FALSE); // Header

if ( FREEUNI_PHASE != 1 )
{
	// If we aren't in phase 1 (see ./freeuniversity_phases.php)
	
	$Ctemplate->useTemplate("freeuniversity_phase_error", array(
		'FREEUNI_PHASE'	=>	FREEUNI_PHASE, // Current phase (number)
		'REQUIRED_PHASE'	=>	1, // Required phase (number)
		'REQUIRED_TEXT'	=>	"Előadók szervezése", // Required phase (text)
	), FALSE); // Error message
	
	// Terminate the script
	$Ctemplate->useStaticTemplate("freeuni/allocate_foot", FALSE); // Footer
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

if ( isset($_POST['performer_id']) )
{
	// If there's a present performer ID
	// we move on allocating the performer for the user
	
	// First, we check whether there's an active relation for this presenter.
	$prev_relation = mysql_fetch_row($Cmysql->Query("SELECT user_id FROM fu_perf_user_relation WHERE performer_id='" .$Cmysql->EscapeString($_POST['performer_id']). "'")); // If there, this will have result
	
	if ( $prev_relation == TRUE )
	{
		// If there is an active relation
		// We terminate the allocation with giving an error message
		
		// Get username for related user
		$prev_username = mysql_fetch_row($Cmysql->Query("SELECT username FROM users WHERE id=" . $prev_relation[0])); // Get the username from the user's table
		
		$Ctemplate->useTemplate("errormessage", array(
			'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
			'PICTURE_NAME'	=>	"Nuvola_apps_ksnapshot.png", // Screen + camera icon
			'TITLE'	=>	"A kívánt előadó már le van foglalva!", // Error title
			'BODY'	=>	"Ezt az előadót <i>" .$prev_username[0]. "</i> már lefoglalta!", // Error text
			'ALT'	=>	"Lefoglalt előadó!" // Alternate picture text
		), FALSE ); // Give error
	} elseif ( $prev_relation == FALSE )
	{
		// If there is no relation for this performer
		// Go on with allocation...
		
		$allocate_performer = $Cmysql->Query("INSERT INTO fu_perf_user_relation(user_id, performer_id) VALUES (" .$_SESSION['uid']. ", " .$Cmysql->EscapeString($_POST['performer_id']). ")");
		
		// $allocate_performer is TRUE if we succeed
		// $allocate_performer is FALSE if we fail
		
		if ( $allocate_performer == FALSE )
		{
			// We failed allocating the performer
			$Ctemplate->useTemplate("errormessage", array(
				'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
				'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
				'TITLE'	=>	"Hiba!", // Error title
				'BODY'	=>	"Az SQL kérés futtatása közben hiba történt. Az előadó nem került lefoglalásra.", // Error text
				'ALT'	=>	"Hiba!" // Alternate picture text
			), FALSE ); // Give error
		} elseif ( $allocate_performer == TRUE )
		{
			// Succeeded writing allocation
			
			// Modify performer's status
			$perf_stat_modify = $Cmysql->Query("UPDATE fu_performers SET status='pending' WHERE id='" .$Cmysql->EscapeString($_POST['performer_id']). "'");
			
			// $perf_stat_modify is TRUE if we succeed
			// $perf_stat_modify is FALSE if we fail
			
			if ( $perf_stat_modify == FALSE )
			{
				// Failed modifying status
				$Ctemplate->useTemplate("errormessage", array(
					'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
					'PICTURE_NAME'	=>	"Nuvola_filesystems_folder_txt.png", // Text folder icon
					'TITLE'	=>	"Hiba!", // Error title
					'BODY'	=>	"Az SQL kérés futtatása közben hiba történt. Az előadó nem került lefoglalásra.", // Error text
					'ALT'	=>	"Hiba!" // Alternate picture text
				), FALSE ); // Give error
			} elseif ( $perf_stat_modify == TRUE )
			{
				// Give ultimate succeed message, performer is allocated
				$Ctemplate->useTemplate("successbox", array(
					'THEME_NAME'	=>	$_SESSION['theme_name'], // Theme name
					'PICTURE_NAME'	=>	"Nuvola_apps_kate.png", // Allocater icon
					'TITLE'	=>	"Előadó lefoglalva", // Success title
					'BODY'	=>	"Az előadó lefoglalása sikeres volt, és az előadó Függő státuszt kapott.<br>A státuszát a saját előadóid kezelésénél állíthatod be.", // Success text
					'ALT'	=>	"Siker!" // Alternate picture text
				), FALSE ); // We give a success message
			}
		}
	}
} elseif ( !isset($_POST['performer_id']) )
{
	// If there's not, give list
	
	$Ctemplate->useStaticTemplate("freeuni/allocate_table_open", FALSE); // Open list table
	
	// Unallocated performers
	$pUnallocated = $Cmysql->Query("SELECT * FROM fu_performers WHERE status='unallocated'");
	
	while ( $row_u = mysql_fetch_assoc($pUnallocated) )
	{
		// Generate rows
		$Ctemplate->useTemplate("freeuni/allocate_tablerow", array(
			'PERFORMER_NAME'	=>	$row_u['pName'],
			'EMAIL'	=>	$row_u['email'],
			'TELEPHONE'	=>	$row_u['telephone'],
			'COMMENTS'	=>	substr($row_u['comments'], 0, 64), // First 64 character of comments
			'PERFORMER_ID'	=>	$row_u['id'] // Performer's ID
		), FALSE);
	}
	
	$Ctemplate->useStaticTemplate("freeuni/allocate_table_close", FALSE); // Closing table
}

}

$Ctemplate->useStaticTemplate("freeuni/allocate_foot", FALSE); // Footer
DoFooter();
?>